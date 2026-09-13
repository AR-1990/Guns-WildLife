<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'receipt_no',
        'document_type',
        'customer_name',
        'customer_phone',
        'sale_date',
        'reference',
        'subtotal',
        'discount_percent',
        'tax_percent',
        'grand_total',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
