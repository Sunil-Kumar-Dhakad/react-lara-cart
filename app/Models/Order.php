<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'subtotal',
        'tax',
        'total',
        'status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'tax'      => 'float',
        'total'    => 'float',
    ];

    const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    const PAYMENT_STATUSES = ['pending', 'paid', 'refunded'];

    // ── Relationships ──────────────────────────────────────────────────────
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    public function getStatusColorAttribute(): string
    {
        return [
            'pending'    => 'yellow',
            'processing' => 'blue',
            'shipped'    => 'purple',
            'delivered'  => 'green',
            'cancelled'  => 'red',
        ][$this->status] ?? 'gray';
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return [
            'pending'  => 'yellow',
            'paid'     => 'green',
            'refunded' => 'blue',
        ][$this->payment_status] ?? 'gray';
    }
}
