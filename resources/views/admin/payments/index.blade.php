@extends('admin.layouts.app')
@section('title','Payments')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Payment Records</div>
    <div class="page-subtitle">Gateway transaction log</div>
  </div>
</div>

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
  <div class="stat-card stat-green">
    <div class="stat-label">Total Collected</div>
    <div class="stat-value">${{ number_format($totalSuccess,2) }}</div>
    <div class="stat-meta stat-up">Successful payments</div>
  </div>
  <div class="stat-card stat-yellow">
    <div class="stat-label">Pending Amount</div>
    <div class="stat-value">${{ number_format($totalPending,2) }}</div>
    <div class="stat-meta">Awaiting processing</div>
  </div>
  <div class="stat-card stat-red">
    <div class="stat-label">Failed Transactions</div>
    <div class="stat-value">{{ $totalFailed }}</div>
    <div class="stat-meta stat-down">Requires attention</div>
  </div>
</div>

<form method="GET" class="filter-bar">
  <div class="search-wrap">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input name="search" value="{{ request('search') }}" placeholder="Customer or order number…">
  </div>
  <select name="gateway" class="filter-input">
    <option value="">All Gateways</option>
    @foreach(['stripe','razorpay','paypal','manual'] as $g)
      <option value="{{ $g }}" {{ request('gateway')===$g?'selected':'' }}>{{ ucfirst($g) }}</option>
    @endforeach
  </select>
  <select name="status" class="filter-input">
    <option value="">All Status</option>
    @foreach(['success','pending','failed','refunded'] as $s)
      <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <input type="date" name="from" value="{{ request('from') }}" class="filter-input">
  <input type="date" name="to"   value="{{ request('to') }}"   class="filter-input">
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr><th>Pay ID</th><th>Order</th><th>Customer</th><th>Amount</th><th>Gateway</th><th>Ref ID</th><th>Date</th><th>Status</th></tr>
    </thead>
    <tbody>
      @forelse($payments as $pay)
      @php
        $gc=['stripe'=>'blue','razorpay'=>'purple','paypal'=>'yellow','manual'=>'gray'][$pay->gateway??'manual'];
        $sc=['success'=>'green','pending'=>'yellow','failed'=>'red','refunded'=>'blue'][$pay->status??'pending'];
      @endphp
      <tr>
        <td class="text-accent" style="font-size:12px;font-family:monospace">PAY-{{ str_pad($pay->id,5,'0',STR_PAD_LEFT) }}</td>
        <td class="text-muted" style="font-size:12px">{{ $pay->order->order_number ?? '—' }}</td>
        <td>{{ $pay->order->customer_name ?? '—' }}</td>
        <td class="fw-700">${{ number_format($pay->amount,2) }}</td>
        <td><span class="badge badge-{{ $gc }}">{{ strtoupper($pay->gateway) }}</span></td>
        <td class="text-muted" style="font-size:11px;font-family:monospace;max-width:140px;overflow:hidden;text-overflow:ellipsis">
          {{ $pay->gateway_ref ?? '—' }}
        </td>
        <td class="text-muted" style="font-size:12px">{{ $pay->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
        <td><span class="badge badge-{{ $sc }}">{{ $pay->status }}</span></td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text3)">No payment records found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $payments->withQueryString()->links() }}</div>
@endsection
