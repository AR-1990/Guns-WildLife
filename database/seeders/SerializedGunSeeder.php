<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SerializedGunSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->where('name', 'Firearms')->first();

        if (! $category) {
            return;
        }

        $products = [
            [
                'product_code' => 'PRD-30001',
                'data' => [
                    'name' => 'Beretta 92X Performance',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '300000000001',
                    'purchase_price' => 245000,
                    'selling_price' => 289000,
                    'stock_quantity' => 4,
                    'min_stock_level' => 1,
                    'max_stock_level' => 6,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Premium serialized pistol for sales and stock flow testing.',
                ],
                'units' => [
                    'B92X-240901',
                    'B92X-240902',
                    'B92X-240903',
                    'B92X-240904',
                ],
            ],
            [
                'product_code' => 'PRD-30002',
                'data' => [
                    'name' => 'CZ Shadow 2 Orange',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '300000000002',
                    'purchase_price' => 268000,
                    'selling_price' => 315000,
                    'stock_quantity' => 3,
                    'min_stock_level' => 1,
                    'max_stock_level' => 5,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'High-end competition pistol seeded with unique serials.',
                ],
                'units' => [
                    'CZS2O-240911',
                    'CZS2O-240912',
                    'CZS2O-240913',
                ],
            ],
            [
                'product_code' => 'PRD-30003',
                'data' => [
                    'name' => 'Benelli M4 Tactical',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '300000000003',
                    'purchase_price' => 355000,
                    'selling_price' => 415000,
                    'stock_quantity' => 3,
                    'min_stock_level' => 1,
                    'max_stock_level' => 4,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Serialized tactical shotgun for live demo and invoice testing.',
                ],
                'units' => [
                    'BNM4-240921',
                    'BNM4-240922',
                    'BNM4-240923',
                ],
            ],
            [
                'product_code' => 'PRD-30004',
                'data' => [
                    'name' => 'Tikka T3x Hunter',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '300000000004',
                    'purchase_price' => 310000,
                    'selling_price' => 368000,
                    'stock_quantity' => 4,
                    'min_stock_level' => 1,
                    'max_stock_level' => 6,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Premium hunting rifle with unique seeded serial tracking.',
                ],
                'units' => [
                    'TKT3X-240931',
                    'TKT3X-240932',
                    'TKT3X-240933',
                    'TKT3X-240934',
                ],
            ],
            [
                'product_code' => 'PRD-30005',
                'data' => [
                    'name' => 'Walther PDP Match',
                    'ownership_type' => 'company',
                    'unit' => 'Piece',
                    'barcode' => '300000000005',
                    'purchase_price' => 232000,
                    'selling_price' => 276000,
                    'stock_quantity' => 5,
                    'min_stock_level' => 2,
                    'max_stock_level' => 8,
                    'status' => 'active',
                    'is_serialized' => true,
                    'notes' => 'Modern serialized sidearm with enough stock for repeated sales testing.',
                ],
                'units' => [
                    'WLPDP-240941',
                    'WLPDP-240942',
                    'WLPDP-240943',
                    'WLPDP-240944',
                    'WLPDP-240945',
                ],
            ],
        ];

        foreach ($products as $item) {
            $product = Product::query()->firstOrNew([
                'product_code' => $item['product_code'],
            ]);

            $product->fill($item['data'] + ['category_id' => $category->id]);
            $product->save();

            $product->units()->delete();
            $product->restockEntries()->delete();

            $restockEntry = $product->restockEntries()->create([
                'quantity' => count($item['units']),
                'remaining_quantity' => count($item['units']),
                'unit_purchase_price' => $item['data']['purchase_price'],
                'restocked_at' => now()->toDateString(),
                'notes' => 'Opening serialized stock seeded for testing.',
                'created_by' => null,
                'is_opening' => true,
            ]);

            foreach ($item['units'] as $weaponNumber) {
                $product->units()->create([
                    'product_restock_entry_id' => $restockEntry->id,
                    'weapon_number' => $weaponNumber,
                    'status' => 'available',
                    'unit_purchase_price' => $item['data']['purchase_price'],
                ]);
            }

            $product->updateQuietly([
                'stock_quantity' => count($item['units']),
            ]);
        }
    }
}
