<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'partner_id',
        'ownership_type',
        'unit',
        'secondary_uom',
        'pack_size',
        'barcode',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'tax_rate',
        'min_stock_level',
        'max_stock_level',
        'batch_expiry',
        'status',
        'commission_type',
        'commission_percent',
        'commission_fixed',
        'image_path',
        'is_serialized',
        'requires_license',
        'weapon_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'tax_rate' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_fixed' => 'decimal:2',
            'batch_expiry' => 'boolean',
            'is_serialized' => 'boolean',
            'requires_license' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function availableUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class)->where('status', 'available');
    }

    public function partnerInvestments(): HasMany
    {
        return $this->hasMany(ProductPartnerInvestment::class);
    }

    public function restockEntries(): HasMany
    {
        return $this->hasMany(ProductRestockEntry::class);
    }

    public function getPartnerNamesAttribute(): string
    {
        $investments = $this->relationLoaded('partnerInvestments')
            ? $this->partnerInvestments
            : $this->partnerInvestments()->with('partner')->get();

        if ($investments->isEmpty()) {
            return 'Company Stock';
        }

        $names = $investments
            ->map(fn (ProductPartnerInvestment $investment) => $investment->partner?->name)
            ->filter()
            ->unique()
            ->values();

        if ($names->isNotEmpty()) {
            return $names->implode(', ');
        }

        return $this->partner?->name ?: 'Partner Stock';
    }

    public function getUnitPurchasePriceAttribute(): float
    {
        return (float) $this->purchase_price;
    }
}
