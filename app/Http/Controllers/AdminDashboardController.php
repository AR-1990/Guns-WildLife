<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Support\SaleVisibilitySummary;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $salesBaseQuery = function () use ($isSalesman, $user) {
            $q = Sale::query()
                ->with(['items.product'])
                ->whereHas('items.product')
                ->latest('sale_date')
                ->latest('id');
            if ($isSalesman) {
                $q->where('created_by', $user->id);
            }
            return $q;
        };

        $products = Product::query()
            ->with(['category'])
            ->withCount('availableUnits')
            ->orderBy('name')
            ->get();

        $lowStockProducts = $this->lowStockProducts($products);
        $outOfStockProducts = $lowStockProducts
            ->where('current_stock', '<=', 0)
            ->values();

        if ($isSalesman) {
            $sales = SaleVisibilitySummary::applyToSales($salesBaseQuery()->get());
            $completedSales = $sales->where('status', 'completed')->values();
            $draftSales = $sales->where('status', 'draft')->values();

            $monthlySales = $completedSales->filter(function (Sale $sale) use ($monthStart, $monthEnd) {
                $saleDate = $sale->sale_date?->toDateString();
                return $saleDate && $saleDate >= $monthStart && $saleDate <= $monthEnd;
            })->values();

            $todaySales = $completedSales->filter(fn (Sale $sale) => $sale->sale_date?->toDateString() === $today)->values();

            $recentSales = $sales->take(8)->values();

            return view('admin.index', [
                'isSalesmanView' => true,
                'summary' => [
                    'completed_sales' => $completedSales->count(),
                    'draft_sales' => $draftSales->count(),
                    'low_stock_count' => $lowStockProducts->count(),
                    'out_of_stock_count' => $outOfStockProducts->count(),
                    'today_sales_count' => $todaySales->count(),
                    'today_sales_total' => (float) $todaySales->sum('grand_total'),
                    'monthly_revenue' => (float) $monthlySales->sum('grand_total'),
                    'total_revenue' => (float) $completedSales->sum('grand_total'),
                ],
                'quickStats' => [
                    [
                        'label' => 'Today Sales',
                        'value' => number_format((float) $todaySales->sum('grand_total'), 2),
                        'link' => route('admin.sales.index'),
                        'link_label' => 'Open Sales',
                    ],
                    [
                        'label' => 'Low Stock',
                        'value' => $lowStockProducts->count(),
                        'link' => route('admin.dashboard'),
                        'link_label' => 'Stock Alert',
                    ],
                    [
                        'label' => 'Total Completed',
                        'value' => $completedSales->count(),
                        'link' => route('admin.sales.index'),
                        'link_label' => 'View All',
                    ],
                    [
                        'label' => 'Out of Stock',
                        'value' => $outOfStockProducts->count(),
                        'link' => route('admin.dashboard'),
                        'link_label' => 'Review Stock',
                    ],
                ],
                'recentSales' => $recentSales,
                'topProducts' => $this->topProducts($monthlySales, false),
                'lowStockProducts' => $lowStockProducts->take(10),
            ]);
        }

        $sales = SaleVisibilitySummary::applyToSales($salesBaseQuery()->get());

        $completedSales = $sales->where('status', 'completed')->values();
        $draftSales = $sales->where('status', 'draft')->values();

        $monthlySales = $completedSales->filter(function (Sale $sale) use ($monthStart, $monthEnd) {
            $saleDate = $sale->sale_date?->toDateString();

            return $saleDate && $saleDate >= $monthStart && $saleDate <= $monthEnd;
        })->values();

        $todaySales = $completedSales->filter(fn (Sale $sale) => $sale->sale_date?->toDateString() === $today)->values();

        $monthlyExpenses = Expense::query()
            ->whereDate('expense_date', '>=', $monthStart)
            ->whereDate('expense_date', '<=', $monthEnd)
            ->get();

        $recentSales = $sales->take(8)->values();
        $topProducts = $this->topProducts($monthlySales, true);
        $contactsCount = ContactRequest::query()->count();

        $monthlyAdminProfit = $monthlySales->sum(fn (Sale $sale) => $sale->items->sum(fn ($item) => $this->adminProfitForItem($item)));

        return view('admin.index', [
            'isSalesmanView' => false,
            'summary' => [
                'products' => $products->count(),
                'serialized_available' => $products->where('is_serialized', true)->sum('available_units_count'),
                'low_stock_count' => $lowStockProducts->count(),
                'completed_sales' => $completedSales->count(),
                'draft_sales' => $draftSales->count(),
                'salesmen' => User::query()->where('role', User::ROLE_SALESMAN)->count(),
                'monthly_revenue' => (float) $monthlySales->sum('grand_total'),
                'monthly_expense' => (float) $monthlyExpenses->sum('amount'),
                'monthly_admin_profit' => (float) $monthlyAdminProfit,
                'contacts' => $contactsCount,
                'today_sales_count' => $todaySales->count(),
                'today_sales_total' => (float) $todaySales->sum('grand_total'),
            ],
            'quickStats' => [
                [
                    'label' => 'Products',
                    'value' => $products->count(),
                    'link' => route('admin.products.index'),
                    'link_label' => 'Open Products',
                ],
                [
                    'label' => 'Low Stock',
                    'value' => $lowStockProducts->count(),
                    'link' => route('admin.products.index'),
                    'link_label' => 'Manage Stock',
                ],
                [
                    'label' => 'Today Sales',
                    'value' => number_format((float) $todaySales->sum('grand_total'), 2),
                    'link' => route('admin.sales.index'),
                    'link_label' => 'Open Sales',
                ],
                [
                    'label' => 'Contacts',
                    'value' => $contactsCount,
                    'link' => route('admin.contacts.index'),
                    'link_label' => 'Open Contacts',
                ],
            ],
            'lowStockProducts' => $lowStockProducts->take(10),
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
        ]);
    }

    private function lowStockProducts(Collection $products): Collection
    {
        return $products
            ->filter(function (Product $product) {
                if ($product->status !== 'active') {
                    return false;
                }

                $threshold = max(1, (int) ($product->min_stock_level ?: 5));
                $currentStock = $product->is_serialized
                    ? (int) $product->available_units_count
                    : (int) $product->stock_quantity;

                return $currentStock <= $threshold;
            })
            ->map(function (Product $product) {
                $product->current_stock = $product->is_serialized
                    ? (int) $product->available_units_count
                    : (int) $product->stock_quantity;
                $product->stock_threshold = max(1, (int) ($product->min_stock_level ?: 5));

                return $product;
            })
            ->sortBy('current_stock')
            ->values();
    }

    private function topProducts(Collection $monthlySales, bool $includeProfit = true): Collection
    {
        return $monthlySales
            ->flatMap(fn (Sale $sale) => $sale->items)
            ->groupBy('product_name')
            ->map(function (Collection $items, string $productName) use ($includeProfit) {
                $data = [
                    'product_name' => $productName,
                    'qty' => $items->sum('quantity'),
                    'revenue' => (float) $items->sum('total'),
                ];
                if ($includeProfit) {
                    $data['admin_profit'] = (float) $items->sum(fn ($item) => $this->adminProfitForItem($item));
                }
                return $data;
            })
            ->sortByDesc('qty')
            ->take(8)
            ->values();
    }

    private function adminProfitForItem(SaleItem $item): float
    {
        return (((float) $item->price) - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity;
    }
}
