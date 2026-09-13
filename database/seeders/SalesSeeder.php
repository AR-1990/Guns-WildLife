<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::query()->where('email', 'salesman@guns.local')->firstOrFail();
        $seededReceipts = ['SAL-00001', 'SAL-00002', 'SAL-00003', 'SAL-00004', 'SAL-00005', 'SAL-00006'];

        Sale::withTrashed()
            ->whereIn('receipt_no', $seededReceipts)
            ->forceDelete();

        ProductUnit::query()
            ->whereIn('weapon_number', ['GLK17-001', 'CQA-1001', 'HPS-2001'])
            ->update(['status' => 'available']);

        $glock = Product::query()->where('name', 'Glock 17 Gen 5')->firstOrFail();
        $rifle = Product::query()->where('name', 'CQ-A Tactical Rifle')->firstOrFail();
        $shotgun = Product::query()->where('name', 'Hunter Pro Shotgun')->firstOrFail();
        $cover = Product::query()->where('name', 'Premium Rifle Cover')->firstOrFail();
        $cleaningKit = Product::query()->where('name', 'Universal Cleaning Kit')->firstOrFail();
        $bag = Product::query()->where('name', 'Heavy Duty Gun Bag Large')->firstOrFail();

        $this->createCompletedSerializedSale(
            receiptNo: 'SAL-00001',
            customerName: 'Ahmed Traders',
            customerPhone: '03001234567',
            saleDate: '2026-08-10',
            product: $glock,
            unitWeaponNumber: 'GLK17-001',
            price: 215000,
            createdBy: $salesman->id
        );

        $this->createCompletedSerializedSale(
            receiptNo: 'SAL-00002',
            customerName: 'Capital Security',
            customerPhone: '03111222333',
            saleDate: '2026-08-12',
            product: $rifle,
            unitWeaponNumber: 'CQA-1001',
            price: 268000,
            createdBy: $salesman->id
        );

        $this->createCompletedSerializedSale(
            receiptNo: 'SAL-00003',
            customerName: 'Falcon Hunters',
            customerPhone: '03219876543',
            saleDate: '2026-08-15',
            product: $shotgun,
            unitWeaponNumber: 'HPS-2001',
            price: 129000,
            createdBy: $salesman->id
        );

        $this->createSale([
            'receipt_no' => 'SAL-00004',
            'document_type' => 'sales_invoice',
            'customer_name' => 'Mall Road Store',
            'customer_phone' => '03330001111',
            'sale_date' => '2026-08-18',
            'reference' => 'Accessory counter sale',
            'discount_percent' => 0,
            'status' => 'completed',
            'created_by' => $salesman->id,
        ], [
            [
                'product' => $cover,
                'quantity' => 2,
                'price' => 4800,
            ],
            [
                'product' => $bag,
                'quantity' => 1,
                'price' => 6500,
            ],
        ]);

        $this->createSale([
            'receipt_no' => 'SAL-00005',
            'document_type' => 'sales_invoice',
            'customer_name' => 'Service Counter',
            'customer_phone' => '03005554444',
            'sale_date' => '2026-08-20',
            'reference' => 'Discounted service bundle',
            'discount_percent' => 0,
            'status' => 'completed',
            'created_by' => $salesman->id,
        ], [
            [
                'product' => $cleaningKit,
                'quantity' => 4,
                'price' => 1600,
            ],
        ]);

        $this->createSale([
            'receipt_no' => 'SAL-00006',
            'document_type' => 'sales_invoice',
            'customer_name' => 'Walk-in Customer',
            'customer_phone' => '03440002222',
            'sale_date' => '2026-08-22',
            'reference' => 'Draft quotation',
            'discount_percent' => 0,
            'status' => 'draft',
            'created_by' => $salesman->id,
        ], [
            [
                'product' => $cleaningKit,
                'quantity' => 2,
                'price' => 3200,
            ],
            [
                'product' => $bag,
                'quantity' => 1,
                'price' => 6500,
            ],
        ]);
    }

    private function createCompletedSerializedSale(
        string $receiptNo,
        string $customerName,
        string $customerPhone,
        string $saleDate,
        Product $product,
        string $unitWeaponNumber,
        float $price,
        int $createdBy
    ): void {
        $unit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('weapon_number', $unitWeaponNumber)
            ->firstOrFail();

        $sale = Sale::query()->create([
            'receipt_no' => $receiptNo,
            'document_type' => 'sales_invoice',
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'sale_date' => $saleDate,
            'reference' => 'Seeded completed sale',
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
            'partner_id' => $product->partner_id,
            'product_name' => $product->name,
            'serial_number' => $unit->weapon_number,
            'unit' => $product->unit,
            'quantity' => 1,
            'price' => $price,
            'unit_purchase_price' => (float) $product->unit_purchase_price,
            'total' => $price,
            'partner_profit' => 0,
        ]);

        $unit->update(['status' => 'sold']);
    }

    private function createSale(array $saleData, array $items): void
    {
        $subtotal = round(collect($items)->sum(fn (array $item) => (float) $item['price'] * (int) $item['quantity']), 2);
        $discountPercent = (float) ($saleData['discount_percent'] ?? 0);
        $grandTotal = round($subtotal - ($subtotal * ($discountPercent / 100)), 2);

        $sale = Sale::query()->create($saleData + [
            'subtotal' => $subtotal,
            'tax_percent' => 0,
            'grand_total' => $grandTotal,
        ]);

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item['product'];

            $sale->items()->create([
                'product_id' => $product->id,
                'product_unit_id' => null,
                'partner_id' => $product->partner_id,
                'product_name' => $product->name,
                'serial_number' => null,
                'unit' => $product->unit,
                'quantity' => (int) $item['quantity'],
                'price' => (float) $item['price'],
                'unit_purchase_price' => (float) $product->unit_purchase_price,
                'total' => round((float) $item['price'] * (int) $item['quantity'], 2),
                'partner_profit' => 0,
            ]);
        }
    }
}
