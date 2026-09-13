<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PartnerWithdrawal;
use App\Models\SalaryPaymentPortion;
use App\Models\Sale;
use App\Support\SaleVisibilitySummary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCashbookController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $allTransactions = $this->buildTransactions($filters);
        $transactions = $this->paginateCollection($allTransactions, 10, $request, 'cashbook_page');

        return view('admin.accounts.cashbook', [
            'filters' => [
                'from_date' => $filters['from_date'] ?? '',
                'to_date' => $filters['to_date'] ?? '',
            ],
            'openingBalance' => $this->calculateOpeningBalance($filters),
            'transactions' => $transactions,
            'summary' => [
                'receipts' => $allTransactions->sum('debit'),
                'payments' => $allTransactions->sum('credit'),
                'closing' => $allTransactions->first()['balance'] ?? $this->calculateOpeningBalance($filters),
            ],
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $openingBalance = $this->calculateOpeningBalance($filters);
        $transactions = $this->buildTransactions($filters);

        return response()->streamDownload(function () use ($openingBalance, $transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Opening Balance', number_format($openingBalance, 2, '.', '')]);
            fputcsv($handle, [
                'Date',
                'Voucher',
                'Description',
                'Method',
                'Debit (In)',
                'Credit (Out)',
                'Type',
                'Balance',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction['date'],
                    $transaction['voucher'],
                    $transaction['description'],
                    $transaction['method'],
                    $transaction['debit'],
                    $transaction['credit'],
                    $transaction['type'],
                    $transaction['balance'],
                ]);
            }

            fclose($handle);
        }, 'cashbook-report.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    private function calculateOpeningBalance(array $filters): float
    {
        if (blank($filters['from_date'] ?? null)) {
            return 0;
        }

        $fromDate = $filters['from_date'];

        $salesBefore = SaleVisibilitySummary::applyToSales(
            Sale::query()
            ->where('status', 'completed')
            ->whereHas('items.product')
            ->with('items.product')
            ->whereDate('sale_date', '<', $fromDate)
            ->get()
        )->sum('grand_total');

        $expensesBefore = Expense::query()
            ->whereDate('expense_date', '<', $fromDate)
            ->sum('amount');

        $salaryPaymentsBefore = SalaryPaymentPortion::query()
            ->whereHas('salary')
            ->whereDate('paid_date', '<', $fromDate)
            ->sum('amount');

        $partnerWithdrawalsBefore = PartnerWithdrawal::query()
            ->whereHas('partner', fn ($query) => $query->where('role', \App\Models\User::ROLE_PARTNER))
            ->whereDate('withdrawal_date', '<', $fromDate)
            ->sum('amount');

        return (float) $salesBefore
            - (float) $expensesBefore
            - (float) $salaryPaymentsBefore
            - (float) $partnerWithdrawalsBefore;
    }

    private function buildTransactions(array $filters): Collection
    {
        $sales = Sale::query()
            ->where('status', 'completed')
            ->whereHas('items.product')
            ->with('items.product')
            ->when(filled($filters['from_date'] ?? null), fn ($query) => $query->whereDate('sale_date', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($query) => $query->whereDate('sale_date', '<=', $filters['to_date']))
            ->get();
        $sales = SaleVisibilitySummary::applyToSales($sales)
            ->map(function (Sale $sale) {
                return [
                    'date' => optional($sale->sale_date)->format('Y-m-d'),
                    'display_date' => optional($sale->sale_date)->format('d-M-Y'),
                    'voucher' => $sale->receipt_no,
                    'description' => 'Sale Receipt - ' . $sale->customer_name,
                    'method' => str($sale->document_type)->replace('_', ' ')->title()->toString(),
                    'debit' => (float) $sale->grand_total,
                    'credit' => 0.0,
                    'type' => 'Sale',
                ];
            });

        $expenses = Expense::query()
            ->with('accountUser')
            ->when(filled($filters['from_date'] ?? null), fn ($query) => $query->whereDate('expense_date', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($query) => $query->whereDate('expense_date', '<=', $filters['to_date']))
            ->get()
            ->map(function (Expense $expense) {
                $accountLabel = $expense->accountUser?->name
                    ? ' - ' . $expense->accountUser->name . ' (' . $expense->accountUser->roleLabel() . ')'
                    : '';

                return [
                    'date' => optional($expense->expense_date)->format('Y-m-d'),
                    'display_date' => optional($expense->expense_date)->format('d-M-Y'),
                    'voucher' => 'EXP-' . $expense->id,
                    'description' => $expense->title . ($expense->category ? ' (' . $expense->category . ')' : '') . $accountLabel,
                    'method' => str($expense->payment_method)->title()->toString(),
                    'debit' => 0.0,
                    'credit' => (float) $expense->amount,
                    'type' => 'Expense',
                ];
            });

        $salaryPayments = SalaryPaymentPortion::query()
            ->with('salary.employee', 'salary.accountUser')
            ->whereHas('salary')
            ->when(filled($filters['from_date'] ?? null), fn ($query) => $query->whereDate('paid_date', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($query) => $query->whereDate('paid_date', '<=', $filters['to_date']))
            ->get()
            ->map(function (SalaryPaymentPortion $portion) {
                $salary = $portion->salary;
                $accountLabel = $salary?->accountUser?->name
                    ? ' - ' . $salary->accountUser->name . ' (' . $salary->accountUser->roleLabel() . ')'
                    : '';

                return [
                    'date' => optional($portion->paid_date)->format('Y-m-d'),
                    'display_date' => optional($portion->paid_date)->format('d-M-Y'),
                    'voucher' => 'SAL-' . $salary?->id . '-' . $portion->portion_no,
                    'description' => ucfirst($salary?->status ?: 'salary') . ' - ' . ($salary?->employee?->name ?: 'Employee') . $accountLabel,
                    'method' => 'Entry #' . $portion->portion_no,
                    'debit' => 0.0,
                    'credit' => (float) $portion->amount,
                    'type' => 'Salary',
                ];
            });

        $partnerWithdrawals = PartnerWithdrawal::query()
            ->with('partner')
            ->whereHas('partner', fn ($query) => $query->where('role', \App\Models\User::ROLE_PARTNER))
            ->when(filled($filters['from_date'] ?? null), fn ($query) => $query->whereDate('withdrawal_date', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($query) => $query->whereDate('withdrawal_date', '<=', $filters['to_date']))
            ->get()
            ->map(function (PartnerWithdrawal $withdrawal) {
                $actionLabel = match ($withdrawal->entry_type) {
                    'close_account' => 'Account Close',
                    default => 'Partner Withdraw',
                };
                $breakdown = ' (Actual: ' . number_format((float) $withdrawal->actual_component, 2) . ', Profit: ' . number_format((float) $withdrawal->profit_component, 2) . ')';

                return [
                    'date' => optional($withdrawal->withdrawal_date)->format('Y-m-d'),
                    'display_date' => optional($withdrawal->withdrawal_date)->format('d-M-Y'),
                    'voucher' => 'PWD-' . $withdrawal->id,
                    'description' => $actionLabel . ' - ' . ($withdrawal->partner?->name ?: 'Partner') . $breakdown,
                    'method' => match ($withdrawal->entry_type) {
                        'close_account' => 'Full settlement',
                        default => 'Partner account',
                    },
                    'debit' => 0.0,
                    'credit' => (float) $withdrawal->amount,
                    'type' => 'Partner Withdraw',
                ];
            });

        $transactions = $sales
            ->concat($expenses)
            ->concat($salaryPayments)
            ->concat($partnerWithdrawals)
            ->sortBy([
                ['date', 'asc'],
                ['voucher', 'asc'],
            ])
            ->values();

        $balance = $this->calculateOpeningBalance($filters);

        return $transactions->map(function (array $transaction) use (&$balance) {
            $balance += (float) $transaction['debit'];
            $balance -= (float) $transaction['credit'];
            $transaction['balance'] = round($balance, 2);

            return $transaction;
        })->reverse()->values();
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage($pageName);
        $pageItems = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ]
        );
    }
}
