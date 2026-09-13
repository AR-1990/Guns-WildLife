<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountClosing extends Model
{
    protected $fillable = [
        'period_name',
        'closing_date',
        'notes',
        'total_sales_count',
        'total_revenue',
        'total_subtotal',
        'total_discount',
        'total_shipping',
        'total_tax',
        'total_cogs',
        'total_gross_profit',
        'gross_margin_percent',
        'expense_records',
        'total_expenses',
        'salary_records',
        'total_salaries',
        'total_operating_cost',
        'withdrawal_records',
        'total_withdrawals',
        'total_withdrawn_actual',
        'total_withdrawn_profit',
        'total_partner_profit',
        'total_partner_deductions',
        'total_partner_expense_deductions',
        'total_partner_salary_deductions',
        'total_admin_profit',
        'total_net_operating_income',
        'cashbook_balance',
        'total_released_actual',
        'total_partner_invested_actual',
        'total_net_payable',
        'total_partner_unified_net_payable',
        'created_by',
    ];

    protected $casts = [
        'closing_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function partnerBalances(): HasMany
    {
        return $this->hasMany(PartnerClosingBalance::class, 'account_closing_id');
    }
}
