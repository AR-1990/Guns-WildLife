<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPartnerInvestment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'partner_id',
        'amount',
        'on_hold_amount',
        'investment_date',
        'ownership_percentage',
        'profit_type',
        'profit_value',
        'is_profit_manual',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'on_hold_amount' => 'decimal:2',
            'investment_date' => 'date',
            'ownership_percentage' => 'decimal:4',
            'profit_value' => 'decimal:2',
            'is_profit_manual' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
