<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\DiaryContact;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Support\ProductCostingService;
use App\Support\SaleVisibilitySummary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();
        $costingService = app(ProductCostingService::class);

        $products = Product::query()
            ->with([
                'category',
                'partnerInvestments.partner',
                'availableUnits.restockEntry',
                'restockEntries' => fn ($query) => $query->where('remaining_quantity', '>', 0)->orderBy('restocked_at')->orderBy('id'),
            ])
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where(function ($nonSerialized) {
                    $nonSerialized->where('is_serialized', false)
                        ->where('stock_quantity', '>', 0);
                })->orWhere(function ($serialized) {
                    $serialized->where('is_serialized', true)
                        ->whereHas('availableUnits');
                });
            })
            ->orderBy('name')
            ->get();

        $products->each(fn (Product $product) => $costingService->ensureLegacyTracked($product));
        $products->load([
            'availableUnits.restockEntry',
            'restockEntries' => fn ($query) => $query->where('remaining_quantity', '>', 0)->orderBy('restocked_at')->orderBy('id'),
        ]);

        $saleBaseQuery = function () use ($request, $isSalesman, $user) {
            $q = Sale::query()
                ->with(['items.product'])
                ->whereHas('items.product')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->latest();
            if ($isSalesman) {
                $q->where('created_by', $user->id);
            }
            return $q;
        };

        $sales = SaleVisibilitySummary::applyToSales($saleBaseQuery()->get());

        $draftBaseQuery = function () use ($isSalesman, $user) {
            $q = Sale::query()
                ->with('items.product')
                ->whereHas('items.product')
                ->where('status', 'draft')
                ->latest();
            if ($isSalesman) {
                $q->where('created_by', $user->id);
            }
            return $q;
        };
        $draftSales = SaleVisibilitySummary::applyToSales($draftBaseQuery()->get());

        $selectedDraft = $request->filled('draft')
            ? (function () use ($request, $draftBaseQuery) {
                $sale = (clone $draftBaseQuery())->where('status', 'draft')->find($request->integer('draft'));
                return $sale ? SaleVisibilitySummary::applyToSale($sale) : null;
            })()
            : null;

        $productsMap = $products->mapWithKeys(fn ($product) => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->product_code,
                'unit' => $product->unit,
                'price' => (float) $product->selling_price,
                'cost' => $isSalesman ? 0.0 : (float) $product->unit_purchase_price,
                'ownership_type' => $product->ownership_type,
                'commission_type' => $product->commission_type,
                'commission_fixed' => (float) $product->commission_fixed,
                'serialized' => $product->is_serialized,
                'stock' => $product->is_serialized ? (int) $product->availableUnits->count() : (int) $product->stock_quantity,
                'cost_layers' => $product->restockEntries->map(fn ($entry) => [
                    'quantity' => (int) $entry->remaining_quantity,
                    'unit_purchase_price' => (float) $entry->unit_purchase_price,
                ])->values()->all(),
            ],
        ])->all();

        return view('admin.sales.index', [
            'products' => $products,
            'productsJson' => $productsMap,
            'productUnitsJson' => $products->mapWithKeys(fn ($product) => [
                $product->id => $product->availableUnits->map(fn ($unit) => [
                    'id' => $unit->id,
                    'weapon_number' => $unit->weapon_number,
                    'unit_purchase_price' => (float) $unit->unit_purchase_price,
                ])->values()->all(),
            ])->all(),
            'sales' => $sales,
            'draftSales' => $draftSales,
            'selectedDraft' => $selectedDraft,
            'selectedDraftItems' => $selectedDraft
                ? $selectedDraft->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'serial_number' => $item->serial_number,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ])->values()->all()
                : [],
            'summary' => [
                'subtotal' => $sales->sum(fn ($sale) => (float) $sale->subtotal),
                'drafts' => $sales->where('status', 'draft')->count(),
                'completed' => $sales->where('status', 'completed')->count(),
                'grand_total' => $sales->sum(fn ($sale) => (float) $sale->grand_total),
            ],
            'isSalesmanView' => $isSalesman,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $preparedItems, $subtotal, $discountPercent, $grandTotal] = $this->prepareSalePayload($request);
        $costingService = app(ProductCostingService::class);

        $sale = DB::transaction(function () use ($request, $data, $preparedItems, $subtotal, $discountPercent, $grandTotal, $costingService) {
            $sale = Sale::query()->create([
                'receipt_no' => $this->nextReceiptNumber(),
                'document_type' => $data['document_type'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'sale_date' => $data['sale_date'],
                'reference' => $data['reference'] ?? null,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'tax_percent' => 0,
                'grand_total' => $grandTotal,
                'status' => $data['action_type'],
                'created_by' => $request->user()->id,
            ]);

            foreach ($preparedItems as $item) {
                $unitPurchasePrice = (float) $item['unit_purchase_price'];
                $stockAllocations = [];

                if (! $item['product_unit'] && $data['action_type'] === 'completed') {
                    $actualCost = $costingService->allocateSimpleSaleCost($item['product'], $item['quantity']);
                    $unitPurchasePrice = (float) $actualCost['average_unit_cost'];
                    $stockAllocations = collect($actualCost['allocations'])->map(fn ($allocation) => [
                        'product_restock_entry_id' => $allocation['entry_id'],
                        'quantity' => $allocation['quantity'],
                        'unit_purchase_price' => $allocation['unit_purchase_price'],
                        'total_cost' => $allocation['total_cost'],
                    ])->values()->all();
                }

                $saleItem = $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'product_unit_id' => $item['product_unit']?->id,
                    'partner_id' => $item['primary_partner_id'],
                    'product_name' => $item['product']->name,
                    'serial_number' => $item['serial_number'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'unit_purchase_price' => $unitPurchasePrice,
                    'total' => $item['line_total'],
                    'partner_profit' => 0,
                ]);

                if ($item['product_unit'] && $data['action_type'] === 'completed') {
                    $costingService->markSerializedUnitSold($item['product_unit']);
                } elseif ($data['action_type'] === 'completed') {
                    $this->decrementProductStock($item['product'], $item['quantity']);
                    $saleItem->stockAllocations()->createMany($stockAllocations);
                }
            }

            $this->syncDiaryContactFromSale($sale, $request->user()->id);
            
            return $sale;
        });

        return redirect()
            ->route('admin.sales.index', $data['action_type'] === 'draft' ? ['draft' => $sale->id] : [])
            ->with('status', $data['action_type'] === 'draft' ? 'Sale saved as draft.' : 'Sale completed successfully.');
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'draft') {
            throw ValidationException::withMessages([
                'sale' => 'Only draft sales can be edited or updated.',
            ]);
        }

        [$data, $preparedItems, $subtotal, $discountPercent, $grandTotal] = $this->prepareSalePayload($request);
        $costingService = app(ProductCostingService::class);

        DB::transaction(function () use ($sale, $data, $preparedItems, $subtotal, $discountPercent, $grandTotal, $costingService) {
            $sale->update([
                'document_type' => $data['document_type'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'sale_date' => $data['sale_date'],
                'reference' => $data['reference'] ?? null,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'tax_percent' => 0,
                'grand_total' => $grandTotal,
                'status' => $data['action_type'],
            ]);

            $sale->items()->delete();

            foreach ($preparedItems as $item) {
                $unitPurchasePrice = (float) $item['unit_purchase_price'];
                $stockAllocations = [];

                if (! $item['product_unit'] && $data['action_type'] === 'completed') {
                    $actualCost = $costingService->allocateSimpleSaleCost($item['product'], $item['quantity']);
                    $unitPurchasePrice = (float) $actualCost['average_unit_cost'];
                    $stockAllocations = collect($actualCost['allocations'])->map(fn ($allocation) => [
                        'product_restock_entry_id' => $allocation['entry_id'],
                        'quantity' => $allocation['quantity'],
                        'unit_purchase_price' => $allocation['unit_purchase_price'],
                        'total_cost' => $allocation['total_cost'],
                    ])->values()->all();
                }

                $saleItem = $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'product_unit_id' => $item['product_unit']?->id,
                    'partner_id' => $item['primary_partner_id'],
                    'product_name' => $item['product']->name,
                    'serial_number' => $item['serial_number'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'unit_purchase_price' => $unitPurchasePrice,
                    'total' => $item['line_total'],
                    'partner_profit' => 0,
                ]);

                if ($item['product_unit'] && $data['action_type'] === 'completed') {
                    $costingService->markSerializedUnitSold($item['product_unit']);
                } elseif ($data['action_type'] === 'completed') {
                    $this->decrementProductStock($item['product'], $item['quantity']);
                    $saleItem->stockAllocations()->createMany($stockAllocations);
                }
            }

            $this->syncDiaryContactFromSale($sale, $sale->created_by ?: auth()->id());
        });

        return redirect()
            ->route('admin.sales.index', $data['action_type'] === 'draft' ? ['draft' => $sale->id] : [])
            ->with('status', $data['action_type'] === 'draft' ? 'Draft updated successfully.' : 'Draft completed successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $costingService = app(ProductCostingService::class);

        DB::transaction(function () use ($sale, $costingService) {
            if ($sale->status === 'completed') {
                $sale->loadMissing('items.productUnit.restockEntry', 'items.product', 'items.stockAllocations');

                $sale->items->each(function ($item) use ($costingService) {
                    if ($item->productUnit) {
                        $costingService->restoreSerializedUnit($item->productUnit);

                        return;
                    }

                    if ($item->stockAllocations->isNotEmpty()) {
                        $costingService->restoreSimpleSaleCost($item);
                    }

                    if ($item->product) {
                        $this->restoreProductStock($item->product, (int) $item->quantity);
                    }
                });
            }

            $sale->delete();
        });

        return redirect()
            ->back()
            ->with('status', 'Sale removed successfully.');
    }

    public function show(Sale $sale): View
    {
        $user = auth()->user();
        if ($user && $user->isSalesman()) {
            abort_if((int) $sale->created_by !== (int) $user->id, 403);
        }
        return view('admin.sales.show', $this->receiptViewData($sale));
    }

    public function printReceipt(Sale $sale): View
    {
        $user = auth()->user();
        if ($user && $user->isSalesman()) {
            abort_if((int) $sale->created_by !== (int) $user->id, 403);
        }
        return view('admin.sales.print', $this->receiptViewData($sale) + [
            'autoPrint' => true,
            'isPdf' => false,
        ]);
    }

    public function downloadReceiptPdf(Sale $sale): Response
    {
        $user = auth()->user();
        if ($user && $user->isSalesman()) {
            abort_if((int) $sale->created_by !== (int) $user->id, 403);
        }
        $pdf = Pdf::loadView('admin.sales.print', $this->receiptViewData($sale) + [
            'autoPrint' => false,
            'isPdf' => true,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('receipt-' . $sale->receipt_no . '.pdf');
    }

    public function report(Request $request): View
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();

        $filters = $this->validatedReportFilters($request);
        $salesQuery = $this->reportSalesQuery($filters, $isSalesman, $user)
            ->with(['items.product']);
        $summarySales = SaleVisibilitySummary::applyToSales((clone $salesQuery)->get());
        $sales = $salesQuery->paginate(10)->withQueryString();
        $sales->setCollection(SaleVisibilitySummary::applyToSales($sales->getCollection()));

        return view('admin.sales.report', [
            'sales' => $sales,
            'filters' => [
                'from_date' => $filters['from_date'] ?? '',
                'to_date' => $filters['to_date'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'summary' => [
                'total_sales' => $summarySales->count(),
                'completed' => $summarySales->where('status', 'completed')->count(),
                'drafts' => $summarySales->where('status', 'draft')->count(),
                'qty_sold' => $summarySales->sum(fn ($sale) => $sale->items->sum('quantity')),
                'grand_total' => $summarySales->sum(fn ($sale) => (float) $sale->grand_total),
            ],
            'isSalesmanView' => $isSalesman,
        ]);
    }

    public function profitReport(Request $request): RedirectResponse
    {
        return redirect()->route('admin.reports.revenue')->with('status', 'Profit report merged into Revenue Report.');
    }

    public function exportReportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();

        $filters = $this->validatedReportFilters($request);

        $sales = SaleVisibilitySummary::applyToSales(
            $this->reportSalesQuery($filters, $isSalesman, $user)
                ->with('items.product')
                ->get()
        );

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Receipt No',
                'Date',
                'Customer',
                'Items Count',
                'Total Qty',
                'Subtotal',
                'Grand Total',
                'Status',
            ]);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->receipt_no,
                    $sale->sale_date?->format('Y-m-d'),
                    $sale->customer_name,
                    $sale->items->count(),
                    $sale->items->sum('quantity'),
                    $sale->subtotal,
                    $sale->grand_total,
                    Str::title($sale->status),
                ]);
            }

            fclose($handle);
        }, 'sales-report.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportProfitReportCsv(Request $request): StreamedResponse
    {
        return $this->exportRevenueCsvFallback($request);
    }

    private function exportRevenueCsvFallback(Request $request): StreamedResponse
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();
        if ($isSalesman) {
            abort(403);
        }

        $filters = $this->validatedReportFilters($request);

        $sales = SaleVisibilitySummary::applyToSales(
            $this->reportSalesQuery($filters, false, null)
                ->with('items.product')
                ->get()
        );

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Receipt No', 'Date', 'Customer', 'Items Count', 'Qty',
                'Subtotal', 'Discount', 'Revenue',
                'COGS (Historical)', 'Gross Profit (Admin)', 'Gross Margin %', 'Status', 'Creator',
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
                    $sale->items->count(),
                    $sale->items->sum('quantity'),
                    round((float) $sale->subtotal, 2),
                    round((float) $sale->discount_total, 2),
                    round((float) $sale->grand_total, 2),
                    round($sCogs, 2),
                    round($sGross, 2),
                    $margin,
                    ucfirst($sale->status ?? ''),
                    $sale->creator?->name ?? '',
                ]);
            }

            fclose($handle);
        }, 'profit-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $isSalesman = $user && $user->isSalesman();

        $salesQuery = Sale::query()
            ->with('items.product')
            ->whereHas('items.product')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($isSalesman, fn ($q) => $q->where('created_by', $user->id))
            ->latest();
        $sales = SaleVisibilitySummary::applyToSales($salesQuery->get());

        $columns = [
            'Receipt No',
            'Date',
            'Customer',
            'Document Type',
            'Product',
            'Qty',
            'Unit Price',
            'Line Total',
        ];
        if (!$isSalesman) {
            $columns[] = 'Gross Profit';
        }
        $columns[] = 'Status';

        return response()->streamDownload(function () use ($sales, $columns, $isSalesman) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $columns);

            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $row = [
                        $sale->receipt_no,
                        $sale->sale_date?->format('Y-m-d'),
                        $sale->customer_name,
                        $sale->document_type,
                        $item->product_name,
                        $item->quantity,
                        $item->price,
                        $item->total,
                    ];
                    if (!$isSalesman) {
                        $row[] = round(((float) $item->price - (float) ($item->historical_cost ?? 0)) * (int) $item->quantity, 2);
                    }
                    $row[] = Str::title($sale->status);
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, 'sales-report.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function calculateAdminProfit(object $item): float
    {
        $purchasePrice = (float) ($item->historical_cost ?? 0);
        $grossProfit = (((float) $item->price) - $purchasePrice) * (int) $item->quantity;

        return $grossProfit;
    }

    private function receiptViewData(Sale $sale): array
    {
        $user = auth()->user();
        $isSalesman = $user && $user->isSalesman();

        $sale->load([
            'creator',
            'items.product.category',
            'items.productUnit',
        ]);
        $sale = SaleVisibilitySummary::applyToSale($sale);

        abort_if($sale === null, 404);

        $discountAmount = ((float) $sale->subtotal * (float) $sale->discount_percent) / 100;
        $adminProfitTotal = $isSalesman
            ? 0.0
            : $sale->items->sum(fn ($item) => $this->calculateAdminProfit($item));

        return [
            'sale' => $sale,
            'settings' => BusinessSetting::query()->first(),
            'discountAmount' => $discountAmount,
            'partnerProfitTotal' => 0,
            'adminProfitTotal' => $adminProfitTotal,
            'isSalesmanView' => $isSalesman,
        ];
    }

    private function nextReceiptNumber(): string
    {
        $nextId = (Sale::withTrashed()->lockForUpdate()->max('id') ?? 0) + 1;
        $receiptNo = $this->formatReceiptNumber($nextId);

        while (Sale::withTrashed()->where('receipt_no', $receiptNo)->exists()) {
            $receiptNo = $this->formatReceiptNumber(++$nextId);
        }

        return $receiptNo;
    }

    private function formatReceiptNumber(int $id): string
    {
        return 'SAL-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    private function validatedReportFilters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'in:completed,draft'],
        ]);
    }

    private function reportSalesQuery(array $filters, bool $isSalesman = false, $user = null)
    {
        return Sale::query()
            ->whereHas('items.product')
            ->when($isSalesman && $user, fn ($q) => $q->where('created_by', $user->id))
            ->when(filled($filters['from_date'] ?? null), function ($query) use ($filters) {
                $query->whereDate('sale_date', '>=', $filters['from_date']);
            })
            ->when(filled($filters['to_date'] ?? null), function ($query) use ($filters) {
                $query->whereDate('sale_date', '<=', $filters['to_date']);
            })
            ->when(filled($filters['status'] ?? null), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest('sale_date')
            ->latest('id');
    }

    private function prepareSalePayload(Request $request): array
    {
        $costingService = app(ProductCostingService::class);

        $data = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'sale_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_unit_id' => ['nullable', 'exists:product_units,id'],
            'items.*.serial_number' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'action_type' => ['required', 'in:draft,completed'],
        ]);

        $selectedWeaponUnits = [];

        $preparedItems = collect($data['items'])
            ->filter(fn (array $item) => filled($item['product_id'] ?? null))
            ->map(function (array $item) use (&$selectedWeaponUnits, $data, $costingService) {
                $product = Product::query()
                    ->with(['partner', 'availableUnits.restockEntry'])
                    ->withCount('availableUnits')
                    ->findOrFail($item['product_id']);
                $productUnit = null;
                $costingService->ensureLegacyTracked($product);
                $product->loadMissing([
                    'availableUnits.restockEntry',
                    'restockEntries' => fn ($query) => $query->where('remaining_quantity', '>', 0)->orderBy('restocked_at')->orderBy('id'),
                ]);

                if ($product->status !== 'active') {
                    throw ValidationException::withMessages([
                        'items' => 'Inactive product sale mein use nahi ho sakta.',
                    ]);
                }

                if ($product->is_serialized) {
                    if (blank($item['product_unit_id'] ?? null)) {
                        throw ValidationException::withMessages([
                            'items' => 'A weapon code is required for serialized guns or rifles.',
                        ]);
                    }

                    if (in_array((int) $item['product_unit_id'], $selectedWeaponUnits, true)) {
                        throw ValidationException::withMessages([
                            'items' => 'The same weapon code cannot be selected twice in one invoice.',
                        ]);
                    }

                    $productUnit = ProductUnit::query()
                        ->where('product_id', $product->id)
                        ->where('status', 'available')
                        ->with('restockEntry')
                        ->findOrFail($item['product_unit_id']);

                    $selectedWeaponUnits[] = (int) $item['product_unit_id'];
                    $item['quantity'] = 1;
                    $item['serial_number'] = $productUnit->weapon_number;
                } elseif (($data['action_type'] ?? 'completed') === 'completed' && (int) $item['quantity'] > $this->availableStockForProduct($product)) {
                    throw ValidationException::withMessages([
                        'items' => 'Selected quantity current stock se zyada hai.',
                    ]);
                }

                $lineTotal = (float) $item['price'] * (int) $item['quantity'];
                $stockCost = $product->is_serialized
                    ? [
                        'average_unit_cost' => $costingService->serializedUnitCost($productUnit),
                    ]
                    : [
                        'average_unit_cost' => (float) $product->unit_purchase_price,
                    ];

                return [
                    'product' => $product,
                    'product_unit' => $productUnit,
                    'unit' => $product->unit,
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                    'unit_purchase_price' => (float) $stockCost['average_unit_cost'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'line_total' => $lineTotal,
                    'primary_partner_id' => $product->partner_id,
                ];
            })
            ->values();

        if ($preparedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Kam az kam ek product add karna required hai.',
            ]);
        }

        $subtotal = $preparedItems->sum('line_total');
        $discountPercent = (float) ($data['discount_percent'] ?? 0);
        $discountAmount = $subtotal * ($discountPercent / 100);
        $grandTotal = $subtotal - $discountAmount;

        return [$data, $preparedItems, $subtotal, $discountPercent, $grandTotal];
    }

    private function availableStockForProduct(Product $product): int
    {
        return $product->is_serialized
            ? (int) ($product->available_units_count ?? $product->availableUnits()->count())
            : (int) $product->stock_quantity;
    }

    private function decrementProductStock(Product $product, int $quantity): void
    {
        if ($product->is_serialized) {
            return;
        }

        $product->decrement('stock_quantity', $quantity);
        $product->refresh();
    }

    private function restoreProductStock(Product $product, int $quantity): void
    {
        if ($product->is_serialized) {
            return;
        }

        $product->increment('stock_quantity', $quantity);
        $product->refresh();
    }

    private function syncDiaryContactFromSale(Sale $sale, ?int $createdBy): void
    {
        if (blank($sale->customer_name) || blank($sale->customer_phone)) {
            return;
        }

        DiaryContact::query()->updateOrCreate(
            ['sale_id' => $sale->id],
            [
                'full_name' => $sale->customer_name,
                'phone' => $sale->customer_phone,
                'source' => 'sale',
                'created_by' => $createdBy,
            ]
        );
    }
}
