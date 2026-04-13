@extends('admin.layouts.app')
@section('title','Users & Roles')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Users & Roles</div>
    <div class="page-subtitle">Manage admin panel access and permissions</div>
  </div>
  <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Add User</a>
</div>

{{-- Role reference card --}}
<div class="card card-body" style="margin-bottom:20px">
  <div style="font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:12px">Role Permissions Reference</div>
  <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px">
    @foreach(['super_admin'=>['All access','#c4b5fd'], 'admin'=>['Products, Orders, Employees','#a5b4fc'], 'accountant'=>['Orders, Payments','#6ee7b7'], 'hr'=>['Employees, Attendance','#fcd34d'], 'manager'=>['Dashboard only','#93c5fd'], 'viewer'=>['Read-only dashboard','#94a3b8']] as $role => [$desc, $color])
    <div style="background:var(--card2);border:1px solid var(--border2);border-radius:8px;padding:10px">
      <div class="role-pill role-{{ $role }}" style="margin-bottom:6px">{{ str_replace('_',' ',strtoupper($role)) }}</div>
      <div style="font-size:11px;color:var(--text3);line-height:1.4">{{ $desc }}</div>
    </div>
    @endforeach
  </div>
</div>

<form method="GET" class="filter-bar">
  <div class="search-wrap">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input name="search" value="{{ request('search') }}" placeholder="Search name or email…">
  </div>
  <select name="role" class="filter-input">
    <option value="">All Roles</option>
    @foreach(\App\Models\User::ROLES as $key => $label)
      <option value="{{ $key }}" {{ request('role')===$key?'selected':'' }}>{{ $label }}</option>
    @endforeach
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created</th><th>Actions</th></tr>
    </thead>
    <tbody>
      @forelse($users as $user)
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0">
              {{ strtoupper(substr($user->name,0,2)) }}
            </div>
            <div>
              <div style="font-weight:600">{{ $user->name }}
                @if($user->id === auth('admin')->id())
                  <span class="badge badge-blue" style="margin-left:4px">You</span>
                @endif
              </div>
              <div class="text-muted" style="font-size:11px">{{ $user->email }}</div>
            </div>
          </div>
        </td>
        <td>
          {{-- Quick role update --}}
          <form method="POST" action="{{ route('admin.users.role',$user) }}">
            @csrf @method('PATCH')
            <select name="role" class="filter-input" style="width:auto;padding:4px 8px;font-size:12px" onchange="this.form.submit()">
              @foreach(\App\Models\User::ROLES as $key => $label)
                <option value="{{ $key }}" {{ $user->role===$key?'selected':'' }}>{{ $label }}</option>
              @endforeach
            </select>
          </form>
        </td>
        <td>
          <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
            {{ $user->is_active ? 'Active' : 'Disabled' }}
          </span>
        </td>
        <td class="text-muted" style="font-size:12px">
          {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
        </td>
        <td class="text-muted" style="font-size:12px">{{ $user->created_at->format('d M Y') }}</td>
        <td>
          <div style="display:flex;gap:5px">
            <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-secondary btn-sm">✏️ Edit</a>
            @if($user->id !== auth('admin')->id())
            <form method="POST" action="{{ route('admin.users.destroy',$user) }}" onsubmit="return confirm('Delete this user?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">🗑</button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text3)">No users found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $users->withQueryString()->links() }}</div>
@endsection
