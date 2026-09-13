<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItemPartnerProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_item_id',
        'partner_id',
        'investment_amount',
        'ownership_percentage',
        'profit_amount',
    ];

    protected function casts(): array
    {
        return [
            'investment_amount' => 'decimal:2',
            'ownership_percentage' => 'decimal:4',
            'profit_amount' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
