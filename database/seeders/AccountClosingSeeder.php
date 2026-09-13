<?php

namespace Database\Seeders;

use App\Models\AccountClosing;
use App\Models\Expense;
use App\Models\PartnerWithdrawal;
use App\Models\ProductPartnerInvestment;
use App\Models\SalaryPaymentPortion;
use App\Models\Sale;
use App\Models\User;
use App\Support\PartnerAccountSummary;
use Illuminate\Database\Seeder;

class AccountClosingSeeder extends Seeder
{
    public function run(): void
    {
        $closingDate = '2026-08-31';
        $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();

        AccountClosing::query()
            ->whereDate('closing_date', $closingDate)
            ->delete();

        $sales = Sale::query()
            ->with('items')
            ->where('status', 'completed')
            ->whereDate('sale_date', '<=', $closingDate)
            ->get();

        $expenses = Expense::query()
            ->whereDate('expense_date', '<=', $closingDate)
            ->get();

        $salaryPortions = SalaryPaymentPortion::query()
            ->whereDate('paid_date', '<=', $closingDate)
            ->get();

        $withdrawals = PartnerWithdrawal::query()
            ->whereDate('withdrawal_date', '<=', $closingDate)
            ->get();

        $subtotal = round((float) $sales->sum('subtotal'), 2);
        $discount = round((float) $sales->sum(fn (Sale $sale) => ((float) $sale->subtotal * (float) $sale->discount_percent) / 100), 2);
        $revenue = round((float) $sales->sum('grand_total'), 2);
        $cogs = round((float) $sales->sum(fn (Sale $sale) => $sale->items->sum(fn ($item) => (float) $item->unit_purchase_price * (int) $item->quantity)), 2);
        $grossProfit = round($revenue - $cogs, 2);
        $expensesTotal = round((float) $expenses->sum('amount'), 2);
        $salaryTotal = round((float) $salaryPortions->sum('amount'), 2);
        $withdrawalTotal = round((float) $withdrawals->sum('amount'), 2);
        $operatingCost = round($expensesTotal + $salaryTotal, 2);
        $netOperatingIncome = round($grossProfit - $expensesTotal - $salaryTotal, 2);
        $cashbookBalance = round($revenue - $expensesTotal - $salaryTotal - $withdrawalTotal, 2);

        $closing = AccountClosing::query()->create([
            'period_name' => 'August 2026 Closing',
            'closing_date' => $closingDate,
            'notes' => 'Seeded month-end closing snapshot for accounts dashboard and reports.',
            'total_sales_count' => $sales->count(),
            'total_revenue' => $revenue,
            'total_subtotal' => $subtotal,
            'total_discount' => $discount,
            'total_shipping' => 0,
            'total_tax' => 0,
            'total_cogs' => $cogs,
            'total_gross_profit' => $grossProfit,
            'gross_margin_percent' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 4) : 0,
            'expense_records' => $expenses->count(),
            'total_expenses' => $expensesTotal,
            'salary_records' => $salaryPortions->count(),
            'total_salaries' => $salaryTotal,
            'total_operating_cost' => $operatingCost,
            'withdrawal_records' => $withdrawals->count(),
            'total_withdrawals' => $withdrawalTotal,
            'total_withdrawn_actual' => $withdrawalTotal,
            'total_withdrawn_profit' => 0,
            'total_partner_profit' => 0,
            'total_partner_deductions' => 0,
            'total_partner_expense_deductions' => 0,
            'total_partner_salary_deductions' => 0,
            'total_admin_profit' => $grossProfit,
            'total_net_operating_income' => $netOperatingIncome,
            'cashbook_balance' => $cashbookBalance,
            'total_released_actual' => 0,
            'total_partner_invested_actual' => round((float) ProductPartnerInvestment::query()->sum('amount'), 2),
            'total_net_payable' => 0,
            'total_partner_unified_net_payable' => $withdrawalTotal,
            'created_by' => $admin->id,
        ]);

        $partners = User::query()
            ->where('role', User::ROLE_PARTNER)
            ->orderBy('name')
            ->get();

        foreach ($partners as $partner) {
            $withdrawn = round((float) $withdrawals->where('partner_id', $partner->id)->sum('amount'), 2);
            $expenseDeductions = round(PartnerAccountSummary::totalExpenseDeductions($partner->id, null, $closingDate), 2);
            $salaryDeductions = round(PartnerAccountSummary::totalSalaryDeductions($partner->id, null, $closingDate), 2);
            $totalDeductions = round($expenseDeductions + $salaryDeductions, 2);

            $closing->partnerBalances()->create([
                'partner_id' => $partner->id,
                'sales_count' => 0,
                'total_invested_actual' => round((float) ProductPartnerInvestment::query()->where('partner_id', $partner->id)->sum('amount'), 2),
                'released_actual_amount' => 0,
                'earned_profit_amount' => 0,
                'expense_deduction_amount' => $expenseDeductions,
                'salary_deduction_amount' => $salaryDeductions,
                'total_deduction_amount' => $totalDeductions,
                'withdrawn_actual_amount' => $withdrawn,
                'withdrawn_profit_amount' => 0,
                'total_withdrawn_amount' => $withdrawn,
                'net_payable_amount' => 0,
                'unified_net_payable_amount' => $withdrawn,
                'notes' => 'Seeded closing snapshot row.',
            ]);
        }
    }
}
