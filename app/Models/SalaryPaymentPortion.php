<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPaymentPortion extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_id',
        'portion_no',
        'amount',
        'paid_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_date' => 'date',
        ];
    }

    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
