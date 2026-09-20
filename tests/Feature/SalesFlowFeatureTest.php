<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRestockEntry;
use App\Models\Sale;
use App\Models\User;
use App\Support\ProductCostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesFlowFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_salesman_gets_dashboard_and_cannot_open_product_module(): void
    {
        $salesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
        ]);

        Product::query()->create([
            'product_code' => 'PRD-LOW-001',
            'name' => 'Low Stock Rifle',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 100,
            'selling_price' => 125,
            'stock_quantity' => 1,
            'min_stock_level' => 5,
            'status' => 'active',
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        Product::query()->create([
            'product_code' => 'PRD-OUT-001',
            'name' => 'Out Stock Ammo',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Box',
            'purchase_price' => 50,
            'selling_price' => 70,
            'stock_quantity' => 0,
            'min_stock_level' => 5,
            'status' => 'active',
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        $dashboard = $this->actingAs($salesman)->get(route('admin.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSeeText('My Sales Dashboard');
        $dashboard->assertSeeText('Low Stock Alert');
        $dashboard->assertSeeText('Out of Stock');

        $this->actingAs($salesman)
            ->get(route('admin.products.index'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_sales_page_hides_out_of_stock_products(): void
    {
        $salesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
        ]);

        Product::query()->create([
            'product_code' => 'PRD-IN-001',
            'name' => 'Available Shotgun',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 100,
            'selling_price' => 140,
            'stock_quantity' => 3,
            'min_stock_level' => 1,
            'status' => 'active',
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        Product::query()->create([
            'product_code' => 'PRD-OUT-002',
            'name' => 'Zero Stock Scope',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 40,
            'selling_price' => 75,
            'stock_quantity' => 0,
            'min_stock_level' => 1,
            'status' => 'active',
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        $response = $this->actingAs($salesman)->get(route('admin.sales.index'));

        $response->assertOk();
        $response->assertSeeText('Available Shotgun');
        $response->assertDontSeeText('Zero Stock Scope');
    }

    public function test_completed_sale_decrements_simple_stock_and_delete_restores_it(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $product = Product::query()->create([
            'product_code' => 'PRD-SALE-001',
            'name' => 'Sale Flow Pistol',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 5,
            'min_stock_level' => 1,
            'status' => 'active',
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'Walk In',
                'sale_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'price' => 150,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $product->refresh();
        $this->assertSame(3, $product->stock_quantity);

        $sale = Sale::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.sales.destroy', $sale))
            ->assertSessionHas('status');

        $product->refresh();
        $this->assertSame(5, $product->stock_quantity);
    }

    public function test_admin_can_add_and_restock_simple_product_with_separate_restock_flow(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $category = $this->category();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Restock Test Gun',
                'category_id' => $category->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 200,
                'selling_price' => 260,
                'stock_quantity' => 2,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'notes' => 'Initial stock',
                'entry_date' => '2026-08-01',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Restock Test Gun')->firstOrFail();
        $this->assertSame(2, $product->stock_quantity);
        $this->assertDatabaseHas('product_restock_entries', [
            'product_id' => $product->id,
            'quantity' => 2,
            'remaining_quantity' => 2,
            'unit_purchase_price' => 200,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.restock.store', $product), [
                'restocked_at' => now()->toDateString(),
                'purchase_price' => 260,
                'quantity' => 6,
                'notes' => 'Restocked separately',
            ])
            ->assertRedirect(route('admin.products.restock.create', $product));

        $product->refresh();
        $this->assertSame(8, $product->stock_quantity);
        $this->assertSame('260.00', $product->purchase_price);
        $this->assertDatabaseHas('product_restock_entries', [
            'product_id' => $product->id,
            'quantity' => 6,
            'remaining_quantity' => 6,
            'unit_purchase_price' => 260,
        ]);
    }

    public function test_admin_can_create_product_with_backdated_entry_date(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Backdated Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 500,
                'selling_price' => 650,
                'stock_quantity' => 4,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'entry_date' => '2026-08-15',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Backdated Product')->firstOrFail();
        $openingEntry = ProductRestockEntry::query()->where('product_id', $product->id)->firstOrFail();

        $this->assertSame('2026-08-15', $product->created_at?->toDateString());
        $this->assertSame('2026-08-15', $openingEntry->restocked_at?->toDateString());
    }

    public function test_admin_can_update_product_entry_date_from_edit_screen(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Editable Entry Date Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 700,
                'selling_price' => 900,
                'stock_quantity' => 3,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-18',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Editable Entry Date Product')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Editable Entry Date Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 700,
                'selling_price' => 950,
                'stock_quantity' => 3,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-10',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $openingEntry = ProductRestockEntry::query()->where('product_id', $product->id)->firstOrFail();

        $this->assertSame('2026-08-10', $product->created_at?->toDateString());
        $this->assertSame('2026-08-10', $openingEntry->restocked_at?->toDateString());
        $this->assertSame('950.00', $product->selling_price);
    }

    public function test_admin_can_correct_opening_purchase_price_and_stock_before_any_restock_or_sale(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Opening Correction Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 300,
                'selling_price' => 450,
                'stock_quantity' => 2,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-18',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Opening Correction Product')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Opening Correction Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 350,
                'selling_price' => 450,
                'stock_quantity' => 5,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-18',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $openingEntry = ProductRestockEntry::query()->where('product_id', $product->id)->firstOrFail();

        $this->assertSame('350.00', $product->purchase_price);
        $this->assertSame(5, $product->stock_quantity);
        $this->assertSame(5, $openingEntry->quantity);
        $this->assertSame(5, $openingEntry->remaining_quantity);
        $this->assertSame('350.00', $openingEntry->unit_purchase_price);
    }

    public function test_admin_cannot_correct_opening_purchase_price_and_stock_after_restock(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Locked Correction Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 300,
                'selling_price' => 450,
                'stock_quantity' => 2,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-18',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Locked Correction Product')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.restock.store', $product), [
                'restocked_at' => '2026-08-20',
                'purchase_price' => 500,
                'quantity' => 3,
            ])
            ->assertRedirect(route('admin.products.restock.create', $product));

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Locked Correction Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 999,
                'selling_price' => 450,
                'stock_quantity' => 99,
                'min_stock_level' => 1,
                'max_stock_level' => 8,
                'status' => 'active',
                'entry_date' => '2026-08-18',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $openingEntry = ProductRestockEntry::query()
            ->where('product_id', $product->id)
            ->where('is_opening', true)
            ->firstOrFail();

        $this->assertSame('500.00', $product->purchase_price);
        $this->assertSame(5, $product->stock_quantity);
        $this->assertSame(2, $openingEntry->quantity);
        $this->assertSame(2, $openingEntry->remaining_quantity);
        $this->assertSame('300.00', $openingEntry->unit_purchase_price);
    }

    public function test_products_index_shows_summary_cards_and_edit_screen_does_not(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Summary Screen Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 900,
                'selling_price' => 1200,
                'stock_quantity' => 2,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'entry_date' => '2026-08-10',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Summary Screen Product')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.products.restock.create', $product))
            ->assertOk()
            ->assertDontSeeText('Product Analytics')
            ->assertDontSeeText('Stock Snapshot by Date Range');

        $this->actingAs($admin)
            ->get(route('admin.products.index', [
                'tab' => 'products',
                'stats_from_date' => '2026-08-01',
                'stats_to_date' => '2026-09-12',
            ]))
            ->assertOk()
            ->assertSeeText('Current Stock')
            ->assertSeeText('Current Stock Value')
            ->assertSeeText('Current Total Sell')
            ->assertSeeText('Date Range Stock')
            ->assertSeeText('Date Range Stock Value')
            ->assertSeeText('Date Range Total Sell');

        $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertDontSeeText('Product Summary')
            ->assertDontSeeText('Date Range Summary');
    }

    public function test_admin_dashboard_shows_remaining_and_received_stock_metrics(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Dashboard Stock Summary Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 100,
                'selling_price' => 150,
                'stock_quantity' => 3,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'entry_date' => '2026-09-01',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Dashboard Stock Summary Product')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.restock.store', $product), [
                'restocked_at' => '2026-09-05',
                'purchase_price' => 200,
                'quantity' => 2,
            ])
            ->assertRedirect(route('admin.products.restock.create', $product));

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'Dashboard Buyer',
                'sale_date' => '2026-09-07',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'price' => 150,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Remaining Stock')
            ->assertSeeText('Remaining Stock Price')
            ->assertSeeText('Total Stock Received')
            ->assertSeeText('Received Stock Price')
            ->assertSeeText('4')
            ->assertSeeText('Rs. 600.00')
            ->assertSeeText('5')
            ->assertSeeText('Rs. 700.00');
    }

    public function test_fifo_costing_keeps_old_and_new_stock_layers_separate(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $category = $this->category();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'FIFO Test Product',
                'category_id' => $category->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'stock_quantity' => 2,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'entry_date' => '2026-08-01',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'FIFO Test Product')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.restock.store', $product), [
                'restocked_at' => now()->toDateString(),
                'purchase_price' => 1500,
                'quantity' => 15,
            ])
            ->assertRedirect(route('admin.products.restock.create', $product));

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'FIFO Customer',
                'sale_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 15,
                        'price' => 2000,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $sale = Sale::query()->with('items')->firstWhere('customer_name', 'FIFO Customer');
        $this->assertNotNull($sale);
        $this->assertSame('1433.33', $sale->items->first()->unit_purchase_price);

        $openingLayer = ProductRestockEntry::query()
            ->where('product_id', $product->id)
            ->where('unit_purchase_price', 1000)
            ->firstOrFail();
        $restockLayer = ProductRestockEntry::query()
            ->where('product_id', $product->id)
            ->where('unit_purchase_price', 1500)
            ->firstOrFail();

        $this->assertSame(0, $openingLayer->remaining_quantity);
        $this->assertSame(2, $restockLayer->remaining_quantity);
    }

    public function test_stock_snapshot_range_shows_opening_added_sold_and_closing_layers(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Snapshot Product',
                'category_id' => $this->category()->id,
                'ownership_type' => 'company',
                'unit' => 'Piece',
                'purchase_price' => 100,
                'selling_price' => 180,
                'stock_quantity' => 5,
                'min_stock_level' => 1,
                'max_stock_level' => 10,
                'status' => 'active',
                'entry_date' => '2026-09-01',
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Snapshot Product')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.restock.store', $product), [
                'restocked_at' => '2026-09-05',
                'purchase_price' => 150,
                'quantity' => 3,
            ])
            ->assertRedirect(route('admin.products.restock.create', $product));

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'Snapshot Buyer',
                'sale_date' => '2026-09-07',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 4,
                        'price' => 180,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $snapshot = app(ProductCostingService::class)->snapshotRange($product->fresh(), '2026-09-05', '2026-09-10');

        $this->assertSame(5, $snapshot['opening']['stock_quantity']);
        $this->assertSame(3, $snapshot['added_quantity']);
        $this->assertSame(4, $snapshot['sold_quantity']);
        $this->assertSame(4, $snapshot['closing']['stock_quantity']);
        $this->assertSame(550.0, $snapshot['closing']['stock_value']);
        $this->assertCount(2, $snapshot['closing']['layers']);
        $this->assertSame(1, $snapshot['closing']['layers'][0]['remaining_quantity']);
        $this->assertSame(100.0, $snapshot['closing']['layers'][0]['unit_purchase_price']);
        $this->assertSame(3, $snapshot['closing']['layers'][1]['remaining_quantity']);
        $this->assertSame(150.0, $snapshot['closing']['layers'][1]['unit_purchase_price']);
    }

    public function test_serialized_sale_repairs_stale_restock_remaining_quantity_before_selling(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $product = Product::query()->create([
            'product_code' => 'PRD-SER-001',
            'name' => 'Serialized Repair Test',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock_quantity' => 1,
            'min_stock_level' => 1,
            'status' => 'active',
            'is_serialized' => true,
            'commission_type' => 'none',
            'commission_percent' => 0,
            'commission_fixed' => 0,
        ]);

        $entry = $product->restockEntries()->create([
            'quantity' => 1,
            'remaining_quantity' => 0,
            'unit_purchase_price' => 1000,
            'restocked_at' => now()->toDateString(),
            'is_opening' => true,
        ]);

        $unit = $product->units()->create([
            'product_restock_entry_id' => $entry->id,
            'weapon_number' => 'SER-REPAIR-001',
            'status' => 'available',
            'unit_purchase_price' => 1000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'Serialized Buyer',
                'sale_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'product_unit_id' => $unit->id,
                        'quantity' => 1,
                        'price' => 1500,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $entry->refresh();
        $unit->refresh();

        $this->assertSame(0, $entry->remaining_quantity);
        $this->assertSame('sold', $unit->status);
    }

    private function category(): Category
    {
        static $category;

        if (! $category instanceof Category || ! $category->exists || ! Category::query()->whereKey($category->id)->exists()) {
            $category = Category::query()->first() ?: Category::query()->create([
                'name' => 'Testing Category',
                'slug' => 'testing-category',
                'is_active' => true,
            ]);
        }

        return $category;
    }
}
