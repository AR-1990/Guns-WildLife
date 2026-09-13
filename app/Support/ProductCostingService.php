<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductRestockEntry;
use App\Models\ProductUnit;
use App\Models\SaleItemStockAllocation;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductCostingService
{
    public function ensureLegacyTracked(Product $product): void
    {
        if ($product->restockEntries()->exists()) {
            if ($product->is_serialized) {
                $entryId = $product->restockEntries()->oldest('restocked_at')->oldest('id')->value('id');

                $product->units()
                    ->where(function ($query) {
                        $query->whereNull('product_restock_entry_id')
                            ->orWhere('unit_purchase_price', '<=', 0);
                    })
                    ->get()
                    ->each(function (ProductUnit $unit) use ($entryId, $product) {
                        $unit->updateQuietly([
                            'product_restock_entry_id' => $unit->product_restock_entry_id ?: $entryId,
                            'unit_purchase_price' => (float) ($unit->unit_purchase_price ?: $product->unit_purchase_price),
                        ]);
                    });

                $this->syncSerializedRestockQuantities($product);
            }

            return;
        }

        $restockedAt = now()->toDateString();

        if ($product->is_serialized) {
            $units = $product->units()->get();
            if ($units->isEmpty()) {
                return;
            }

            $entry = ProductRestockEntry::query()->create([
                'product_id' => $product->id,
                'quantity' => $units->count(),
                'remaining_quantity' => $units->where('status', 'available')->count(),
                'unit_purchase_price' => (float) $product->unit_purchase_price,
                'restocked_at' => $restockedAt,
                'notes' => 'Opening stock migrated from existing product record.',
                'created_by' => null,
                'is_opening' => true,
            ]);

            $product->units()->update([
                'product_restock_entry_id' => $entry->id,
                'unit_purchase_price' => (float) $product->unit_purchase_price,
            ]);

            $product->updateQuietly([
                'stock_quantity' => $product->availableUnits()->count(),
            ]);

            return;
        }

        if ((int) $product->stock_quantity <= 0) {
            return;
        }

        ProductRestockEntry::query()->create([
            'product_id' => $product->id,
            'quantity' => (int) $product->stock_quantity,
            'remaining_quantity' => (int) $product->stock_quantity,
            'unit_purchase_price' => (float) $product->unit_purchase_price,
            'restocked_at' => $restockedAt,
            'notes' => 'Opening stock migrated from existing product record.',
            'created_by' => null,
            'is_opening' => true,
        ]);
    }

    public function createOpeningInventory(Product $product, float $unitPurchasePrice, array $weaponNumbers = [], ?int $createdBy = null, ?string $restockedAt = null): ?ProductRestockEntry
    {
        $restockedAt = $restockedAt ?: now()->toDateString();

        if ($product->is_serialized) {
            $weaponNumbers = collect($weaponNumbers)
                ->map(fn ($weaponNumber) => trim((string) $weaponNumber))
                ->filter()
                ->values();

            if ($weaponNumbers->isEmpty()) {
                $product->updateQuietly([
                    'stock_quantity' => 0,
                ]);

                return null;
            }

            $entry = ProductRestockEntry::query()->create([
                'product_id' => $product->id,
                'quantity' => $weaponNumbers->count(),
                'remaining_quantity' => $weaponNumbers->count(),
                'unit_purchase_price' => $unitPurchasePrice,
                'restocked_at' => $restockedAt,
                'notes' => 'Opening stock entry created with product.',
                'created_by' => $createdBy,
                'is_opening' => true,
            ]);

            $weaponNumbers->each(function (string $weaponNumber) use ($product, $entry, $unitPurchasePrice) {
                $product->units()->create([
                    'product_restock_entry_id' => $entry->id,
                    'weapon_number' => $weaponNumber,
                    'status' => 'available',
                    'unit_purchase_price' => $unitPurchasePrice,
                ]);
            });

            $product->updateQuietly([
                'stock_quantity' => $weaponNumbers->count(),
            ]);

            return $entry;
        }

        if ((int) $product->stock_quantity <= 0) {
            return null;
        }

        return ProductRestockEntry::query()->create([
            'product_id' => $product->id,
            'quantity' => (int) $product->stock_quantity,
            'remaining_quantity' => (int) $product->stock_quantity,
            'unit_purchase_price' => $unitPurchasePrice,
            'restocked_at' => $restockedAt,
            'notes' => 'Opening stock entry created with product.',
            'created_by' => $createdBy,
            'is_opening' => true,
        ]);
    }

    public function snapshotAsOf(Product $product, ?string $asOfDate = null): array
    {
        $this->ensureLegacyTracked($product);

        $asOf = Carbon::parse($asOfDate ?: now()->toDateString())->toDateString();
        $entries = $product->restockEntries()
            ->whereDate('restocked_at', '<=', $asOf)
            ->orderBy('restocked_at')
            ->orderBy('id')
            ->get();

        $soldByEntry = $this->soldQuantityByEntry($product, null, $asOf);
        $layers = [];
        $stockQuantity = 0;
        $stockValue = 0.0;

        foreach ($entries as $entry) {
            $soldQuantity = (int) ($soldByEntry[$entry->id] ?? 0);
            $remainingQuantity = max((int) $entry->quantity - $soldQuantity, 0);

            if ($remainingQuantity <= 0) {
                continue;
            }

            $unitPurchasePrice = (float) $entry->unit_purchase_price;
            $layerValue = round($remainingQuantity * $unitPurchasePrice, 2);

            $layers[] = [
                'entry_id' => $entry->id,
                'restocked_at' => $entry->restocked_at,
                'type' => $entry->is_opening ? 'Opening' : 'Restock',
                'quantity' => (int) $entry->quantity,
                'sold_quantity' => $soldQuantity,
                'remaining_quantity' => $remainingQuantity,
                'unit_purchase_price' => $unitPurchasePrice,
                'layer_value' => $layerValue,
            ];

            $stockQuantity += $remainingQuantity;
            $stockValue += $layerValue;
        }

        return [
            'as_of_date' => $asOf,
            'stock_quantity' => $stockQuantity,
            'stock_value' => round($stockValue, 2),
            'layers' => $layers,
        ];
    }

    public function snapshotRange(Product $product, ?string $fromDate = null, ?string $toDate = null): array
    {
        $toDate = Carbon::parse($toDate ?: now()->toDateString())->toDateString();
        $fromDate = Carbon::parse($fromDate ?: $toDate)->toDateString();
        $openingDate = Carbon::parse($fromDate)->subDay()->toDateString();

        $openingSnapshot = $this->snapshotAsOf($product, $openingDate);
        $closingSnapshot = $this->snapshotAsOf($product, $toDate);

        $addedInRange = (int) $product->restockEntries()
            ->when($fromDate, fn ($query) => $query->whereDate('restocked_at', '>=', $fromDate))
            ->whereDate('restocked_at', '<=', $toDate)
            ->sum('quantity');

        $soldInRange = $this->soldQuantityBetween($product, $fromDate, $toDate);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening' => $openingSnapshot,
            'closing' => $closingSnapshot,
            'added_quantity' => $addedInRange,
            'sold_quantity' => $soldInRange,
        ];
    }

    public function restockProduct(Product $product, int $quantity, float $unitPurchasePrice, ?string $restockedAt = null, ?string $notes = null, ?int $createdBy = null, array $weaponNumbers = []): ProductRestockEntry
    {
        $restockedAt = $restockedAt ?: now()->toDateString();

        if ($product->is_serialized) {
            $weaponNumbers = collect($weaponNumbers)
                ->map(fn ($weaponNumber) => trim((string) $weaponNumber))
                ->filter()
                ->values();

            if ($weaponNumbers->isEmpty()) {
                throw ValidationException::withMessages([
                    'weapon_numbers' => 'Serialized product ke liye weapon codes dena zaroori hai.',
                ]);
            }

            if ($quantity !== $weaponNumbers->count()) {
                throw ValidationException::withMessages([
                    'quantity' => 'Serialized quantity weapon codes ke count ke barabar honi chahiye.',
                ]);
            }

            $duplicate = $weaponNumbers->groupBy(fn ($weaponNumber) => $weaponNumber)->first(fn (Collection $rows) => $rows->count() > 1);
            if ($duplicate) {
                throw ValidationException::withMessages([
                    'weapon_numbers' => 'Same weapon code aik restock mein do dafa use nahi ho sakta.',
                ]);
            }

            $existingWeaponNumbers = $product->units()
                ->whereIn('weapon_number', $weaponNumbers->all())
                ->pluck('weapon_number');

            if ($existingWeaponNumbers->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'weapon_numbers' => 'Yeh weapon code pehle se product par mojood hai: ' . $existingWeaponNumbers->implode(', '),
                ]);
            }
        }

        $entry = ProductRestockEntry::query()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'unit_purchase_price' => $unitPurchasePrice,
            'restocked_at' => $restockedAt,
            'notes' => $notes,
            'created_by' => $createdBy,
            'is_opening' => false,
        ]);

        if ($product->is_serialized) {
            collect($weaponNumbers)->each(function (string $weaponNumber) use ($product, $entry, $unitPurchasePrice) {
                $product->units()->create([
                    'product_restock_entry_id' => $entry->id,
                    'weapon_number' => $weaponNumber,
                    'status' => 'available',
                    'unit_purchase_price' => $unitPurchasePrice,
                ]);
            });

            $product->updateQuietly([
                'purchase_price' => $unitPurchasePrice,
                'stock_quantity' => $product->availableUnits()->count(),
            ]);

            return $entry;
        }

        $product->increment('stock_quantity', $quantity);
        $product->updateQuietly([
            'purchase_price' => $unitPurchasePrice,
        ]);

        return $entry;
    }

    public function previewSimpleSaleCost(Product $product, int $quantity): array
    {
        $this->ensureLegacyTracked($product);

        $layers = $product->restockEntries()
            ->where('remaining_quantity', '>', 0)
            ->orderBy('restocked_at')
            ->orderBy('id')
            ->get();

        $remaining = $quantity;
        $totalCost = 0.0;
        $preview = [];

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $picked = min($remaining, (int) $layer->remaining_quantity);
            $cost = round($picked * (float) $layer->unit_purchase_price, 2);
            $preview[] = [
                'entry_id' => $layer->id,
                'quantity' => $picked,
                'unit_purchase_price' => (float) $layer->unit_purchase_price,
                'total_cost' => $cost,
            ];

            $totalCost += $cost;
            $remaining -= $picked;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => 'Selected quantity current stock se zyada hai.',
            ]);
        }

        return [
            'allocations' => $preview,
            'total_cost' => round($totalCost, 2),
            'average_unit_cost' => $quantity > 0 ? round($totalCost / $quantity, 2) : 0.0,
        ];
    }

    public function allocateSimpleSaleCost(Product $product, int $quantity): array
    {
        $preview = $this->previewSimpleSaleCost($product, $quantity);

        foreach ($preview['allocations'] as $allocation) {
            ProductRestockEntry::query()
                ->whereKey($allocation['entry_id'])
                ->decrement('remaining_quantity', $allocation['quantity']);
        }

        return $preview;
    }

    public function restoreSimpleSaleCost(SaleItem $saleItem): void
    {
        $saleItem->loadMissing('stockAllocations');

        foreach ($saleItem->stockAllocations as $allocation) {
            ProductRestockEntry::query()
                ->whereKey($allocation->product_restock_entry_id)
                ->increment('remaining_quantity', (int) $allocation->quantity);
        }
    }

    public function serializedUnitCost(ProductUnit $productUnit): float
    {
        return (float) ($productUnit->unit_purchase_price ?: $productUnit->product?->unit_purchase_price ?: 0);
    }

    public function syncSerializedRestockQuantities(Product $product): void
    {
        if (! $product->is_serialized) {
            return;
        }

        $availableCounts = $product->units()
            ->select('product_restock_entry_id', DB::raw('COUNT(*) as available_count'))
            ->where('status', 'available')
            ->whereNotNull('product_restock_entry_id')
            ->groupBy('product_restock_entry_id')
            ->pluck('available_count', 'product_restock_entry_id');

        $product->restockEntries()->get()->each(function (ProductRestockEntry $entry) use ($availableCounts) {
            $remainingQuantity = (int) ($availableCounts[$entry->id] ?? 0);

            if ((int) $entry->remaining_quantity !== $remainingQuantity) {
                $entry->updateQuietly([
                    'remaining_quantity' => $remainingQuantity,
                ]);
            }
        });

        $product->updateQuietly([
            'stock_quantity' => $product->availableUnits()->count(),
        ]);
    }

    public function markSerializedUnitSold(ProductUnit $productUnit): void
    {
        $productUnit->loadMissing('product', 'restockEntry');

        if (! $productUnit->product) {
            throw ValidationException::withMessages([
                'items' => 'Selected weapon code product ke sath link nahi mil raha.',
            ]);
        }

        if ($productUnit->status !== 'available') {
            throw ValidationException::withMessages([
                'items' => 'Selected weapon code already sold ya unavailable hai.',
            ]);
        }

        $this->syncSerializedRestockQuantities($productUnit->product);
        $productUnit->refresh()->loadMissing('restockEntry');

        if ($productUnit->restockEntry) {
            $updated = ProductRestockEntry::query()
                ->whereKey($productUnit->restockEntry->id)
                ->where('remaining_quantity', '>', 0)
                ->decrement('remaining_quantity', 1);

            if (! $updated) {
                throw ValidationException::withMessages([
                    'items' => 'Selected weapon code ka stock layer stale tha. Page refresh karke dobara try karein.',
                ]);
            }
        }

        $productUnit->update(['status' => 'sold']);
        $productUnit->product?->updateQuietly([
            'stock_quantity' => $productUnit->product->availableUnits()->count(),
        ]);
    }

    public function restoreSerializedUnit(ProductUnit $productUnit): void
    {
        $productUnit->loadMissing('product', 'restockEntry');

        $productUnit->update(['status' => 'available']);

        if ($productUnit->product) {
            $this->syncSerializedRestockQuantities($productUnit->product);
        } elseif ($productUnit->restockEntry) {
            $productUnit->restockEntry()->increment('remaining_quantity', 1);
        }
    }

    private function soldQuantityBetween(Product $product, string $fromDate, string $toDate): int
    {
        return (int) SaleItemStockAllocation::query()
            ->join('product_restock_entries', 'product_restock_entries.id', '=', 'sale_item_stock_allocations.product_restock_entry_id')
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_stock_allocations.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('product_restock_entries.product_id', $product->id)
            ->whereNull('sales.deleted_at')
            ->whereDate('sales.sale_date', '>=', $fromDate)
            ->whereDate('sales.sale_date', '<=', $toDate)
            ->sum('sale_item_stock_allocations.quantity');
    }

    private function soldQuantityByEntry(Product $product, ?string $fromDate = null, ?string $toDate = null): array
    {
        $rows = SaleItemStockAllocation::query()
            ->selectRaw('sale_item_stock_allocations.product_restock_entry_id, COALESCE(SUM(sale_item_stock_allocations.quantity), 0) as sold_quantity')
            ->join('product_restock_entries', 'product_restock_entries.id', '=', 'sale_item_stock_allocations.product_restock_entry_id')
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_stock_allocations.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('product_restock_entries.product_id', $product->id)
            ->whereNull('sales.deleted_at')
            ->when($fromDate, fn ($query) => $query->whereDate('sales.sale_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('sales.sale_date', '<=', $toDate))
            ->groupBy('sale_item_stock_allocations.product_restock_entry_id')
            ->get();

        return $rows
            ->mapWithKeys(fn ($row) => [(int) $row->product_restock_entry_id => (int) $row->sold_quantity])
            ->all();
    }
}
