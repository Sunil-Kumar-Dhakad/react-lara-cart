<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order');
        if ($request->gateway) $query->where('gateway', $request->gateway);
        if ($request->status)  $query->where('status',  $request->status);
        if ($request->from)    $query->whereDate('paid_at','>=',$request->from);
        if ($request->to)      $query->whereDate('paid_at','<=',$request->to);
        if ($request->search)  $query->whereHas('order', fn($q) =>
            $q->where('customer_name','like',"%{$request->search}%")
              ->orWhere('order_number','like',"%{$request->search}%")
        );

        $payments     = $query->latest()->paginate(15)->withQueryString();
        $totalSuccess = Payment::where('status','success')->sum('amount');
        $totalPending = Payment::where('status','pending')->sum('amount');
        $totalFailed  = Payment::where('status','failed')->count();

        return view('admin.payments.index', compact('payments','totalSuccess','totalPending','totalFailed'));
    }

    public function show(Payment $payment)
    {
        return view('admin.payments.show', ['payment' => $payment->load('order.items.product')]);
    }
}
