<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','email','phone','role','department',
        'salary','status','joined_at','address','user_id','avatar',
    ];

    protected $casts = [
        'salary'    => 'float',
        'joined_at' => 'date',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function attendance()  { return $this->hasMany(Attendance::class); }

    public function getPresentThisMonthAttribute(): int
    {
        return $this->attendance()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', 'present')
            ->count();
    }
}
