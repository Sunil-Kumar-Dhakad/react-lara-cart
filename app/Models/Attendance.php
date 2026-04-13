<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id','date','check_in','check_out',
        'status','note','hours_worked',
    ];

    protected $casts = [
        'date'      => 'date',
        'check_in'  => 'datetime',
        'check_out' => 'datetime',
    ];

    const STATUSES = ['present','absent','half_day','late','on_leave'];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function getHoursWorkedAttribute(): ?float
    {
        if ($this->check_in && $this->check_out) {
            return round($this->check_in->diffInMinutes($this->check_out) / 60, 2);
        }
        return null;
    }
}
