@extends('admin.layouts.app')
@section('title', $employee ? 'Edit Employee' : 'Add Employee')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ $employee ? 'Edit: '.$employee->name : 'Add New Employee' }}</div>
  </div>
  <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">← Back</a>
</div>

<form method="POST" action="{{ $employee ? route('admin.employees.update',$employee) : route('admin.employees.store') }}" style="max-width:700px">
  @csrf
  @if($employee) @method('PUT') @endif

  <div class="card card-body" style="margin-bottom:16px">
    <div style="font-size:13px;font-weight:700;margin-bottom:16px">Personal Details</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input class="form-input" name="name" value="{{ old('name',$employee->name??'') }}" placeholder="Arjun Sharma" required>
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Email *</label>
        <input class="form-input" type="email" name="email" value="{{ old('email',$employee->email??'') }}" placeholder="arjun@nexus.io" required>
        @error('email')<div class="form-error">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input class="form-input" name="phone" value="{{ old('phone',$employee->phone??'') }}" placeholder="+91 9876543210">
      </div>
      <div class="form-group">
        <label class="form-label">Joined Date</label>
        <input class="form-input" type="date" name="joined_at" value="{{ old('joined_at', $employee->joined_at?->format('Y-m-d')??'') }}">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Address</label>
      <textarea class="form-input" name="address" rows="2" placeholder="123 MG Road, Indore…">{{ old('address',$employee->address??'') }}</textarea>
    </div>
  </div>

  <div class="card card-body" style="margin-bottom:16px">
    <div style="font-size:13px;font-weight:700;margin-bottom:16px">Job Details</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Job Role *</label>
        <input class="form-input" name="role" value="{{ old('role',$employee->role??'') }}" placeholder="Senior Developer" required>
        @error('role')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Department *</label>
        <select class="form-input" name="department" required>
          <option value="">Select…</option>
          @foreach(['Engineering','Design','Sales','Finance','HR','Operations','Marketing','DevOps'] as $d)
            <option value="{{ $d }}" {{ old('department',$employee->department??'')===$d?'selected':'' }}>{{ $d }}</option>
          @endforeach
        </select>
        @error('department')<div class="form-error">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Monthly Salary (₹) *</label>
        <input class="form-input" type="number" name="salary" value="{{ old('salary',$employee->salary??'') }}" placeholder="75000" required>
        @error('salary')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Status *</label>
        <select class="form-input" name="status" required>
          <option value="active"   {{ old('status',$employee->status??'active')==='active'?'selected':'' }}>Active</option>
          <option value="inactive" {{ old('status',$employee->status??'')==='inactive'?'selected':'' }}>Inactive</option>
        </select>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">{{ $employee ? '💾 Save Changes' : '✓ Add Employee' }}</button>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</form>
@endsection
