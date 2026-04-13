<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_id',
        'gateway',
        'gateway_ref',
        'amount',
        'currency',
        'status',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'amount'  => 'float',
        'paid_at' => 'datetime',
        'meta'    => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getGatewayLabelAttribute(): string
    {
        return ucfirst($this->gateway ?? '—');
    }
}
