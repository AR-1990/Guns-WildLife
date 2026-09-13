<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Salary;
use App\Models\SalaryPaymentPortion;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Support\SaleVisibilitySummary;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAccountController extends Controller
{
    public function loanAdvance(Request $request): View
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'from_month' => ['nullable', 'date_format:Y-m'],
            'to_month' => ['nullable', 'date_format:Y-m', 'after_or_equal:from_month'],
        ]);

        $records = Salary::query()
            ->with('employee')
            ->where(function ($query) {
                $query->where('advance_salary', '>', 0)
                    ->orWhere('advance_adjustment_amount', '>', 0)
                    ->orWhere('advance_waived_amount', '>', 0)
                    ->orWhere('loan_amount', '>', 0)
                    ->orWhere('loan_adjustment_amount', '>', 0)
                    ->orWhere('loan_waived_amount', '>', 0)
                    ->orWhereIn('status', ['advance', 'loan']);
            })
            ->when(filled($filters['employee_id'] ?? null), fn ($query) => $query->where('employee_id', $filters['employee_id']))
            ->when(filled($filters['from_month'] ?? null), fn ($query) => $query->where('salary_month', '>=', $filters['from_month']))
            ->when(filled($filters['to_month'] ?? null), fn ($query) => $query->where('salary_month', '<=', $filters['to_month']))
            ->latest('salary_month')
            ->latest('id');
        $summaryRecords = (clone $records)->get();
        $records = $records->paginate(10)->withQueryString();

        return view('admin.accounts.loan-advance', [
            'employees' => User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SALESMAN])
                ->orderBy('name')
                ->get(),
            'records' => $records,
            'filters' => [
                'employee_id' => $filters['employee_id'] ?? '',
                'from_month' => $filters['from_month'] ?? '',
                'to_month' => $filters['to_month'] ?? '',
            ],
            'summary' => [
                'records' => $summaryRecords->count(),
                'advance' => (float) $summaryRecords->sum('advance_salary'),
                'advance_adjusted' => (float) $summaryRecords->sum('advance_adjustment_amount'),
                'advance_waived' => (float) $summaryRecords->sum('advance_waived_amount'),
                'loan' => (float) $summaryRecords->sum('loan_amount'),
                'loan_adjusted' => (float) $summaryRecords->sum('loan_adjustment_amount'),
                'loan_waived' => (float) $summaryRecords->sum('loan_waived_amount'),
                'outstanding' => max(
                    0,
                    (float) $summaryRecords->sum('loan_amount') - (float) $summaryRecords->sum('loan_adjustment_amount')
                        - (float) $summaryRecords->sum('loan_waived_amount')
                ),
            ],
        ]);
    }

    public function revenueReport(Request $request): View
    {
        $filters = $this->validatedDateFilters($request);
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $salesQuery = $this->completedSalesQuery($filters)->with('items');
        $summarySales = SaleVisibilitySummary::applyToSales((clone $salesQuery)->with('items.product')->get());
        $sales = $salesQuery->with('items.product')->paginate(10)->withQueryString();
        $sales->setCollection(SaleVisibilitySummary::applyToSales($sales->getCollection()));

        $calculateSaleMetrics = function (Collection $saleList) {
            $totalCogs = 0;
            $totalGrossProfit = 0;
            $positiveProfit = 0;
            $negativeLoss = 0;
            $totalDiscount = 0;
            $totalShipping = 0;
            $totalTax = 0;

            foreach ($saleList as $sale) {
                $totalDiscount += (float) $sale->discount_total;
                $totalShipping += (float) $sale->shipping_total;
                $totalTax += (float) $sale->tax_total;

                foreach ($sale->items as $item) {
                    $cogsLine = (float) ($item->historical_cost ?? 0) * (int) $item->quantity;
                    $grossLine = ((float) $item->price - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;

                    $totalCogs += $cogsLine;
                    $totalGrossProfit += $grossLine;
                    if ($grossLine >= 0) {
                        $positiveProfit += $grossLine;
                    } else {
                        $negativeLoss += abs($grossLine);
                    }
                }
            }

            return [
                'cogs' => round($totalCogs, 2),
                'gross_profit' => round($totalGrossProfit, 2),
                'admin_profit' => round($totalGrossProfit, 2),
                'positive_profit' => round($positiveProfit, 2),
                'loss_total' => round($negativeLoss, 2),
                'discount' => round($totalDiscount, 2),
                'shipping' => round($totalShipping, 2),
                'tax' => round($totalTax, 2),
            ];
        };

        $aggregates = $calculateSaleMetrics($summarySales);

        $expenses = Expense::query()
            ->when($fromDate, fn ($query) => $query->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('expense_date', '<=', $toDate))
            ->get();

        $revenue = (float) $summarySales->sum('grand_total');
        $expenseTotal = (float) $expenses->sum('amount');
        $netProfit = round($aggregates['positive_profit'] - $aggregates['loss_total'] - $expenseTotal, 2);

        $saleRowComputed = [];
        foreach ($summarySales as $s) {
            $sCogs = 0;
            $sGross = 0;
            foreach ($s->items as $item) {
                $c = (float) ($item->historical_cost ?? 0) * (int) $item->quantity;
                $g = ((float) $item->price - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;
                $sCogs += $c;
                $sGross += $g;
            }
            $saleRowComputed[$s->id] = [
                'cogs' => round($sCogs, 2),
                'gross_profit' => round($sGross, 2),
                'admin_profit' => round($sGross, 2),
                'margin' => (float) $s->subtotal > 0
                    ? round(($sGross / (float) $s->subtotal) * 100, 2)
                    : 0,
            ];
        }

        return view('admin.reports.revenue', [
            'sales' => $sales,
            'saleRowComputed' => $saleRowComputed,
            'filters' => [
                'from_date' => $fromDate ?? '',
                'to_date' => $toDate ?? '',
            ],
            'summary' => [
                'sales' => $summarySales->count(),
                'qty' => $summarySales->sum(fn ($sale) => $sale->items->sum('quantity')),
                'subtotal' => round((float) $summarySales->sum('subtotal'), 2),
                'discount' => $aggregates['discount'],
                'shipping' => $aggregates['shipping'],
                'tax' => $aggregates['tax'],
                'revenue' => $revenue,
                'cogs' => $aggregates['cogs'],
                'gross_profit' => $aggregates['gross_profit'],
                'gross_margin' => $revenue > 0 ? round(($aggregates['gross_profit'] / $revenue) * 100, 2) : 0,
                'profit' => $aggregates['positive_profit'],
                'loss' => $aggregates['loss_total'],
                'admin_profit' => $aggregates['admin_profit'],
                'expenses' => $expenseTotal,
                'expense_records' => $expenses->count(),
                'net_profit' => $netProfit,
            ],
        ]);
    }

    public function exportRevenueCsv(Request $request): StreamedResponse
    {
        $filters = $this->validatedDateFilters($request);
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $sales = SaleVisibilitySummary::applyToSales(
            $this->completedSalesQuery($filters)->with('items.product')->get()
        );

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Receipt No', 'Date', 'Customer', 'Phone', 'Items Count', 'Qty',
                'Subtotal', 'Discount', 'Shipping', 'Tax', 'Revenue',
                'COGS (Historical)', 'Gross Profit',
                'Net Profit', 'Gross Margin %', 'Status', 'Creator',
            ]);

            foreach ($sales as $sale) {
                $sCogs = 0;
                $sGross = 0;
                foreach ($sale->items as $item) {
                    $c = (float) ($item->historical_cost ?? 0) * (int) $item->quantity;
                    $g = ((float) $item->price - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;
                    $sCogs += $c;
                    $sGross += $g;
                }
                $margin = (float) $sale->subtotal > 0 ? round(($sGross / (float) $sale->subtotal) * 100, 2) : 0;

                fputcsv($handle, [
                    $sale->receipt_no,
                    $sale->sale_date?->format('Y-m-d'),
                    $sale->customer_name,
                    $sale->customer_phone,
                    $sale->items->count(),
                    $sale->items->sum('quantity'),
                    round((float) $sale->subtotal, 2),
                    round((float) $sale->discount_total, 2),
                    round((float) $sale->shipping_total, 2),
                    round((float) $sale->tax_total, 2),
                    round((float) $sale->grand_total, 2),
                    round($sCogs, 2),
                    round($sGross, 2),
                    round($sGross, 2),
                    $margin,
                    ucfirst($sale->status ?? ''),
                    $sale->creator?->name ?? '',
                ]);
            }

            fclose($handle);
        }, 'revenue-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function lossReport(Request $request): View
    {
        $filters = $this->validatedDateFilters($request);
        $summaryLossItems = $this->lossItemsCollection($filters);
        $lossItems = $this->paginateCollection($summaryLossItems, 10, $request, 'loss_page');

        return view('admin.reports.loss', [
            'lossItems' => $lossItems,
            'filters' => [
                'from_date' => $filters['from_date'] ?? '',
                'to_date' => $filters['to_date'] ?? '',
            ],
            'summary' => [
                'sales' => $summaryLossItems->pluck('receipt_no')->unique()->count(),
                'items' => $summaryLossItems->count(),
                'loss_total' => abs($summaryLossItems->sum('admin_profit')),
                'avg_loss' => $summaryLossItems->count() ? abs($summaryLossItems->avg('admin_profit')) : 0,
            ],
        ]);
    }

    public function exportLossCsv(Request $request): StreamedResponse
    {
        $filters = $this->validatedDateFilters($request);
        $lossItems = $this->lossItemsCollection($filters);

        return response()->streamDownload(function () use ($lossItems) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Receipt No', 'Date', 'Customer', 'Product', 'Qty', 'Sale Total', 'Loss']);

            foreach ($lossItems as $item) {
                fputcsv($handle, [
                    $item['receipt_no'],
                    $item['sale_date'],
                    $item['customer_name'],
                    $item['product_name'],
                    $item['quantity'],
                    $item['sale_total'],
                    abs($item['admin_profit']),
                ]);
            }

            fclose($handle);
        }, 'loss-report.csv', ['Content-Type' => 'text/csv']);
    }

    private function financialSnapshot(?string $fromDate = null, ?string $toDate = null): array
    {
        $sales = $this->completedSalesQuery([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ])->with('items.product')->get();
        $sales = SaleVisibilitySummary::applyToSales($sales);

        $expenses = Expense::query()
            ->when($fromDate, fn ($query) => $query->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('expense_date', '<=', $toDate))
            ->get();

        $salaryPayments = SalaryPaymentPortion::query()
            ->whereHas('salary')
            ->when($fromDate, fn ($query) => $query->whereDate('paid_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('paid_date', '<=', $toDate))
            ->get();

        $loanAdvance = Salary::query()
            ->when($fromDate, fn ($query) => $query->where('salary_month', '>=', substr($fromDate, 0, 7)))
            ->when($toDate, fn ($query) => $query->where('salary_month', '<=', substr($toDate, 0, 7)))
            ->get();

        $adminProfit = $sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return (((float) $item->price) - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;
            });
        });

        $revenue = (float) $sales->sum('grand_total');
        $expenseTotal = (float) $expenses->sum('amount');
        $salaryPaid = (float) $salaryPayments->sum('amount');
        $cashBalance = $revenue - $expenseTotal - $salaryPaid;
        $managedUsers = User::query()
            ->where('role', User::ROLE_SALESMAN)
            ->count();
        $lossTotal = abs($sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                $grossProfit = (((float) $item->price) - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;

                return $grossProfit < 0 ? $grossProfit : 0;
            });
        }));

        $totalAdvanceIssued = (float) $loanAdvance->sum('advance_salary');
        $totalAdvanceAdjusted = (float) $loanAdvance->sum('advance_adjustment_amount');
        $totalAdvanceWaived = (float) $loanAdvance->sum('advance_waived_amount');
        $totalLoanIssued = (float) $loanAdvance->sum('loan_amount');
        $totalLoanAdjusted = (float) $loanAdvance->sum('loan_adjustment_amount');
        $totalLoanWaived = (float) $loanAdvance->sum('loan_waived_amount');
        $outstandingAdvance = max(0, $totalAdvanceIssued - $totalAdvanceAdjusted - $totalAdvanceWaived);
        $outstandingLoan = max(0, $totalLoanIssued - $totalLoanAdjusted - $totalLoanWaived);

        return [
            'cashbook_balance' => $cashBalance,
            'expenses' => $expenseTotal,
            'salary_paid' => $salaryPaid,
            'loan_advance' => $outstandingAdvance + $outstandingLoan,
            'admin_profit' => (float) $adminProfit,
            'loss_total' => (float) $lossTotal,
            'revenue' => $revenue,
            'users' => $managedUsers,
            'records' => $sales->count(),
            'expense_records' => $expenses->count(),
        ];
    }

    private function validatedDateFilters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    private function completedSalesQuery(array $filters)
    {
        return Sale::query()
            ->where('status', 'completed')
            ->whereHas('items.product')
            ->when(filled($filters['from_date'] ?? null), fn ($query) => $query->whereDate('sale_date', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($query) => $query->whereDate('sale_date', '<=', $filters['to_date']))
            ->latest('sale_date')
            ->latest('id');
    }

    private function lossItemsCollection(array $filters)
    {
        return SaleItem::query()
            ->with(['sale', 'product'])
            ->whereHas('product')
            ->whereHas('sale', function ($query) use ($filters) {
                $query->where('status', 'completed')
                    ->when(filled($filters['from_date'] ?? null), fn ($saleQuery) => $saleQuery->whereDate('sale_date', '>=', $filters['from_date']))
                    ->when(filled($filters['to_date'] ?? null), fn ($saleQuery) => $saleQuery->whereDate('sale_date', '<=', $filters['to_date']));
            })
            ->get()
            ->map(function (SaleItem $item) {
                $grossProfit = (((float) $item->price) - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;

                return [
                    'receipt_no' => $item->sale?->receipt_no,
                    'sale_date' => $item->sale?->sale_date?->format('Y-m-d'),
                    'customer_name' => $item->sale?->customer_name,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'sale_total' => (float) $item->total,
                    'admin_profit' => $grossProfit,
                    'sale_id' => $item->sale_id,
                ];
            })
            ->filter(fn (array $item) => $item['admin_profit'] < 0)
            ->values();
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
