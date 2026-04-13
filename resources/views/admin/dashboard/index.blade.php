@extends('admin.layouts.app')
@section('title','Dashboard')

@section('content')
{{-- KPI Cards --}}
<div class="stat-grid">
  <div class="stat-card stat-green">
    <div class="stat-label">Total Revenue</div>
    <div class="stat-value">${{ number_format($stats['total_revenue'],0) }}</div>
    <div class="stat-meta stat-up">↑ This month: ${{ number_format($stats['this_month_revenue'],0) }}</div>
  </div>
  <div class="stat-card stat-blue">
    <div class="stat-label">Total Orders</div>
    <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
    <div class="stat-meta stat-down">{{ $stats['pending_orders'] }} pending</div>
  </div>
  <div class="stat-card stat-purple">
    <div class="stat-label">Active Employees</div>
    <div class="stat-value">{{ $stats['total_employees'] }}</div>
    <div class="stat-meta stat-up">{{ $stats['present_today'] }} present today</div>
  </div>
  <div class="stat-card stat-yellow">
    <div class="stat-label">Products</div>
    <div class="stat-value">{{ $stats['total_products'] }}</div>
    <div class="stat-meta {{ $stats['low_stock'] > 0 ? 'stat-down' : 'stat-up' }}">
      {{ $stats['low_stock'] }} low stock
    </div>
  </div>
</div>

{{-- Row 1: Revenue Line + Order Doughnut --}}
<div class="grid-2" style="margin-bottom:20px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">📈 Revenue &amp; Orders — Last 6 Months</span>
      <span class="badge badge-green">Live</span>
    </div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title">📦 Order Status Distribution</span>
    </div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="orderChart"></canvas></div>
    </div>
  </div>
</div>

{{-- Row 2: Gateway Bar + Attendance Area + Dept Bar --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px">
  <div class="card">
    <div class="card-header"><span class="card-title">💳 Gateway Revenue</span></div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="gatewayChart"></canvas></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">👥 Attendance — 7 Days</span></div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="attendanceChart"></canvas></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">🏢 Dept Headcount</span></div>
    <div class="card-body">
      <div class="chart-wrap"><canvas id="deptChart"></canvas></div>
    </div>
  </div>
</div>

{{-- Row 3: Recent Orders + Top Products --}}
<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Recent Orders</span>
      <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">View all</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($recentOrders as $order)
          <tr>
            <td class="text-accent" style="font-size:12px;font-weight:600">{{ $order->order_number }}</td>
            <td>{{ $order->customer_name }}</td>
            <td class="fw-700">${{ number_format($order->total,2) }}</td>
            <td>
              @php $sc=['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red'][$order->status??'pending'] @endphp
              <span class="badge badge-{{ $sc }}">{{ $order->status }}</span>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;color:var(--text3);padding:24px">No orders yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Top Products by Sales</span>
      <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">View all</a>
    </div>
    <div class="card-body">
      @forelse($topProducts as $product)
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:13px;font-weight:500">{{ $product->name }}</span>
          <span class="text-muted" style="font-size:12px">{{ $product->order_items_count }} sold</span>
        </div>
        <div style="height:5px;background:var(--border);border-radius:3px;overflow:hidden">
          @php $max = $topProducts->max('order_items_count') ?: 1; $w = $product->order_items_count/$max*100 @endphp
          <div style="height:100%;width:{{ $w }}%;background:var(--accent);border-radius:3px;transition:width .4s"></div>
        </div>
      </div>
      @empty
      <div style="text-align:center;color:var(--text3);padding:24px">No sales data yet</div>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const C = {
  accent:'#6366f1', green:'#10b981', yellow:'#f59e0b',
  blue:'#3b82f6', red:'#ef4444', purple:'#8b5cf6',
  text2:'#94a3b8', grid:'rgba(255,255,255,.05)',
  card:'#1a1d27'
};
Chart.defaults.color = C.text2;
Chart.defaults.borderColor = C.grid;
Chart.defaults.font.family = "'Inter',sans-serif";
Chart.defaults.font.size = 11;

const revenue = @json($revenueChart);
const orderStatus = @json($orderStatus);
const gateway = @json($gatewayChart);
const attendance = @json($attendanceChart);
const dept = @json($deptChart);

// ── Revenue line ──────────────────────────────────────────────────────────
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: revenue.map(r => r.label),
    datasets: [
      { label:'Revenue ($)', data: revenue.map(r => r.revenue), borderColor: C.accent, backgroundColor:'rgba(99,102,241,.1)', tension:.4, fill:true, pointRadius:4, pointBackgroundColor: C.accent, yAxisID:'y' },
      { label:'Orders', data: revenue.map(r => r.orders), borderColor: C.green, backgroundColor:'rgba(16,185,129,.05)', tension:.4, fill:true, pointRadius:4, pointBackgroundColor: C.green, yAxisID:'y1' },
    ]
  },
  options: { responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
    plugins:{ legend:{ labels:{ boxWidth:10, padding:16 } } },
    scales:{
      y:{ ticks:{ callback: v => '$'+v.toLocaleString() }, grid:{color:C.grid} },
      y1:{ position:'right', grid:{drawOnChartArea:false}, ticks:{color: C.green} }
    }
  }
});

// ── Order doughnut ────────────────────────────────────────────────────────
const statusColors = { pending:C.yellow, processing:C.blue, shipped:C.purple, delivered:C.green, cancelled:C.red };
const statusKeys   = Object.keys(orderStatus);
new Chart(document.getElementById('orderChart'), {
  type:'doughnut',
  data:{
    labels: statusKeys.map(k => k.charAt(0).toUpperCase()+k.slice(1)),
    datasets:[{ data: statusKeys.map(k => orderStatus[k]||0), backgroundColor: statusKeys.map(k => statusColors[k]||C.text2), borderWidth:0, hoverOffset:6 }]
  },
  options:{ responsive:true, maintainAspectRatio:false, cutout:'68%',
    plugins:{ legend:{ position:'bottom', labels:{ padding:14, boxWidth:10 } } }
  }
});

// ── Gateway bar ───────────────────────────────────────────────────────────
new Chart(document.getElementById('gatewayChart'), {
  type:'bar',
  data:{
    labels: gateway.map(g => g.gateway),
    datasets:[{ label:'Revenue ($)', data: gateway.map(g => g.total), backgroundColor:[C.accent,C.blue,C.purple], borderRadius:6 }]
  },
  options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}},
    scales:{ y:{ ticks:{ callback: v=>'$'+v.toLocaleString() } }, x:{ grid:{display:false} } }
  }
});

// ── Attendance area ───────────────────────────────────────────────────────
new Chart(document.getElementById('attendanceChart'), {
  type:'line',
  data:{
    labels: attendance.map(a => a.label),
    datasets:[
      { label:'Present', data: attendance.map(a => a.present), borderColor:C.green, backgroundColor:'rgba(16,185,129,.1)', fill:true, tension:.3, pointRadius:3 },
      { label:'Absent',  data: attendance.map(a => a.absent),  borderColor:C.red,   backgroundColor:'rgba(239,68,68,.08)', fill:true, tension:.3, pointRadius:3 },
    ]
  },
  options:{ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false},
    plugins:{ legend:{ labels:{ boxWidth:10 } } },
    scales:{ y:{ beginAtZero:true, ticks:{precision:0} }, x:{grid:{display:false}} }
  }
});

// ── Dept horizontal bar ───────────────────────────────────────────────────
const deptKeys = Object.keys(dept);
new Chart(document.getElementById('deptChart'), {
  type:'bar',
  data:{
    labels: deptKeys,
    datasets:[{ label:'Employees', data: deptKeys.map(k => dept[k]), backgroundColor: C.accent+'bb', borderRadius:4 }]
  },
  options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false,
    plugins:{ legend:{display:false} },
    scales:{ x:{ ticks:{precision:0} }, y:{grid:{display:false}} }
  }
});
</script>
@endpush
