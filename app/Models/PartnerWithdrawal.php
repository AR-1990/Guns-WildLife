<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerWithdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'partner_id',
        'amount',
        'actual_component',
        'profit_component',
        'on_hold_component',
        'investment_component',
        'withdrawal_date',
        'entry_type',
        'available_before',
        'available_after',
        'source_breakdown',
        'investment_entry_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'actual_component' => 'decimal:2',
            'profit_component' => 'decimal:2',
            'on_hold_component' => 'decimal:2',
            'investment_component' => 'decimal:2',
            'withdrawal_date' => 'date',
            'available_before' => 'decimal:2',
            'available_after' => 'decimal:2',
            'source_breakdown' => 'array',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function investmentEntry(): BelongsTo
    {
        return $this->belongsTo(PartnerInvestmentEntry::class, 'investment_entry_id');
    }
}
