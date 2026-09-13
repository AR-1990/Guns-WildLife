<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_restock_entry_id',
        'weapon_number',
        'status',
        'unit_purchase_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_purchase_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function restockEntry(): BelongsTo
    {
        return $this->belongsTo(ProductRestockEntry::class, 'product_restock_entry_id');
    }
}
