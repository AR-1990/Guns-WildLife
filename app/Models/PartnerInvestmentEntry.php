<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerInvestmentEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'partner_id',
        'khata_id',
        'investment_date',
        'total_amount',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'investment_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function khata(): BelongsTo
    {
        return $this->belongsTo(Khata::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PartnerInvestmentEntryItem::class)->orderBy('id');
    }
}
