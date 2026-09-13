<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\PartnerInvestmentEntryItem;
use App\Models\PartnerWithdrawal;
use App\Models\ProductPartnerInvestment;
use App\Models\SalaryPaymentPortion;
use App\Models\User;
use Illuminate\Support\Collection;

class PartnerAccountSummary
{
    public static function totalActual(?int $partnerId = null): float
    {
        return (float) ProductPartnerInvestment::query()
            ->whereHas('product')
            ->when($partnerId, fn ($query) => $query->where('partner_id', $partnerId))
            ->sum('amount');
    }

    public static function totalEarned(?int $partnerId = null): float
    {
        return 0.0;
    }

    public static function totalWithdrawn(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return (float) PartnerWithdrawal::query()
            ->when($partnerId, fn ($query) => $query->where('partner_id', $partnerId))
            ->when($fromDate, fn ($query) => $query->whereDate('withdrawal_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('withdrawal_date', '<=', $toDate))
            ->sum('amount');
    }

    public static function totalPartnerWithdrawn(?int $partnerId = null): float
    {
        return (float) PartnerWithdrawal::query()
            ->when($partnerId, fn ($query) => $query->where('partner_id', $partnerId))
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->sum('amount');
    }

    public static function totalActualWithdrawn(?int $partnerId = null): float
    {
        return (float) PartnerWithdrawal::query()
            ->when($partnerId, fn ($query) => $query->where('partner_id', $partnerId))
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->sum('actual_component');
    }

    public static function totalProfitWithdrawn(?int $partnerId = null): float
    {
        return (float) PartnerWithdrawal::query()
            ->when($partnerId, fn ($query) => $query->where('partner_id', $partnerId))
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->sum('profit_component');
    }

    public static function availableActual(?int $partnerId = null): float
    {
        $actualPool = max(0, self::totalActual($partnerId) - self::totalInvestedSourceDeductions($partnerId));

        return max(0, $actualPool - self::totalActualWithdrawn($partnerId));
    }

    public static function availableProfit(?int $partnerId = null): float
    {
        return 0.0;
    }

    public static function availableBalance(?int $partnerId = null): float
    {
        return round(self::availableActual($partnerId) + self::availableProfit($partnerId), 2);
    }

    public static function availableOnHold(?int $partnerId = null): float
    {
        $items = PartnerInvestmentEntryItem::query()
            ->when($partnerId, function ($query) use ($partnerId) {
                $query->whereHas('entry', fn ($nested) => $nested->where('partner_id', $partnerId));
            })
            ->get();

        return round(max(
            0,
            (float) $items->sum('on_hold_amount') - (float) $items->sum('wallet_used_amount')
        ), 2);
    }

    public static function actualByPartner(): Collection
    {
        return ProductPartnerInvestment::query()
            ->whereHas('product')
            ->selectRaw('partner_id, SUM(amount) as total_actual')
            ->groupBy('partner_id')
            ->pluck('total_actual', 'partner_id');
    }

    public static function releasedActualByPartner(): Collection
    {
        return collect();
    }

    public static function earnedByPartner(): Collection
    {
        return collect();
    }

    public static function withdrawnByPartner(): Collection
    {
        return PartnerWithdrawal::query()
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->selectRaw('partner_id, SUM(amount) as total_withdrawn')
            ->groupBy('partner_id')
            ->pluck('total_withdrawn', 'partner_id');
    }

    public static function actualWithdrawnByPartner(): Collection
    {
        return PartnerWithdrawal::query()
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->selectRaw('partner_id, SUM(actual_component) as total_withdrawn')
            ->groupBy('partner_id')
            ->pluck('total_withdrawn', 'partner_id');
    }

    public static function profitWithdrawnByPartner(): Collection
    {
        return PartnerWithdrawal::query()
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->selectRaw('partner_id, SUM(profit_component) as total_withdrawn')
            ->groupBy('partner_id')
            ->pluck('total_withdrawn', 'partner_id');
    }

    public static function lastWithdrawalDatesByPartner(): Collection
    {
        return PartnerWithdrawal::query()
            ->whereHas('partner', fn ($query) => $query->where('role', User::ROLE_PARTNER))
            ->selectRaw('partner_id, MAX(withdrawal_date) as last_withdrawal_date')
            ->groupBy('partner_id')
            ->pluck('last_withdrawal_date', 'partner_id');
    }

    public static function totalReleasedActual(?int $partnerId = null): float
    {
        return 0.0;
    }

    public static function totalDeductions(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return round(
            self::totalExpenseDeductions($partnerId, $fromDate, $toDate),
            2
        );
    }

    public static function totalProfitSourceDeductions(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return self::sourceFilteredDeductionSum('profit', $partnerId, $fromDate, $toDate);
    }

    public static function totalInvestedSourceDeductions(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return self::sourceFilteredDeductionSum('invested', $partnerId, $fromDate, $toDate);
    }

    public static function totalExpenseDeductions(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return (float) Expense::query()
            ->when($partnerId, fn ($query) => $query->where('account_user_id', $partnerId))
            ->when($fromDate, fn ($query) => $query->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('expense_date', '<=', $toDate))
            ->sum('amount');
    }

    public static function totalSalaryDeductions(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        return (float) SalaryPaymentPortion::query()
            ->whereHas('salary', fn ($query) => $query->when($partnerId, fn ($nested) => $nested->where('account_user_id', $partnerId)))
            ->when($fromDate, fn ($query) => $query->whereDate('paid_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('paid_date', '<=', $toDate))
            ->sum('amount');
    }

    public static function deductionEntries(?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): Collection
    {
        $expenseEntries = Expense::query()
            ->with('accountUser')
            ->when($partnerId, fn ($query) => $query->where('account_user_id', $partnerId))
            ->when($fromDate, fn ($query) => $query->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('expense_date', '<=', $toDate))
            ->get()
            ->map(fn (Expense $expense) => [
                'type' => 'expense',
                'partner_id' => (int) ($expense->account_user_id ?? 0),
                'partner_name' => $expense->accountUser?->name ?: 'Partner',
                'date' => $expense->expense_date?->toDateString(),
                'amount' => (float) $expense->amount,
                'label' => $expense->title ?: 'Expense',
                'notes' => $expense->notes,
                'id' => $expense->id,
                'deduct_source' => self::extractDeductSource($expense->notes),
            ]);

        $salaryEntries = SalaryPaymentPortion::query()
            ->with(['salary', 'salary.accountUser'])
            ->whereHas('salary', fn ($query) => $query->when($partnerId, fn ($nested) => $nested->where('account_user_id', $partnerId)))
            ->when($fromDate, fn ($query) => $query->whereDate('paid_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('paid_date', '<=', $toDate))
            ->get()
            ->map(fn (SalaryPaymentPortion $portion) => [
                'type' => 'salary',
                'partner_id' => (int) ($portion->salary?->account_user_id ?? 0),
                'partner_name' => $portion->salary?->accountUser?->name ?: 'Partner',
                'date' => $portion->paid_date?->toDateString(),
                'amount' => (float) $portion->amount,
                'label' => ucfirst((string) $portion->salary?->status) . ' Payment',
                'notes' => $portion->salary?->notes,
                'id' => $portion->id,
                'deduct_source' => self::extractDeductSource($portion->salary?->notes),
            ]);

        return $expenseEntries
            ->concat($salaryEntries)
            ->sortByDesc(fn (array $entry) => ($entry['date'] ?? '') . '-' . str_pad((string) $entry['id'], 10, '0', STR_PAD_LEFT))
            ->values();
    }

    private static function sourceFilteredDeductionSum(string $targetSource, ?int $partnerId = null, ?string $fromDate = null, ?string $toDate = null): float
    {
        $expenses = Expense::query()
            ->when($partnerId, fn ($query) => $query->where('account_user_id', $partnerId))
            ->when($fromDate, fn ($query) => $query->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('expense_date', '<=', $toDate))
            ->get(['id', 'amount', 'notes', 'category', 'account_user_id']);

        $sum = 0.0;
        foreach ($expenses as $expense) {
            $detected = self::extractDeductSource($expense->notes);
            if ($targetSource === 'profit') {
                if ($detected === 'profit' || $detected === null) {
                    $sum += (float) $expense->amount;
                }
            } else {
                if ($detected === 'invested') {
                    $sum += (float) $expense->amount;
                }
            }
        }

        return round($sum, 2);
    }

    private static function extractDeductSource(mixed $notesRaw): ?string
    {
        if (is_string($notesRaw) && $notesRaw !== '') {
            $decoded = json_decode($notesRaw, true);
            if (is_array($decoded) && isset($decoded['deduct_source']) && in_array($decoded['deduct_source'], ['profit', 'invested'], true)) {
                return $decoded['deduct_source'];
            }
        }
        if (is_array($notesRaw) && isset($notesRaw['deduct_source']) && in_array($notesRaw['deduct_source'], ['profit', 'invested'], true)) {
            return $notesRaw['deduct_source'];
        }

        return null;
    }
}
