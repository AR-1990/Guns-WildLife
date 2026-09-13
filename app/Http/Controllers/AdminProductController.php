<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Support\ProductCostingService;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->get('tab', 'products');
        $productFilters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'filter_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filter_status' => ['nullable', Rule::in(['active', 'inactive'])],
            'stats_from_date' => ['nullable', 'date'],
            'stats_to_date' => ['nullable', 'date', 'after_or_equal:stats_from_date'],
        ]);

        $categoriesQuery = Category::query()
            ->withCount('products')
            ->withCount([
                'products as active_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name');

        $productsQuery = Product::query()
            ->with(['category', 'availableUnits'])
            ->when(filled($productFilters['search'] ?? null), function ($query) use ($productFilters) {
                $search = trim((string) $productFilters['search']);

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('product_code', 'like', '%' . $search . '%');
                });
            })
            ->when(filled($productFilters['filter_category_id'] ?? null), fn ($query) => $query->where('category_id', $productFilters['filter_category_id']))
            ->when(filled($productFilters['filter_status'] ?? null), fn ($query) => $query->where('status', $productFilters['filter_status']))
            ->latest();

        $products = $productsQuery->paginate(10, ['*'], 'products_page')->withQueryString();
        $categories = $categoriesQuery->paginate(10, ['*'], 'categories_page')->withQueryString();
        $summaryProducts = (clone $productsQuery)->get();
        $hasStatsRange = filled($productFilters['stats_from_date'] ?? null) || filled($productFilters['stats_to_date'] ?? null);
        $summary = $this->productListSummary(
            $summaryProducts,
            $productFilters['stats_from_date'] ?? null,
            $productFilters['stats_to_date'] ?? null,
        );

        $editingCategory = $request->filled('category')
            ? Category::query()->find($request->integer('category'))
            : null;

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'editingCategory' => $editingCategory,
            'productCount' => (clone $productsQuery)->count(),
            'categoryCount' => (clone $categoriesQuery)->count(),
            'activeTab' => $activeTab,
            'productFilters' => [
                'search' => $productFilters['search'] ?? '',
                'filter_category_id' => $productFilters['filter_category_id'] ?? '',
                'filter_status' => $productFilters['filter_status'] ?? '',
                'stats_from_date' => $productFilters['stats_from_date'] ?? '',
                'stats_to_date' => $productFilters['stats_to_date'] ?? '',
            ],
            'productFilterCategories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'summary' => $summary,
            'hasStatsRange' => $hasStatsRange,
        ]);
    }

    public function create(): View
    {
        return $this->productFormView(new Product(), 'Add Product', 'Add Products');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $costingService = app(ProductCostingService::class);
        $entryDate = Carbon::parse((string) ($data['entry_date'] ?? now()->toDateString()))->startOfDay();

        DB::transaction(function () use ($request, $data, $costingService, $entryDate) {
            $productPayload = $data;
            unset($productPayload['entry_date']);

            if ($request->hasFile('image')) {
                $productPayload['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::query()->create($productPayload);
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate,
                ]);
            $product->forceFill([
                'created_at' => $entryDate,
                'updated_at' => $entryDate,
            ])->syncOriginal();

            $openingEntry = $costingService->createOpeningInventory(
                $product,
                (float) $productPayload['purchase_price'],
                $this->weaponNumbersFromRequest($request),
                $request->user()?->id,
                $entryDate->toDateString()
            );

            if ($openingEntry) {
                DB::table('product_restock_entries')
                    ->where('id', $openingEntry->id)
                    ->update([
                        'restocked_at' => $entryDate->toDateString(),
                        'created_at' => $entryDate,
                        'updated_at' => $entryDate,
                    ]);
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product added successfully.');
    }

    public function edit(Request $request, Product $product): View
    {
        $product->load('units');

        return $this->productFormView($product, 'Edit Product', 'Edit Product', $request);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product);
        $entryDate = Carbon::parse((string) ($data['entry_date'] ?? optional($product->created_at)->toDateString() ?: now()->toDateString()))->startOfDay();

        DB::transaction(function () use ($request, $product, $data, $entryDate) {
            $productPayload = $data;
            unset($productPayload['entry_date']);

            if ($request->hasFile('image')) {
                $productPayload['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product->update($productPayload);
            $this->syncProductEntryDate($product, $entryDate);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function createRestock(Product $product): View
    {
        $costingService = app(ProductCostingService::class);
        $costingService->ensureLegacyTracked($product);

        $product->load([
            'category',
            'restockEntries' => fn ($query) => $query->latest('restocked_at')->latest('id'),
            'availableUnits' => fn ($query) => $query->orderBy('weapon_number'),
        ]);

        return view('admin.products.restock', [
            'product' => $product,
        ]);
    }

    public function storeRestock(Request $request, Product $product): RedirectResponse
    {
        $costingService = app(ProductCostingService::class);
        $costingService->ensureLegacyTracked($product);

        $data = $request->validate([
            'restocked_at' => ['required', 'date'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'weapon_numbers' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $product, $costingService, $data) {
            $costingService->restockProduct(
                $product,
                (int) $data['quantity'],
                (float) $data['purchase_price'],
                $data['restocked_at'],
                $data['notes'] ?? null,
                $request->user()?->id,
                $this->weaponNumbersFromTextarea((string) ($data['weapon_numbers'] ?? ''))
            );
        });

        return redirect()
            ->route('admin.products.restock.create', $product)
            ->with('status', 'Product restocked successfully.');
    }

    public function toggleProduct(Product $product): RedirectResponse
    {
        $product->update([
            'status' => $product->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', $product->status === 'active' ? 'Product activated successfully.' : 'Product deactivated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['status' => 'inactive']);
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Category::query()->create([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.products.index', ['tab' => 'category'])
            ->with('status', 'Category added successfully.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name'], $category->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.products.index', ['tab' => 'category'])
            ->with('status', 'Category updated successfully.');
    }

    public function toggleCategory(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return redirect()
            ->route('admin.products.index', ['tab' => 'category'])
            ->with('status', $category->is_active ? 'Category activated successfully.' : 'Category deactivated successfully.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        DB::transaction(function () use ($category) {
            $category->products()->get()->each(function (Product $product) {
                $product->update(['status' => 'inactive']);
                $product->delete();
            });

            $category->update(['is_active' => false]);
            $category->delete();
        });

        return redirect()
            ->route('admin.products.index', ['tab' => 'category'])
            ->with('status', 'Category deleted successfully.');
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function productFormView(Product $product, string $title, string $pageHeading, ?Request $request = null): View
    {
        $product->loadMissing(['units']);

        return view('admin.products.create.create-product', [
            'product' => $product,
            'title' => $title,
            'pageHeading' => $pageHeading,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'productUnits' => old('weapon_units', $product->units->map(fn (ProductUnit $unit) => [
                'id' => $unit->id,
                'weapon_number' => $unit->weapon_number,
                'status' => $unit->status,
            ])->values()->all()),
        ]);
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'product_code' => $product?->product_code ?: $this->nextProductCode(),
        ]);

        $data = $request->validate([
            'product_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'product_code')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'ownership_type' => ['required', Rule::in(['company', 'partner'])],
            'unit' => ['required', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'max_stock_level' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_serialized' => ['nullable', 'boolean'],
            'weapon_units' => ['nullable', 'array'],
            'weapon_units.*.id' => ['nullable', 'integer'],
            'weapon_units.*.weapon_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'entry_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($product) {
                    if (! $product) {
                        return;
                    }

                    $entryDate = Carbon::parse((string) $value)->toDateString();
                    $earliestLockedDate = $this->earliestLockedInventoryDate($product);

                    if ($earliestLockedDate && $entryDate > $earliestLockedDate) {
                        $fail('Entry date restock ya sale history ke baad ki nahi ho sakti.');
                    }
                },
            ],
        ]);

        $data['is_serialized'] = $request->boolean('is_serialized');
        $data['secondary_uom'] = null;
        $data['pack_size'] = null;
        $data['tax_rate'] = 0;
        $data['batch_expiry'] = false;
        $data['requires_license'] = false;
        $data['weapon_number'] = null;
        $data['ownership_type'] = 'company';
        $data['partner_id'] = null;
        $data['commission_type'] = 'none';
        $data['commission_percent'] = 0;
        $data['commission_fixed'] = 0;

        if (! filled($data['purchase_price'] ?? null)) {
            throw ValidationException::withMessages([
                'purchase_price' => 'Purchase amount required hai.',
            ]);
        }

        if ($product) {
            $data['purchase_price'] = (float) $product->purchase_price;
            $data['stock_quantity'] = (int) $product->stock_quantity;
            $data['is_serialized'] = (bool) $product->is_serialized;
        }

        return $data;
    }

    private function nextProductCode(): string
    {
        $nextId = (Product::withTrashed()->lockForUpdate()->max('id') ?? 0) + 1;
        $productCode = $this->formatProductCode($nextId);

        while (Product::withTrashed()->where('product_code', $productCode)->exists()) {
            $productCode = $this->formatProductCode(++$nextId);
        }

        return $productCode;
    }

    private function formatProductCode(int $id): string
    {
        return 'PRD-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }

    private function syncProductUnits(Request $request, Product $product): void
    {
        if (! $request->boolean('is_serialized')) {
            $product->units()->delete();

            return;
        }

        $units = collect($request->input('weapon_units', []))
            ->filter(fn (array $unit) => filled($unit['weapon_number'] ?? null))
            ->values();

        $keepIds = [];

        foreach ($units as $unitData) {
            $payload = [
                'weapon_number' => $unitData['weapon_number'],
            ];

            $unit = $product->units()->updateOrCreate(
                ['id' => $unitData['id'] ?? null],
                $payload + ['status' => 'available']
            );

            $keepIds[] = $unit->id;
        }

        $product->units()
            ->whereNotIn('id', $keepIds)
            ->where('status', 'available')
            ->delete();

        $product->updateQuietly([
            'stock_quantity' => $product->units()->count(),
        ]);
    }

    private function weaponNumbersFromRequest(Request $request): array
    {
        return collect($request->input('weapon_units', []))
            ->pluck('weapon_number')
            ->map(fn ($weaponNumber) => trim((string) $weaponNumber))
            ->filter()
            ->values()
            ->all();
    }

    private function weaponNumbersFromTextarea(string $weaponNumbers): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $weaponNumbers) ?: [])
            ->map(fn ($weaponNumber) => trim((string) $weaponNumber))
            ->filter()
            ->values()
            ->all();
    }

    private function syncProductEntryDate(Product $product, Carbon $entryDate): void
    {
        DB::table('products')
            ->where('id', $product->id)
            ->update([
                'created_at' => $entryDate,
            ]);

        $product->forceFill([
            'created_at' => $entryDate,
        ])->syncOriginalAttribute('created_at');

        $openingEntryId = $product->restockEntries()
            ->where('is_opening', true)
            ->orderBy('id')
            ->value('id');

        if (! $openingEntryId) {
            return;
        }

        DB::table('product_restock_entries')
            ->where('id', $openingEntryId)
            ->update([
                'restocked_at' => $entryDate->toDateString(),
                'created_at' => $entryDate,
            ]);
    }

    private function earliestLockedInventoryDate(Product $product): ?string
    {
        $earliestRestockDate = $product->restockEntries()
            ->where('is_opening', false)
            ->min('restocked_at');

        $earliestSaleDate = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $product->id)
            ->whereNull('sales.deleted_at')
            ->min('sales.sale_date');

        return collect([$earliestRestockDate, $earliestSaleDate])
            ->filter()
            ->sort()
            ->first();
    }

    private function productListSummary($products, ?string $fromDate = null, ?string $toDate = null): array
    {
        $costingService = app(ProductCostingService::class);

        $currentStock = 0;
        $currentStockValue = 0.0;
        $currentTotalSell = 0;
        $rangeStock = 0;
        $rangeStockValue = 0.0;
        $rangeTotalSell = 0;

        $resolvedFromDate = null;
        $resolvedToDate = null;

        if (filled($fromDate) || filled($toDate)) {
            $resolvedFromDate = Carbon::parse((string) ($fromDate ?: $toDate))->toDateString();
            $resolvedToDate = Carbon::parse((string) ($toDate ?: $fromDate))->toDateString();
        }

        foreach ($products as $product) {
            $costingService->ensureLegacyTracked($product);

            $currentSnapshot = $costingService->snapshotAsOf($product, now()->toDateString());
            $overallSales = $this->productSalesSummary($product);

            $currentStock += (int) $currentSnapshot['stock_quantity'];
            $currentStockValue += (float) $currentSnapshot['stock_value'];
            $currentTotalSell += (int) $overallSales['qty'];

            if ($resolvedFromDate && $resolvedToDate) {
                $rangeSnapshot = $costingService->snapshotRange($product, $resolvedFromDate, $resolvedToDate);
                $rangeSales = $this->productSalesSummary($product, $resolvedFromDate, $resolvedToDate);

                $rangeStock += (int) $rangeSnapshot['closing']['stock_quantity'];
                $rangeStockValue += (float) $rangeSnapshot['closing']['stock_value'];
                $rangeTotalSell += (int) $rangeSales['qty'];
            }
        }

        return [
            'current' => [
                'total_stock' => $currentStock,
                'total_stock_value' => round($currentStockValue, 2),
                'total_sell' => $currentTotalSell,
            ],
            'range' => [
                'total_stock' => $resolvedFromDate && $resolvedToDate ? $rangeStock : 0,
                'total_stock_value' => $resolvedFromDate && $resolvedToDate ? round($rangeStockValue, 2) : 0.0,
                'total_sell' => $resolvedFromDate && $resolvedToDate ? $rangeTotalSell : 0,
            ],
        ];
    }

    private function productSalesSummary(Product $product, ?string $fromDate = null, ?string $toDate = null): array
    {
        $summary = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.product_id', $product->id)
            ->whereNull('sales.deleted_at')
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty, COALESCE(SUM(sale_items.total), 0) as total_amount')
            ->when($fromDate, fn ($query) => $query->whereDate('sales.sale_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('sales.sale_date', '<=', $toDate))
            ->first();

        $qty = (int) ($summary->total_qty ?? 0);
        $amount = round((float) ($summary->total_amount ?? 0), 2);

        return [
            'qty' => $qty,
            'amount' => $amount,
            'average_price' => $qty > 0 ? round($amount / $qty, 2) : 0.0,
        ];
    }
}
