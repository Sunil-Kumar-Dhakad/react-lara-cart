<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'sometimes|in:admin,manager,employee,viewer',
        ]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role'              => $request->role ?? 'employee',
            'email_otp'         => $otp,
            'email_otp_expires' => Carbon::now()->addMinutes(10),
            'email_verified_at' => null,
        ]);

        // Send verification email
        Mail::send('emails.verify', ['otp' => $otp, 'name' => $user->name], function ($msg) use ($user) {
            $msg->to($user->email)->subject('Verify Your Email — Nexus ERP');
        });

        return response()->json([
            'message' => 'Registration successful. Check your email for the verification code.',
            'user_id' => $user->id,
        ], 201);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_otp !== $request->otp) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        if (Carbon::now()->isAfter($user->email_otp_expires)) {
            return response()->json(['message' => 'Verification code expired. Please request a new one.'], 422);
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'email_otp'         => null,
            'email_otp_expires' => null,
        ]);

        $token = $user->createToken('nexus-erp')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);
        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['email_otp' => $otp, 'email_otp_expires' => Carbon::now()->addMinutes(10)]);

        Mail::send('emails.verify', ['otp' => $otp, 'name' => $user->name], function ($msg) use ($user) {
            $msg->to($user->email)->subject('New Verification Code — Nexus ERP');
        });

        return response()->json(['message' => 'Verification code resent.']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            return response()->json([
                'message'           => 'Please verify your email first.',
                'email_unverified'  => true,
            ], 403);
        }

        $token = $user->createToken('nexus-erp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);
        $token = Str::random(60);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );
        // Send reset email (implement Mail)
        return response()->json(['message' => 'Password reset link sent to your email.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = \DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 422);
        }

        User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
