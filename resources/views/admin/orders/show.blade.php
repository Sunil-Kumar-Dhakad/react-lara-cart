@extends('admin.layouts.app')
@section('title','Order '.$order->order_number)

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ $order->order_number }}</div>
    <div class="page-subtitle">Placed {{ $order->created_at->format('d M Y, h:i A') }}</div>
  </div>
  <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">← Back to Orders</a>
</div>

<div class="grid-2" style="align-items:start">
  {{-- Left --}}
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card">
      <div class="card-header"><span class="card-title">Order Items</span></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
          <tbody>
            @foreach($order->items as $item)
            <tr>
              <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
              <td>{{ $item->quantity }}</td>
              <td>${{ number_format($item->price,2) }}</td>
              <td class="fw-700">${{ number_format($item->total,2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body" style="border-top:1px solid var(--border)">
        @foreach([['Subtotal','$'.number_format($order->subtotal,2)],['Tax (18%)','$'.number_format($order->tax,2)],['Shipping','Free']] as [$k,$v])
          <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px">
            <span class="text-muted">{{ $k }}</span><span>{{ $v }}</span>
          </div>
        @endforeach
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;border-top:1px solid var(--border);padding-top:10px;margin-top:6px">
          <span>Total</span><span class="text-accent">${{ number_format($order->total,2) }}</span>
        </div>
      </div>
    </div>

    {{-- Update order --}}
    <div class="card card-body">
      <div style="font-size:13px;font-weight:700;margin-bottom:14px">Update Order</div>
      <form method="POST" action="{{ route('admin.orders.update',$order) }}">
        @csrf @method('PUT')
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Order Status</label>
            <select name="status" class="form-input">
              @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                <option value="{{ $s }}" {{ $order->status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-input">
              @foreach(['pending','paid','refunded'] as $s)
                <option value="{{ $s }}" {{ $order->payment_status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Internal Notes</label>
          <textarea name="notes" class="form-input" rows="2">{{ $order->notes }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </form>
    </div>
  </div>

  {{-- Right --}}
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card card-body">
      <div style="font-size:13px;font-weight:700;margin-bottom:14px">Customer</div>
      @foreach([['Name',$order->customer_name],['Email',$order->customer_email]] as [$k,$v])
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px">
          <span class="text-muted">{{ $k }}</span><span>{{ $v }}</span>
        </div>
      @endforeach
    </div>
    <div class="card card-body">
      <div style="font-size:13px;font-weight:700;margin-bottom:14px">Status</div>
      @php
        $sc=['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red'][$order->status??'pending'];
        $pc=['pending'=>'yellow','paid'=>'green','refunded'=>'blue'][$order->payment_status??'pending'];
      @endphp
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <span class="badge badge-{{ $sc }}" style="font-size:12px;padding:5px 12px">Order: {{ $order->status }}</span>
        <span class="badge badge-{{ $pc }}" style="font-size:12px;padding:5px 12px">Payment: {{ $order->payment_status }}</span>
      </div>
    </div>
  </div>
</div>
@endsection
