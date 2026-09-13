<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerInvestmentEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_investment_entry_id',
        'product_id',
        'amount',
        'on_hold_amount',
        'wallet_used_amount',
        'profit_type',
        'profit_value',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'on_hold_amount' => 'decimal:2',
            'wallet_used_amount' => 'decimal:2',
            'profit_value' => 'decimal:2',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(PartnerInvestmentEntry::class, 'partner_investment_entry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
