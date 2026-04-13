@extends('admin.layouts.app')
@section('title','Attendance Report')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Monthly Attendance Report</div>
    <div class="page-subtitle">{{ $start->format('d M') }} — {{ $end->format('d M Y') }}</div>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <form method="GET" style="display:flex;gap:8px;align-items:center">
      <input type="month" name="month" value="{{ $month }}" class="filter-input" onchange="this.form.submit()">
    </form>
    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">← Daily View</a>
  </div>
</div>

{{-- Summary stats --}}
@php
  $totalPresent  = $data->sum('present');
  $totalAbsent   = $data->sum('absent');
  $totalHalfDay  = $data->sum('half_day');
  $totalLeave    = $data->sum('leave');
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
  <div class="stat-card stat-green"><div class="stat-label">Total Present</div><div class="stat-value">{{ $totalPresent }}</div></div>
  <div class="stat-card stat-red"><div class="stat-label">Total Absent</div><div class="stat-value">{{ $totalAbsent }}</div></div>
  <div class="stat-card stat-yellow"><div class="stat-label">Half Days</div><div class="stat-value">{{ $totalHalfDay }}</div></div>
  <div class="stat-card stat-blue"><div class="stat-label">On Leave</div><div class="stat-value">{{ $totalLeave }}</div></div>
</div>

<div class="card table-wrap">
  <div class="card-header"><span class="card-title">Employee-wise Report — {{ $start->format('F Y') }}</span></div>
  <table>
    <thead>
      <tr>
        <th>Employee</th><th>Department</th><th>Working Days</th>
        <th>Present</th><th>Absent</th><th>Half Day</th><th>Late</th><th>Leave</th>
        <th>Attendance %</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $row)
      @php
        $pct = $row['days'] > 0 ? round(($row['present'] + $row['half_day']*0.5) / $row['days'] * 100) : 0;
        $color = $pct >= 90 ? 'var(--green)' : ($pct >= 75 ? 'var(--yellow)' : 'var(--red)');
      @endphp
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:9px">
            <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
              {{ strtoupper(substr($row['employee']->name,0,2)) }}
            </div>
            <span style="font-weight:600;font-size:13px">{{ $row['employee']->name }}</span>
          </div>
        </td>
        <td><span class="badge badge-blue">{{ $row['employee']->department }}</span></td>
        <td class="text-muted">{{ $row['days'] }}</td>
        <td class="text-success fw-700">{{ $row['present'] }}</td>
        <td class="text-danger">{{ $row['absent'] }}</td>
        <td class="text-warning">{{ $row['half_day'] }}</td>
        <td class="text-warning">{{ $row['late'] }}</td>
        <td class="text-info">{{ $row['leave'] }}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <div style="flex:1;height:5px;background:var(--border);border-radius:3px;overflow:hidden;min-width:60px">
              <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};border-radius:3px"></div>
            </div>
            <span style="font-weight:700;font-size:13px;color:{{ $color }};min-width:36px">{{ $pct }}%</span>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
