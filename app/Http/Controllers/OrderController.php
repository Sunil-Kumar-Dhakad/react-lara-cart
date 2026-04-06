<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items.product');

        if ($request->status)   $query->where('status', $request->status);
        if ($request->customer) $query->where('customer_name', 'like', "%{$request->customer}%");
        if ($request->from)     $query->whereDate('created_at', '>=', $request->from);
        if ($request->to)       $query->whereDate('created_at', '<=', $request->to);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'  => 'required|string',
            'customer_email' => 'required|email',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'notes'          => 'nullable|string',
        ]);

        $subtotal = 0;
        $items = [];

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return response()->json(['message' => "Insufficient stock for {$product->name}"], 422);
            }
            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;
            $items[] = [...$item, 'price' => $product->price, 'total' => $lineTotal];
            $product->decrement('stock', $item['quantity']);
        }

        $tax   = $subtotal * 0.18;
        $total = $subtotal + $tax;

        $order = Order::create([
            'order_number'   => 'ORD-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'status'         => 'pending',
            'payment_status' => 'pending',
            'notes'          => $data['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $order->items()->create($item);
        }

        // Auto-generate invoice
        Invoice::create([
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'order_id'       => $order->id,
            'customer_name'  => $order->customer_name,
            'customer_email' => $order->customer_email,
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'status'         => 'pending',
            'due_date'       => now()->addDays(30),
            'issued_date'    => now(),
        ]);

        return response()->json([
            'message' => 'Order created and invoice generated.',
            'order'   => $order->load('items.product'),
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load('items.product', 'invoice'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,refunded',
            'notes'          => 'nullable|string',
        ]);

        $order->update($data);
        return response()->json(['message' => 'Order updated.', 'order' => $order]);
    }

    public function destroy(Order $order)
    {
        if ($order->status !== 'cancelled') {
            return response()->json(['message' => 'Only cancelled orders can be deleted.'], 422);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted.']);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);
        return response()->json(['message' => 'Order status updated.', 'order' => $order]);
    }

    public function byCustomer(Request $request, string $customer)
    {
        return response()->json(Order::where('customer_name', 'like', "%{$customer}%")->with('items.product')->get());
    }
}


class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('order');
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id'      => 'required|exists:orders,id',
            'due_date'      => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        $order = Order::findOrFail($data['order_id']);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . date('Y') . '-' . strtoupper(\Str::random(6)),
            'order_id'       => $order->id,
            'customer_name'  => $order->customer_name,
            'customer_email' => $order->customer_email,
            'subtotal'       => $order->subtotal,
            'tax'            => $order->tax,
            'total'          => $order->total,
            'status'         => 'pending',
            'due_date'       => $data['due_date'],
            'issued_date'    => now(),
            'notes'          => $data['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Invoice created.', 'invoice' => $invoice], 201);
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load('order.items.product'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($request->validate([
            'status'   => 'sometimes|in:pending,paid,overdue,cancelled',
            'due_date' => 'sometimes|date',
            'notes'    => 'nullable|string',
        ]));
        return response()->json(['message' => 'Invoice updated.', 'invoice' => $invoice]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function downloadPdf(Invoice $invoice)
    {
        // Use barryvdh/laravel-dompdf
        $pdf = \PDF::loadView('invoices.pdf', ['invoice' => $invoice->load('order.items.product')]);
        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    public function sendEmail(Invoice $invoice)
    {
        \Mail::send('emails.invoice', ['invoice' => $invoice], function ($msg) use ($invoice) {
            $msg->to($invoice->customer_email)->subject("Invoice {$invoice->invoice_number} — Nexus ERP");
        });
        return response()->json(['message' => 'Invoice emailed to ' . $invoice->customer_email]);
    }

    public function markPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);
        $invoice->order->update(['payment_status' => 'paid']);
        return response()->json(['message' => 'Invoice marked as paid.', 'invoice' => $invoice]);
    }
}
