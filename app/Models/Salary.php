<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'salary_month',
        'basic_salary',
        'advance_salary',
        'advance_adjustment_amount',
        'advance_waived_amount',
        'loan_amount',
        'loan_adjustment_amount',
        'loan_waived_amount',
        'bonus',
        'deduction',
        'net_salary',
        'account_user_id',
        'salary_waived_amount',
        'paid_amount',
        'balance_amount',
        'paid_date',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'advance_salary' => 'decimal:2',
            'advance_adjustment_amount' => 'decimal:2',
            'advance_waived_amount' => 'decimal:2',
            'loan_amount' => 'decimal:2',
            'loan_adjustment_amount' => 'decimal:2',
            'loan_waived_amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'salary_waived_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'paid_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accountUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_user_id');
    }

    public function paymentPortions(): HasMany
    {
        return $this->hasMany(SalaryPaymentPortion::class)->orderBy('portion_no');
    }
}
