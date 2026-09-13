<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_unit_id',
        'partner_id',
        'product_name',
        'serial_number',
        'unit',
        'quantity',
        'price',
        'unit_purchase_price',
        'total',
        'partner_profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'unit_purchase_price' => 'decimal:2',
            'total' => 'decimal:2',
            'partner_profit' => 'decimal:2',
        ];
    }

    public function getHistoricalCostAttribute(): float
    {
        return (float) ($this->unit_purchase_price ?? $this->product?->unit_purchase_price ?? 0);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function stockAllocations(): HasMany
    {
        return $this->hasMany(SaleItemStockAllocation::class);
    }
}
