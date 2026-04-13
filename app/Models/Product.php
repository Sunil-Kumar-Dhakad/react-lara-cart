<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'description',
        'price',
        'original_price',
        'stock',
        'status',
        'image_url',
        'badge',
        'rating',
        'reviews_count',
    ];

    protected $casts = [
        'price'          => 'float',
        'original_price' => 'float',
        'stock'          => 'integer',
        'rating'         => 'float',
        'reviews_count'  => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
