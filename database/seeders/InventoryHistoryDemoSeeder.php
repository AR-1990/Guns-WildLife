<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\User;
use App\Support\ProductCostingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryHistoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->cleanupDemoData();

            $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();
            $salesman = User::query()->where('email', 'salesman@guns.local')->firstOrFail();
            $costingService = app(ProductCostingService::class);

            $scope = $this->seedSimpleDemoProduct($admin->id, $salesman->id, $costingService);
            $this->seedSerializedDemoProduct($admin->id, $salesman->id, $costingService);

            $scope->refresh();
        });
    }

    private function seedSimpleDemoProduct(int $adminId, int $salesmanId, ProductCostingService $costingService): Product
    {
        $category = Category::query()->where('name', 'Optics & Scopes')->firstOrFail();

        $product = Product::query()->create([
            'product_code' => 'PRD-HIST-001',
            'name' => 'Date Snapshot Demo Scope',
            'category_id' => $category->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'barcode' => '910000000001',
            'purchase_price' => 12000,
            'selling_price' => 16500,
            'stock_quantity' => 6,
            'min_stock_level' => 2,
            'max_stock_level' => 20,
            'status' => 'active',
            'is_serialized' => false,
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
            'notes' => 'Demo product for back-date and stock snapshot checking.',
        ]);

        $this->updateProductCreatedAt($product, '2026-08-20 09:00:00');
        $this->touchEntryTimestamp(
            $costingService->createOpeningInventory($product, 12000, [], $adminId, '2026-08-20'),
            '2026-08-20 09:00:00'
        );

        $this->touchEntryTimestamp(
            $costingService->restockProduct($product, 4, 13500, '2026-08-28', 'Demo restock batch 2', $adminId),
            '2026-08-28 11:00:00'
        );

        $this->createSimpleSale(
            receiptNo: 'SAL-HIST-001',
            product: $product,
            quantity: 3,
            price: 17000,
            saleDate: '2026-08-30',
            createdBy: $salesmanId,
            costingService: $costingService
        );

        $this->touchEntryTimestamp(
            $costingService->restockProduct($product, 5, 15000, '2026-09-04', 'Demo restock batch 3', $adminId),
            '2026-09-04 10:30:00'
        );

        $this->createSimpleSale(
            receiptNo: 'SAL-HIST-002',
            product: $product,
            quantity: 4,
            price: 16800,
            saleDate: '2026-09-07',
            createdBy: $salesmanId,
            costingService: $costingService
        );

        return $product;
    }

    private function seedSerializedDemoProduct(int $adminId, int $salesmanId, ProductCostingService $costingService): void
    {
        $category = Category::query()->where('name', 'Firearms')->firstOrFail();

        $product = Product::query()->create([
            'product_code' => 'PRD-HIST-002',
            'name' => 'Date Snapshot Demo Pistol',
            'category_id' => $category->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'barcode' => '910000000002',
            'purchase_price' => 95000,
            'selling_price' => 125000,
            'stock_quantity' => 2,
            'min_stock_level' => 1,
            'max_stock_level' => 10,
            'status' => 'active',
            'is_serialized' => true,
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
            'notes' => 'Serialized demo product for dated stock snapshot checking.',
        ]);

        $this->updateProductCreatedAt($product, '2026-08-22 10:00:00');
        $this->touchEntryTimestamp(
            $costingService->createOpeningInventory(
                $product,
                95000,
                ['DSP-001', 'DSP-002'],
                $adminId,
                '2026-08-22'
            ),
            '2026-08-22 10:00:00'
        );

        $this->touchEntryTimestamp(
            $costingService->restockProduct(
                $product,
                2,
                102000,
                '2026-09-03',
                'Serialized demo restock',
                $adminId,
                ['DSP-003', 'DSP-004']
            ),
            '2026-09-03 12:15:00'
        );

        $this->createSerializedSale(
            receiptNo: 'SAL-HIST-003',
            product: $product,
            weaponNumber: 'DSP-001',
            price: 128000,
            saleDate: '2026-09-06',
            createdBy: $salesmanId,
            costingService: $costingService
        );
    }

    private function createSimpleSale(
        string $receiptNo,
        Product $product,
        int $quantity,
        float $price,
        string $saleDate,
        int $createdBy,
        ProductCostingService $costingService
    ): void {
        $actualCost = $costingService->allocateSimpleSaleCost($product, $quantity);

        $sale = Sale::query()->create([
            'receipt_no' => $receiptNo,
            'document_type' => 'sales_invoice',
            'customer_name' => 'History Demo Customer',
            'customer_phone' => '03000000001',
            'sale_date' => $saleDate,
            'reference' => 'Inventory history demo sale',
            'subtotal' => round($quantity * $price, 2),
            'discount_percent' => 0,
            'tax_percent' => 0,
            'grand_total' => round($quantity * $price, 2),
            'status' => 'completed',
            'created_by' => $createdBy,
        ]);

        $saleItem = $sale->items()->create([
            'product_id' => $product->id,
            'product_unit_id' => null,
            'partner_id' => null,
            'product_name' => $product->name,
            'serial_number' => null,
            'unit' => $product->unit,
            'quantity' => $quantity,
            'price' => $price,
            'unit_purchase_price' => (float) $actualCost['average_unit_cost'],
            'total' => round($quantity * $price, 2),
            'partner_profit' => 0,
        ]);

        $saleItem->stockAllocations()->createMany(
            collect($actualCost['allocations'])->map(fn (array $allocation) => [
                'product_restock_entry_id' => $allocation['entry_id'],
                'quantity' => $allocation['quantity'],
                'unit_purchase_price' => $allocation['unit_purchase_price'],
                'total_cost' => $allocation['total_cost'],
            ])->values()->all()
        );

        $product->decrement('stock_quantity', $quantity);
        $product->refresh();
    }

    private function createSerializedSale(
        string $receiptNo,
        Product $product,
        string $weaponNumber,
        float $price,
        string $saleDate,
        int $createdBy,
        ProductCostingService $costingService
    ): void {
        $unit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('weapon_number', $weaponNumber)
            ->firstOrFail();

        $sale = Sale::query()->create([
            'receipt_no' => $receiptNo,
            'document_type' => 'sales_invoice',
            'customer_name' => 'Serialized Demo Customer',
            'customer_phone' => '03000000002',
            'sale_date' => $saleDate,
            'reference' => 'Serialized inventory history demo sale',
            'subtotal' => $price,
            'discount_percent' => 0,
            'tax_percent' => 0,
            'grand_total' => $price,
            'status' => 'completed',
            'created_by' => $createdBy,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'product_unit_id' => $unit->id,
            'partner_id' => null,
            'product_name' => $product->name,
            'serial_number' => $unit->weapon_number,
            'unit' => $product->unit,
            'quantity' => 1,
            'price' => $price,
            'unit_purchase_price' => $costingService->serializedUnitCost($unit),
            'total' => $price,
            'partner_profit' => 0,
        ]);

        $costingService->markSerializedUnitSold($unit);
    }

    private function cleanupDemoData(): void
    {
        Sale::withTrashed()
            ->whereIn('receipt_no', ['SAL-HIST-001', 'SAL-HIST-002', 'SAL-HIST-003'])
            ->get()
            ->each(function (Sale $sale) {
                $sale->loadMissing('items.stockAllocations');
                foreach ($sale->items as $item) {
                    $item->stockAllocations()->delete();
                }
                $sale->items()->delete();
                $sale->forceDelete();
            });

        Product::withTrashed()
            ->whereIn('product_code', ['PRD-HIST-001', 'PRD-HIST-002'])
            ->get()
            ->each(function (Product $product) {
                $product->units()->delete();
                $product->restockEntries()->delete();
                $product->forceDelete();
            });
    }

    private function updateProductCreatedAt(Product $product, string $dateTime): void
    {
        DB::table('products')
            ->where('id', $product->id)
            ->update([
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
    }

    private function touchEntryTimestamp($entry, string $dateTime): void
    {
        if (! $entry) {
            return;
        }

        DB::table('product_restock_entries')
            ->where('id', $entry->id)
            ->update([
                'created_at' => $dateTime,
                'updated_at' => $dateTime,
            ]);
    }
}
