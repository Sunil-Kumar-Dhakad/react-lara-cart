@extends('admin.layouts.app')
@section('title','Employees')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Employees</div>
    <div class="page-subtitle">{{ $employees->total() }} total employees</div>
  </div>
  <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">+ Add Employee</a>
</div>

<form method="GET" class="filter-bar">
  <div class="search-wrap">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input name="search" value="{{ request('search') }}" placeholder="Search name or email…">
  </div>
  <select name="dept" class="filter-input">
    <option value="">All Departments</option>
    @foreach($departments as $d)
      <option value="{{ $d }}" {{ request('dept')===$d?'selected':'' }}>{{ $d }}</option>
    @endforeach
  </select>
  <select name="status" class="filter-input">
    <option value="">All Status</option>
    <option value="active"   {{ request('status')==='active'?'selected':'' }}>Active</option>
    <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Filter</button>
  <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr><th>Employee</th><th>Role</th><th>Department</th><th>Salary</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      @forelse($employees as $emp)
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0">
              {{ strtoupper(substr($emp->name,0,2)) }}
            </div>
            <div>
              <div style="font-weight:600">{{ $emp->name }}</div>
              <div class="text-muted" style="font-size:11px">{{ $emp->email }}</div>
            </div>
          </div>
        </td>
        <td>{{ $emp->role }}</td>
        <td><span class="badge badge-blue">{{ $emp->department }}</span></td>
        <td class="fw-700">${{ number_format($emp->salary,0) }}</td>
        <td class="text-muted" style="font-size:12px">{{ $emp->joined_at ? $emp->joined_at->format('d M Y') : '—' }}</td>
        <td><span class="badge {{ $emp->status==='active'?'badge-green':'badge-red' }}">{{ $emp->status }}</span></td>
        <td>
          <div style="display:flex;gap:5px">
            <a href="{{ route('admin.employees.edit',$emp) }}" class="btn btn-secondary btn-sm btn-icon">✏️</a>
            <form method="POST" action="{{ route('admin.employees.toggle',$emp) }}" style="display:inline">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-warning btn-sm btn-icon" title="Toggle">
                {{ $emp->status==='active'?'🔴':'🟢' }}
              </button>
            </form>
            <form method="POST" action="{{ route('admin.employees.destroy',$emp) }}" style="display:inline" onsubmit="return confirm('Remove this employee?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm btn-icon">🗑</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text3)">No employees found</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="pagination">{{ $employees->withQueryString()->links() }}</div>
@endsection
