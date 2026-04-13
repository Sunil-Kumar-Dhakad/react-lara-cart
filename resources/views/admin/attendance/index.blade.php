@extends('admin.layouts.app')
@section('title','Attendance')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">Attendance</div>
    <div class="page-subtitle">{{ $date->format('l, d F Y') }}</div>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('admin.attendance.report') }}" class="btn btn-secondary">📊 Monthly Report</a>
  </div>
</div>

{{-- Date picker --}}
<form method="GET" class="filter-bar" style="margin-bottom:20px">
  <div style="display:flex;align-items:center;gap:8px">
    <label style="font-size:12px;color:var(--text2)">Date</label>
    <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="filter-input" onchange="this.form.submit()">
  </div>
  <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary btn-sm">Today</a>
</form>

{{-- Bulk mark --}}
<div class="card card-body" style="margin-bottom:20px">
  <div style="font-size:13px;font-weight:700;margin-bottom:12px">Bulk Mark Attendance</div>
  <form method="POST" action="{{ route('admin.attendance.bulk') }}" id="bulkForm">
    @csrf
    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);cursor:pointer">
        <input type="checkbox" id="selectAll" style="width:15px;height:15px;accent-color:var(--accent)">
        Select All
      </label>
      <select name="status" class="filter-input" required>
        <option value="present">Present</option>
        <option value="absent">Absent</option>
        <option value="on_leave">On Leave</option>
        <option value="half_day">Half Day</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Mark Selected</button>
    </div>
  </form>
</div>

{{-- Daily attendance table --}}
<div class="card table-wrap">
  <table>
    <thead>
      <tr>
        <th style="width:36px"><input type="checkbox" id="selectAllTh" style="width:15px;height:15px;accent-color:var(--accent)"></th>
        <th>Employee</th><th>Department</th><th>Status</th>
        <th>Check In</th><th>Check Out</th><th>Note</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($employees as $emp)
      @php $rec = $records->get($emp->id) @endphp
      <tr>
        <td>
          <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" form="bulkForm"
            class="emp-check" style="width:15px;height:15px;accent-color:var(--accent)">
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:9px">
            <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:#fff;flex-shrink:0">
              {{ strtoupper(substr($emp->name,0,2)) }}
            </div>
            <div>
              <div style="font-weight:600;font-size:13px">{{ $emp->name }}</div>
              <div class="text-muted" style="font-size:11px">{{ $emp->role }}</div>
            </div>
          </div>
        </td>
        <td><span class="badge badge-blue">{{ $emp->department }}</span></td>
        <td>
          @if($rec)
            @php $sc=['present'=>'green','absent'=>'red','half_day'=>'yellow','late'=>'yellow','on_leave'=>'blue'][$rec->status] @endphp
            <span class="badge badge-{{ $sc }}">{{ str_replace('_',' ',$rec->status) }}</span>
          @else
            <span class="badge badge-gray">Not marked</span>
          @endif
        </td>
        <td class="text-muted" style="font-size:12px">{{ $rec?->check_in ?? '—' }}</td>
        <td class="text-muted" style="font-size:12px">{{ $rec?->check_out ?? '—' }}</td>
        <td class="text-muted" style="font-size:12px;max-width:100px;overflow:hidden;text-overflow:ellipsis">{{ $rec?->note ?? '—' }}</td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="openMarkModal({{ $emp->id }},'{{ addslashes($emp->name) }}','{{ $rec?->status ?? '' }}','{{ $rec?->check_in ?? '' }}','{{ $rec?->check_out ?? '' }}','{{ $rec?->note ?? '' }}')">
            {{ $rec ? '✏️ Edit' : '+ Mark' }}
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- Mark modal --}}
<div class="modal-overlay" id="markModal">
  <div class="modal">
    <div class="modal-header">
      <span style="font-size:15px;font-weight:700" id="modalTitle">Mark Attendance</span>
      <button onclick="closeModal('markModal')" style="background:none;border:none;color:var(--text3);font-size:22px;cursor:pointer">×</button>
    </div>
    <form method="POST" action="{{ route('admin.attendance.store') }}">
      @csrf
      <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
      <input type="hidden" name="employee_id" id="modalEmpId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Status *</label>
          <select name="status" id="modalStatus" class="form-input" required>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="half_day">Half Day</option>
            <option value="late">Late</option>
            <option value="on_leave">On Leave</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Check In</label>
            <input type="time" name="check_in" id="modalIn" class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Check Out</label>
            <input type="time" name="check_out" id="modalOut" class="form-input">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Note</label>
          <input type="text" name="note" id="modalNote" class="form-input" placeholder="Optional remark…">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('markModal')" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Attendance</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openMarkModal(id, name, status, checkIn, checkOut, note) {
  document.getElementById('modalEmpId').value  = id;
  document.getElementById('modalTitle').textContent = 'Mark: ' + name;
  document.getElementById('modalStatus').value = status || 'present';
  document.getElementById('modalIn').value     = checkIn  || '';
  document.getElementById('modalOut').value    = checkOut || '';
  document.getElementById('modalNote').value   = note     || '';
  openModal('markModal');
}

// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
  document.querySelectorAll('.emp-check').forEach(c => c.checked = this.checked);
});
document.getElementById('selectAllTh')?.addEventListener('change', function() {
  document.querySelectorAll('.emp-check').forEach(c => c.checked = this.checked);
  if(document.getElementById('selectAll')) document.getElementById('selectAll').checked = this.checked;
});
</script>
@endpush
