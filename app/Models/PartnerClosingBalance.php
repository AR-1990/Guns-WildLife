<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerClosingBalance extends Model
{
    protected $fillable = [
        'account_closing_id',
        'partner_id',
        'sales_count',
        'total_invested_actual',
        'released_actual_amount',
        'earned_profit_amount',
        'expense_deduction_amount',
        'salary_deduction_amount',
        'total_deduction_amount',
        'withdrawn_actual_amount',
        'withdrawn_profit_amount',
        'total_withdrawn_amount',
        'net_payable_amount',
        'unified_net_payable_amount',
        'notes',
    ];

    public function accountClosing(): BelongsTo
    {
        return $this->belongsTo(AccountClosing::class, 'account_closing_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
