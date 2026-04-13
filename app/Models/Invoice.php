<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'order_id',
        'customer_name',
        'customer_email',
        'subtotal',
        'tax',
        'total',
        'status',
        'due_date',
        'issued_date',
        'notes',
    ];

    protected $casts = [
        'subtotal'    => 'float',
        'tax'         => 'float',
        'total'       => 'float',
        'due_date'    => 'date',
        'issued_date' => 'date',
    ];

    const STATUSES = ['pending', 'paid', 'overdue', 'cancelled'];

    // ── Relationships ──────────────────────────────────────────────────────
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('due_date', '<', now());
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date?->isPast();
    }

    public function getStatusColorAttribute(): string
    {
        return [
            'pending'   => 'yellow',
            'paid'      => 'green',
            'overdue'   => 'red',
            'cancelled' => 'gray',
        ][$this->status] ?? 'gray';
    }
}
