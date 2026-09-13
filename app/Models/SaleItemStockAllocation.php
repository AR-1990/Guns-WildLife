<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemStockAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'product_restock_entry_id',
        'quantity',
        'unit_purchase_price',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_purchase_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function restockEntry(): BelongsTo
    {
        return $this->belongsTo(ProductRestockEntry::class, 'product_restock_entry_id');
    }
}
