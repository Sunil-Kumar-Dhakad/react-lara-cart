@extends('admin.layouts.app')
@section('title','Orders')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Orders</div>
    <div class="page-subtitle">{{ $orders->total() }} total orders</div>
  </div>
</div>

<form method="GET" class="filter-bar">
  <div class="search-wrap">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input name="search" value="{{ request('search') }}" placeholder="Order #, customer name…">
  </div>
  <select name="status" class="filter-input">
    <option value="">All Status</option>
    @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
      <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <select name="payment" class="filter-input">
    <option value="">All Payments</option>
    <option value="pending"  {{ request('payment')==='pending'?'selected':'' }}>Pending</option>
    <option value="paid"     {{ request('payment')==='paid'?'selected':'' }}>Paid</option>
    <option value="refunded" {{ request('payment')==='refunded'?'selected':'' }}>Refunded</option>
  </select>
  <input type="date" name="from" value="{{ request('from') }}" class="filter-input" title="From date">
  <input type="date" name="to"   value="{{ request('to') }}"   class="filter-input" title="To date">
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr>
        <th>Order #</th><th>Customer</th><th>Items</th>
        <th>Total</th><th>Order Status</th><th>Payment</th><th>Date</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($orders as $order)
      @php
        $sc=['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red'][$order->status??'pending'];
        $pc=['pending'=>'yellow','paid'=>'green','refunded'=>'blue'][$order->payment_status??'pending'];
      @endphp
      <tr>
        <td class="text-accent fw-700" style="font-size:12px">{{ $order->order_number }}</td>
        <td>
          <div style="font-weight:500">{{ $order->customer_name }}</div>
          <div class="text-muted" style="font-size:11px">{{ $order->customer_email }}</div>
        </td>
        <td class="text-muted">{{ $order->items->count() }} item(s)</td>
        <td class="fw-700">${{ number_format($order->total,2) }}</td>
        <td>
          <span class="badge badge-{{ $sc }}">{{ $order->status }}</span>
          {{-- Quick update dropdown --}}
          <form method="POST" action="{{ route('admin.orders.status',$order) }}" style="display:inline;margin-left:5px">
            @csrf @method('PATCH')
            <select name="status" class="filter-input" style="min-width:0;width:90px;padding:3px 6px;font-size:10px" onchange="this.form.submit()">
              @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                <option value="{{ $s }}" {{ $order->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </form>
        </td>
        <td><span class="badge badge-{{ $pc }}">{{ $order->payment_status }}</span></td>
        <td class="text-muted" style="font-size:12px">{{ $order->created_at->format('d M Y') }}</td>
        <td>
          <a href="{{ route('admin.orders.show',$order) }}" class="btn btn-secondary btn-sm">View</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text3)">No orders found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $orders->withQueryString()->links() }}</div>
@endsection
