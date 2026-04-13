<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Dashboard') — Nexus Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f1117;--bg2:#13161f;--card:#1a1d27;--card2:#1e2235;
  --border:#252840;--border2:#2d3148;
  --accent:#6366f1;--accent2:#818cf8;--accent3:#c7d2fe;
  --green:#10b981;--red:#ef4444;--yellow:#f59e0b;--blue:#3b82f6;--purple:#8b5cf6;
  --text:#e2e8f0;--text2:#94a3b8;--text3:#64748b;
  --sidebar:240px;
  --font:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
}
html,body{height:100%;font-family:var(--font);background:var(--bg);color:var(--text);font-size:14px;line-height:1.5}
a{color:inherit;text-decoration:none}
button,input,select,textarea{font-family:var(--font)}

/* ── Sidebar ── */
.sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sidebar);background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;overflow-y:auto}
.sidebar-logo{padding:20px 16px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;flex-shrink:0}
.logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--accent),#7c3aed);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:15px;color:#fff;flex-shrink:0}
.logo-text{font-size:16px;font-weight:700;color:var(--text)}
.logo-sub{font-size:10px;color:var(--text3);margin-top:1px}
.nav-section{padding:14px 12px 4px;font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:1.2px}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 12px;margin:1px 6px;border-radius:7px;font-size:13px;color:var(--text2);cursor:pointer;transition:all .15s;white-space:nowrap;border:none;background:none;width:calc(100% - 12px);text-align:left}
.nav-item:hover{background:rgba(99,102,241,.1);color:var(--text)}
.nav-item.active{background:rgba(99,102,241,.15);color:var(--accent2);border-left:3px solid var(--accent);border-radius:0 7px 7px 0;margin-left:3px;padding-left:9px}
.nav-item svg{flex-shrink:0;opacity:.7}
.nav-item.active svg,.nav-item:hover svg{opacity:1}
.nav-badge{margin-left:auto;background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px}
.sidebar-footer{margin-top:auto;padding:12px 8px;border-top:1px solid var(--border);flex-shrink:0}
.user-card{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;background:var(--card)}
.user-avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0}
.user-name{font-size:12px;font-weight:600;color:var(--text)}
.user-role{font-size:10px;color:var(--text3);margin-top:1px}
.role-pill{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}
.role-super_admin{background:rgba(139,92,246,.2);color:#c4b5fd}
.role-admin{background:rgba(99,102,241,.2);color:#a5b4fc}
.role-accountant{background:rgba(16,185,129,.2);color:#6ee7b7}
.role-hr{background:rgba(245,158,11,.2);color:#fcd34d}
.role-manager{background:rgba(59,130,246,.2);color:#93c5fd}
.role-viewer{background:rgba(100,116,139,.2);color:#94a3b8}

/* ── Main ── */
.main{margin-left:var(--sidebar);min-height:100vh;display:flex;flex-direction:column}
.topbar{height:56px;border-bottom:1px solid var(--border);background:var(--bg2);display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:50;flex-shrink:0}
.topbar-title{font-size:16px;font-weight:600;color:var(--text)}
.topbar-actions{display:flex;align-items:center;gap:10px}
.content{padding:24px;flex:1}

/* ── Cards ── */
.card{background:var(--card);border:1px solid var(--border);border-radius:12px}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:600;color:var(--text)}
.card-body{padding:20px}

/* ── Stat cards ── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:18px 20px;position:relative;overflow:hidden}
.stat-card::after{content:'';position:absolute;right:-10px;top:-10px;width:60px;height:60px;border-radius:50%;opacity:.06}
.stat-blue::after{background:#3b82f6}.stat-green::after{background:#10b981}.stat-purple::after{background:#8b5cf6}.stat-yellow::after{background:#f59e0b}.stat-red::after{background:#ef4444}.stat-indigo::after{background:#6366f1}
.stat-label{font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px}
.stat-value{font-size:26px;font-weight:800;color:var(--text);line-height:1}
.stat-meta{font-size:11px;color:var(--text3);margin-top:6px;display:flex;align-items:center;gap:4px}
.stat-up{color:var(--green)}.stat-down{color:var(--red)}

/* ── Table ── */
.table-wrap{overflow-x:auto;border-radius:12px}
table{width:100%;border-collapse:collapse}
thead th{padding:11px 14px;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.7px;border-bottom:1px solid var(--border);text-align:left;background:var(--card2);white-space:nowrap}
tbody td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:rgba(99,102,241,.04)}

/* ── Badges / pills ── */
.badge{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
.badge-green{background:rgba(16,185,129,.15);color:#6ee7b7}
.badge-red{background:rgba(239,68,68,.15);color:#fca5a5}
.badge-yellow{background:rgba(245,158,11,.15);color:#fcd34d}
.badge-blue{background:rgba(59,130,246,.15);color:#93c5fd}
.badge-purple{background:rgba(139,92,246,.15);color:#c4b5fd}
.badge-gray{background:rgba(100,116,139,.15);color:#94a3b8}

/* ── Buttons ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:all .15s;white-space:nowrap;text-decoration:none}
.btn-primary{background:var(--accent);color:#fff}.btn-primary:hover{background:var(--accent2)}
.btn-secondary{background:var(--card2);color:var(--text);border:1px solid var(--border2)}.btn-secondary:hover{background:var(--border)}
.btn-success{background:rgba(16,185,129,.15);color:#6ee7b7;border:1px solid rgba(16,185,129,.2)}.btn-success:hover{background:rgba(16,185,129,.25)}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.2)}.btn-danger:hover{background:rgba(239,68,68,.25)}
.btn-warning{background:rgba(245,158,11,.15);color:#fcd34d;border:1px solid rgba(245,158,11,.2)}.btn-warning:hover{background:rgba(245,158,11,.25)}
.btn-sm{padding:5px 10px;font-size:11px}
.btn-icon{padding:6px;border-radius:7px}

/* ── Forms ── */
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px}
.form-input{width:100%;padding:9px 12px;background:var(--card2);border:1px solid var(--border2);border-radius:8px;color:var(--text);font-size:13px;transition:border .15s}
.form-input:focus{outline:none;border-color:var(--accent)}
.form-input::placeholder{color:var(--text3)}
select.form-input option{background:var(--card)}
textarea.form-input{resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.form-error{font-size:11px;color:var(--red);margin-top:4px}

/* ── Pagination ── */
.pagination{display:flex;gap:4px;margin-top:16px;justify-content:center;flex-wrap:wrap}
.pagination a,.pagination span{padding:6px 11px;border-radius:7px;font-size:12px;background:var(--card2);border:1px solid var(--border2);color:var(--text2);text-decoration:none;transition:all .15s}
.pagination a:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
.pagination .active span{background:var(--accent);color:#fff;border-color:var(--accent)}

/* ── Alerts ── */
.alert{padding:11px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#6ee7b7}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
.alert-info{background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);color:var(--accent3)}

/* ── Filter bar ── */
.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px}
.filter-input{padding:7px 12px;background:var(--card2);border:1px solid var(--border2);border-radius:8px;color:var(--text);font-size:12px;min-width:140px}
.filter-input:focus{outline:none;border-color:var(--accent)}
.search-wrap{display:flex;align-items:center;gap:8px;background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:7px 12px}
.search-wrap input{background:none;border:none;color:var(--text);font-size:13px;outline:none;min-width:200px}
.search-wrap input::placeholder{color:var(--text3)}

/* ── Grid helpers ── */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}

/* ── Chart wrapper ── */
.chart-wrap{position:relative;height:240px}
canvas{max-height:240px}

/* ── Modal ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:500;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal{background:var(--card);border:1px solid var(--border2);border-radius:14px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}
.modal-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.modal-body{padding:22px}
.modal-footer{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}

/* ── Scrollbar ── */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--accent)}

/* ── Misc ── */
.divider{height:1px;background:var(--border);margin:16px 0}
.text-muted{color:var(--text3)}
.text-success{color:var(--green)}
.text-danger{color:var(--red)}
.text-warning{color:var(--yellow)}
.text-info{color:var(--blue)}
.text-accent{color:var(--accent2)}
.fw-700{font-weight:700}
.mt-auto{margin-top:auto}
.flex{display:flex}.items-center{align-items:center}.justify-between{justify-content:space-between}.gap-2{gap:8px}.gap-3{gap:12px}.gap-4{gap:16px}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.page-title{font-size:20px;font-weight:700;color:var(--text)}
.page-subtitle{font-size:12px;color:var(--text3);margin-top:2px}
</style>
@stack('styles')
</head>
<body>

{{-- ── Sidebar ── --}}
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">N</div>
    <div>
      <div class="logo-text">Nexus Admin</div>
      <div class="logo-sub">Management Panel</div>
    </div>
  </div>

  {{-- Nav --}}
  <div style="flex:1;overflow-y:auto;padding-bottom:8px">
    <div class="nav-section">Overview</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      @svg_grid() Dashboard
    </a>

    @if(auth('admin')->user()->hasRole('super_admin','admin','accountant'))
    <div class="nav-section">Commerce</div>
    <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
      @svg_orders() Orders
    </a>
    <a href="{{ route('admin.payments.index') }}" class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
      @svg_card() Payments
    </a>
    @endif

    @if(auth('admin')->user()->hasRole('super_admin','admin'))
    <a href="{{ route('admin.products.index') }}" class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
      @svg_box() Products
    </a>
    @endif

    @if(auth('admin')->user()->hasRole('super_admin','admin','hr'))
    <div class="nav-section">People</div>
    <a href="{{ route('admin.employees.index') }}" class="nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
      @svg_users() Employees
    </a>
    <a href="{{ route('admin.attendance.index') }}" class="nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
      @svg_calendar() Attendance
    </a>
    @endif

    @if(auth('admin')->user()->hasRole('super_admin'))
    <div class="nav-section">System</div>
    <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
      @svg_shield() Users & Roles
    </a>
    @endif
  </div>

  {{-- User footer --}}
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">{{ strtoupper(substr(auth('admin')->user()->name,0,2)) }}</div>
      <div style="flex:1;min-width:0">
        <div class="user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth('admin')->user()->name }}</div>
        <div class="role-pill role-{{ auth('admin')->user()->role }}">{{ auth('admin')->user()->role_label }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}" style="margin-top:8px">
      @csrf
      <button type="submit" class="nav-item" style="color:var(--red);width:100%;margin:0">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sign Out
      </button>
    </form>
  </div>
</aside>

{{-- ── Main ── --}}
<div class="main">
  <div class="topbar">
    <div class="topbar-title">@yield('title','Dashboard')</div>
    <div class="topbar-actions">
      <span style="font-size:12px;color:var(--text3)">{{ now()->format('D, d M Y') }}</span>
      <span class="badge badge-green" style="font-size:10px">● Online</span>
    </div>
  </div>

  <div class="content">
    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-error">✗ {{ $errors->first() }}</div>
    @endif

    @yield('content')
  </div>
</div>

{{-- Inline SVG helpers via Blade directives (defined in AppServiceProvider) --}}
{{-- Fallback inline SVGs if directives aren't registered --}}
<script>
// Close modal helper
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.addEventListener('keydown',e=>{ if(e.key==='Escape') document.querySelectorAll('.modal-overlay.open').forEach(m=>m.classList.remove('open')) });
</script>
@stack('scripts')
</body>
</html>
