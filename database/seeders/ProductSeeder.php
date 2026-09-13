<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'product_code' => 'PRD-10001',
                'category' => 'Firearms',
                'data' => [
                    'name' => 'Glock 17 Gen 5',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '100000000001',
                    'purchase_price' => 180000,
                    'selling_price' => 210000,
                    'stock_quantity' => 3,
                    'min_stock_level' => 1,
                    'max_stock_level' => 5,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Serialized company-stock product for complete sale flow checks.',
                ],
                'units' => [
                    ['weapon_number' => 'GLK17-001'],
                    ['weapon_number' => 'GLK17-002'],
                    ['weapon_number' => 'GLK17-003'],
                ],
            ],
            [
                'product_code' => 'PRD-10002',
                'category' => 'Firearms',
                'data' => [
                    'name' => 'CQ-A Tactical Rifle',
                    'ownership_type' => 'partner',
                    'unit' => 'Piece',
                    'barcode' => '100000000002',
                    'purchase_price' => 220000,
                    'selling_price' => 260000,
                    'stock_quantity' => 2,
                    'min_stock_level' => 1,
                    'max_stock_level' => 3,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Serialized partner-backed product seeded for multi-partner investment checks.',
                ],
                'units' => [
                    ['weapon_number' => 'CQA-1001'],
                    ['weapon_number' => 'CQA-1002'],
                ],
            ],
            [
                'product_code' => 'PRD-10003',
                'category' => 'Firearms',
                'data' => [
                    'name' => 'Hunter Pro Shotgun',
                    'ownership_type' => 'partner',
                    'unit' => 'Piece',
                    'barcode' => '100000000003',
                    'purchase_price' => 110000,
                    'selling_price' => 135000,
                    'stock_quantity' => 2,
                    'min_stock_level' => 1,
                    'max_stock_level' => 4,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Serialized partner-backed product used for ownership and stock checks.',
                ],
                'units' => [
                    ['weapon_number' => 'HPS-2001'],
                    ['weapon_number' => 'HPS-2002'],
                ],
            ],
            [
                'product_code' => 'PRD-10004',
                'category' => 'Gun Covers',
                'data' => [
                    'name' => 'Premium Rifle Cover',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '100000000004',
                    'purchase_price' => 3000,
                    'selling_price' => 4500,
                    'stock_quantity' => 12,
                    'min_stock_level' => 4,
                    'max_stock_level' => 20,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Regular accessory product for stock, invoice, and revenue checks.',
                ],
                'units' => [],
            ],
            [
                'product_code' => 'PRD-10005',
                'category' => 'Cleaning Kits',
                'data' => [
                    'name' => 'Universal Cleaning Kit',
                    'ownership_type' => 'company',
                    'unit' => 'Set',
                    'barcode' => '100000000005',
                    'purchase_price' => 1800,
                    'selling_price' => 3200,
                    'stock_quantity' => 25,
                    'min_stock_level' => 6,
                    'max_stock_level' => 40,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Company stock item used for dashboard and draft-sale checks.',
                ],
                'units' => [],
            ],
            [
                'product_code' => 'PRD-10006',
                'category' => 'Gun Bags',
                'data' => [
                    'name' => 'Heavy Duty Gun Bag Large',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '100000000006',
                    'purchase_price' => 4200,
                    'selling_price' => 6500,
                    'stock_quantity' => 10,
                    'min_stock_level' => 3,
                    'max_stock_level' => 20,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Accessory stock for list and category checks.',
                ],
                'units' => [],
            ],
            [
                'product_code' => 'PRD-20001',
                'category' => 'Gun Covers',
                'data' => [
                    'name' => 'Easy Calc Product 1',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '200000000001',
                    'purchase_price' => 1000,
                    'selling_price' => 1500,
                    'stock_quantity' => 10,
                    'min_stock_level' => 2,
                    'max_stock_level' => 20,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Demo product for simple khata calculation checks. Quantity 10 with unit cost 100.',
                ],
                'units' => [],
            ],
            [
                'product_code' => 'PRD-20002',
                'category' => 'Cleaning Kits',
                'data' => [
                    'name' => 'Easy Calc Product 2',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '200000000002',
                    'purchase_price' => 1000,
                    'selling_price' => 1500,
                    'stock_quantity' => 10,
                    'min_stock_level' => 2,
                    'max_stock_level' => 20,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Demo product for simple khata calculation checks. Quantity 10 with unit cost 100.',
                ],
                'units' => [],
            ],
            [
                'product_code' => 'PRD-20003',
                'category' => 'Gun Bags',
                'data' => [
                    'name' => 'Easy Calc Product 3',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '200000000003',
                    'purchase_price' => 1000,
                    'selling_price' => 1500,
                    'stock_quantity' => 10,
                    'min_stock_level' => 2,
                    'max_stock_level' => 20,
                    'status' => 'active',
                    'is_serialized' => false,
                    'notes' => 'Demo product for simple khata calculation checks. Quantity 10 with unit cost 100.',
                ],
                'units' => [],
            ],
        ];

        foreach ($products as $item) {
            $category = Category::query()->where('name', $item['category'])->first();

            if (! $category) {
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['product_code' => $item['product_code']],
                $item['data'] + ['category_id' => $category->id]
            );

            $product->units()->delete();

            foreach ($item['units'] as $unit) {
                $product->units()->create($unit + ['status' => 'available']);
            }

            if ($product->is_serialized) {
                $product->updateQuietly([
                    'stock_quantity' => $product->units()->count(),
                ]);
            }
        }
    }
}
