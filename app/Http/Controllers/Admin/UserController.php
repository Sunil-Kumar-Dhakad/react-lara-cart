<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, fn($q) =>
                $q->where('name','like',"%{$request->search}%")
                  ->orWhere('email','like',"%{$request->search}%"))
            ->when($request->role, fn($q) => $q->where('role',$request->role))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:'.implode(',', array_keys(User::ROLES)),
        ]);
        $data['password']          = Hash::make($data['password']);
        $data['is_active']         = true;
        $data['email_verified_at'] = now();
        User::create($data);
        return redirect()->route('admin.users.index')->with('success','User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'role'     => 'required|in:'.implode(',', array_keys(User::ROLES)),
            'is_active'=> 'boolean',
            'password' => 'nullable|min:8|confirmed',
        ]);
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success','User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth('admin')->id()) {
            return back()->with('error','Cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success','User deleted.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:'.implode(',', array_keys(User::ROLES))]);
        $user->update(['role' => $request->role]);
        return back()->with('success','Role updated to '.User::ROLES[$request->role].'.');
    }
}
