<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Nexus</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f1117;--card:#1a1d27;--border:#252840;--border2:#2d3148;--accent:#6366f1;--accent2:#818cf8;--text:#e2e8f0;--text2:#94a3b8;--text3:#64748b;--red:#ef4444;--green:#10b981}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(99,102,241,.08) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(139,92,246,.06) 0%,transparent 50%);pointer-events:none}
.login-card{background:var(--card);border:1px solid var(--border2);border-radius:16px;padding:40px;width:100%;max-width:400px;position:relative}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:32px;justify-content:center}
.logo-mark{width:42px;height:42px;background:linear-gradient(135deg,var(--accent),#7c3aed);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:18px;color:#fff}
.logo-text{font-size:20px;font-weight:800;color:var(--text)}
.logo-sub{font-size:11px;color:var(--text3);margin-top:1px}
h1{font-size:22px;font-weight:700;margin-bottom:6px;text-align:center}
.sub{font-size:13px;color:var(--text2);text-align:center;margin-bottom:28px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px}
.form-input{width:100%;padding:10px 14px;background:#1e2235;border:1px solid var(--border2);border-radius:9px;color:var(--text);font-size:14px;transition:border .15s;outline:none}
.form-input:focus{border-color:var(--accent)}
.form-input::placeholder{color:var(--text3)}
.btn{width:100%;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;transition:all .15s;margin-top:6px}
.btn:hover{background:var(--accent2)}
.alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#6ee7b7}
.check-row{display:flex;align-items:center;gap:8px;margin-bottom:20px}
.check-row input{width:15px;height:15px;accent-color:var(--accent)}
.check-row label{font-size:13px;color:var(--text2)}
.hint-box{margin-top:24px;padding:12px 16px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:8px}
.hint-title{font-size:10px;font-weight:700;color:var(--accent2);text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px}
.hint-row{display:flex;justify-content:space-between;font-size:11px;color:var(--text3);margin-bottom:3px}
.hint-row span:last-child{color:var(--text2)}
</style>
</head>
<body>
<div class="login-card">
  <div class="logo">
    <div class="logo-mark">N</div>
    <div>
      <div class="logo-text">Nexus Admin</div>
      <div class="logo-sub">Management Panel</div>
    </div>
  </div>

  @if(session('error'))
    <div class="alert alert-error">✗ {{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-error">✗ {{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('admin.login') }}">
    @csrf
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="admin@nexus.io" required autofocus>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input class="form-input" type="password" name="password" placeholder="••••••••" required>
    </div>
    <div class="check-row">
      <input type="checkbox" id="remember" name="remember">
      <label for="remember">Remember me for 30 days</label>
    </div>
    <button type="submit" class="btn">Sign in to Admin Panel →</button>
  </form>

  <div class="hint-box">
    <div class="hint-title">Demo Credentials</div>
    <div class="hint-row"><span>Super Admin</span><span>superadmin@nexus.io / password</span></div>
    <div class="hint-row"><span>Admin</span><span>admin@nexus.io / password</span></div>
    <div class="hint-row"><span>Accountant</span><span>accounts@nexus.io / password</span></div>
    <div class="hint-row"><span>HR Manager</span><span>hr@nexus.io / password</span></div>
  </div>
</div>
</body>
</html>
