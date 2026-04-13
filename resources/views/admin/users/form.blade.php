@extends('admin.layouts.app')
@section('title', $user ? 'Edit User' : 'Add User')

@section('content')
<div class="page-header">
  <div>
    <div class="page-title">{{ $user ? 'Edit User: '.$user->name : 'Add New User' }}</div>
  </div>
  <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">← Back</a>
</div>

<form method="POST" action="{{ $user ? route('admin.users.update',$user) : route('admin.users.store') }}" style="max-width:560px">
  @csrf
  @if($user) @method('PUT') @endif

  <div class="card card-body" style="margin-bottom:16px">
    <div style="font-size:13px;font-weight:700;margin-bottom:16px">Account Details</div>

    <div class="form-group">
      <label class="form-label">Full Name *</label>
      <input class="form-input" name="name" value="{{ old('name',$user->name??'') }}" placeholder="John Doe" required>
      @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label class="form-label">Email Address *</label>
      <input class="form-input" type="email" name="email" value="{{ old('email',$user->email??'') }}" placeholder="john@nexus.io" required>
      @error('email')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">{{ $user ? 'New Password' : 'Password *' }}</label>
        <input class="form-input" type="password" name="password" placeholder="{{ $user ? 'Leave blank to keep' : 'Min. 8 characters' }}" {{ $user ? '' : 'required' }}>
        @error('password')<div class="form-error">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input class="form-input" type="password" name="password_confirmation" placeholder="Re-enter password">
      </div>
    </div>
  </div>

  <div class="card card-body" style="margin-bottom:16px">
    <div style="font-size:13px;font-weight:700;margin-bottom:16px">Permissions</div>

    <div class="form-group">
      <label class="form-label">Role *</label>
      <select name="role" class="form-input" required>
        @foreach(\App\Models\User::ROLES as $key => $label)
          <option value="{{ $key }}" {{ old('role',$user->role??'')===$key?'selected':'' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('role')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    @if($user)
    <div style="display:flex;align-items:center;gap:10px">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" id="is_active" value="1" style="width:16px;height:16px;accent-color:var(--accent)"
        {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
      <label for="is_active" style="font-size:13px;color:var(--text2);cursor:pointer">Account is active</label>
    </div>
    @endif
  </div>

  {{-- Role permissions info --}}
  <div style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:14px;margin-bottom:16px;font-size:12px;color:var(--text2)">
    <div style="font-weight:700;margin-bottom:8px;color:var(--accent2)">Role Permissions</div>
    <div><strong style="color:#c4b5fd">Super Admin</strong> — Full access to everything including user management</div>
    <div style="margin-top:4px"><strong style="color:#a5b4fc">Admin</strong> — Products, orders, employees (no user management)</div>
    <div style="margin-top:4px"><strong style="color:#6ee7b7">Accountant</strong> — Orders and payment records</div>
    <div style="margin-top:4px"><strong style="color:#fcd34d">HR Manager</strong> — Employees and attendance</div>
    <div style="margin-top:4px"><strong style="color:#94a3b8">Viewer</strong> — Dashboard read-only</div>
  </div>

  <div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">{{ $user ? '💾 Save Changes' : '✓ Create User' }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</form>
@endsection
