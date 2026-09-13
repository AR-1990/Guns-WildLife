<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'company_name', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_SALESMAN = 'salesman';
    public const ROLE_PARTNER = 'partner';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSalesman(): bool
    {
        return $this->role === self::ROLE_SALESMAN;
    }

    public function isPartner(): bool
    {
        return $this->role === self::ROLE_PARTNER;
    }

    public function roleLabel(): string
    {
        return str($this->role)->replace('_', ' ')->title()->toString();
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_SALESMAN => 'admin.dashboard',
            default => 'admin.login',
        };
    }

    public function partnerProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'partner_id');
    }

    public function productPartnerInvestments(): HasMany
    {
        return $this->hasMany(ProductPartnerInvestment::class, 'partner_id');
    }

    public function partnerInvestmentEntries(): HasMany
    {
        return $this->hasMany(PartnerInvestmentEntry::class, 'partner_id');
    }

    public function salaryRecords(): HasMany
    {
        return $this->hasMany(Salary::class, 'employee_id');
    }

    public function partnerWithdrawals(): HasMany
    {
        return $this->hasMany(PartnerWithdrawal::class, 'partner_id');
    }

    public function deletedEmailValue(): string
    {
        $timestamp = now()->format('YmdHis');

        return 'deleted-user-' . $this->id . '-' . $timestamp . '@deleted.local';
    }
}
