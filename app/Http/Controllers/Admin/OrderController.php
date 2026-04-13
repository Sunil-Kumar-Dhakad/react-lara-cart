<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items.product');
        if ($request->status)   $query->where('status', $request->status);
        if ($request->payment)  $query->where('payment_status', $request->payment);
        if ($request->search)   $query->where(function($q) use ($request) {
            $q->where('order_number','like',"%{$request->search}%")
              ->orWhere('customer_name','like',"%{$request->search}%")
              ->orWhere('customer_email','like',"%{$request->search}%");
        });
        if ($request->from)     $query->whereDate('created_at','>=',$request->from);
        if ($request->to)       $query->whereDate('created_at','<=',$request->to);
        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status'         => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,refunded',
        ]);
        $order->update($request->only('status','payment_status','notes'));
        return back()->with('success','Order updated.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);
        return back()->with('success','Order status updated to '.ucfirst($request->status).'.');
    }
}
