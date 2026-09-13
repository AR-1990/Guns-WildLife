<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRestockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'remaining_quantity',
        'unit_purchase_price',
        'restocked_at',
        'notes',
        'created_by',
        'is_opening',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'remaining_quantity' => 'integer',
            'unit_purchase_price' => 'decimal:2',
            'restocked_at' => 'date',
            'is_opening' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saleItemAllocations(): HasMany
    {
        return $this->hasMany(SaleItemStockAllocation::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }
}
