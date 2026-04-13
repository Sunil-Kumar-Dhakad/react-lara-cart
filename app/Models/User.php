<?php
// app/Models/User.php  (CHANGED FILE)

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasFactory;

    // Use 'admin' guard for the admin panel session
    protected $guard = 'admin';

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'email_otp', 'email_otp_expires', 'email_verified_at',
        'is_active', 'last_login_at', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token', 'email_otp'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_otp_expires' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
    ];

    // ── Role constants ────────────────────────────────────────────────────
    const ROLES = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'accountant'  => 'Accountant',
        'hr'          => 'HR Manager',
        'manager'     => 'Manager',
        'viewer'      => 'Viewer',
    ];

    // ── Role helpers ──────────────────────────────────────────────────────
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return in_array($this->role, ['super_admin', 'admin']); }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
