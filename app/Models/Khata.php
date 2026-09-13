<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Khata extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function investmentEntries(): HasMany
    {
        return $this->hasMany(PartnerInvestmentEntry::class)->orderByDesc('id');
    }
}
