<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order', 'invoice');
        if ($request->status)  $query->where('status', $request->status);
        if ($request->gateway) $query->where('gateway', $request->gateway);
        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function show(Payment $payment)
    {
        return response()->json($payment->load('order', 'invoice'));
    }

    // ─── STRIPE ───────────────────────────────────────────────────────────────

    public function createStripeIntent(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $order = Order::findOrFail($request->order_id);

        $intent = PaymentIntent::create([
            'amount'   => (int) ($order->total * 100), // cents
            'currency' => 'inr',
            'metadata' => ['order_id' => $order->id, 'order_number' => $order->order_number],
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'amount'        => $order->total,
        ]);
    }

    public function confirmStripePayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'order_id'          => 'required|exists:orders,id',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::retrieve($request->payment_intent_id);

        if ($intent->status !== 'succeeded') {
            return response()->json(['message' => 'Payment not completed.'], 422);
        }

        $order = Order::findOrFail($request->order_id);

        $payment = Payment::create([
            'order_id'       => $order->id,
            'gateway'        => 'stripe',
            'gateway_ref'    => $intent->id,
            'amount'         => $order->total,
            'currency'       => 'INR',
            'status'         => 'success',
            'paid_at'        => now(),
            'meta'           => json_encode(['intent' => $intent->id]),
        ]);

        $order->update(['payment_status' => 'paid']);
        if ($order->invoice) {
            $order->invoice->update(['status' => 'paid']);
        }

        return response()->json(['message' => 'Payment confirmed.', 'payment' => $payment]);
    }

    public function stripeWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook_secret'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent  = $event->data->object;
            $orderId = $intent->metadata->order_id ?? null;
            if ($orderId) {
                Order::where('id', $orderId)->update(['payment_status' => 'paid']);
            }
        }

        return response()->json(['received' => true]);
    }

    // ─── RAZORPAY ─────────────────────────────────────────────────────────────

    public function createRazorpayOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order = Order::findOrFail($request->order_id);

        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );

        $rzOrder = $api->order->create([
            'amount'          => (int) ($order->total * 100),
            'currency'        => 'INR',
            'receipt'         => $order->order_number,
            'payment_capture' => 1,
        ]);

        return response()->json([
            'razorpay_order_id' => $rzOrder->id,
            'amount'            => $order->total,
            'key_id'            => config('services.razorpay.key_id'),
        ]);
    }

    public function verifyRazorpayPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature'  => 'required',
            'order_id'            => 'required|exists:orders,id',
        ]);

        $expectedSig = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            config('services.razorpay.key_secret')
        );

        if ($expectedSig !== $request->razorpay_signature) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $order = Order::findOrFail($request->order_id);

        $payment = Payment::create([
            'order_id'    => $order->id,
            'gateway'     => 'razorpay',
            'gateway_ref' => $request->razorpay_payment_id,
            'amount'      => $order->total,
            'currency'    => 'INR',
            'status'      => 'success',
            'paid_at'     => now(),
        ]);

        $order->update(['payment_status' => 'paid']);

        return response()->json(['message' => 'Razorpay payment verified.', 'payment' => $payment]);
    }

    // ─── PAYPAL ───────────────────────────────────────────────────────────────

    public function createPaypalOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order  = Order::findOrFail($request->order_id);
        $token  = $this->getPaypalAccessToken();

        $response = \Http::withToken($token)
            ->post(config('services.paypal.base_url') . '/v2/checkout/orders', [
                'intent'         => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->order_number,
                    'amount'       => ['currency_code' => 'USD', 'value' => number_format($order->total / 83, 2)],
                ]],
            ]);

        return response()->json($response->json());
    }

    public function capturePaypalOrder(Request $request)
    {
        $request->validate(['paypal_order_id' => 'required', 'order_id' => 'required|exists:orders,id']);

        $token    = $this->getPaypalAccessToken();
        $response = \Http::withToken($token)
            ->post(config('services.paypal.base_url') . "/v2/checkout/orders/{$request->paypal_order_id}/capture");

        $data  = $response->json();
        $order = Order::findOrFail($request->order_id);

        if ($data['status'] === 'COMPLETED') {
            Payment::create([
                'order_id'    => $order->id,
                'gateway'     => 'paypal',
                'gateway_ref' => $request->paypal_order_id,
                'amount'      => $order->total,
                'currency'    => 'INR',
                'status'      => 'success',
                'paid_at'     => now(),
            ]);
            $order->update(['payment_status' => 'paid']);
        }

        return response()->json($data);
    }

    private function getPaypalAccessToken(): string
    {
        $response = \Http::asForm()->withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.client_secret')
        )->post(config('services.paypal.base_url') . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        return $response->json('access_token');
    }
}
