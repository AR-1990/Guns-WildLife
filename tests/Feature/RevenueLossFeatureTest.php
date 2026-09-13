<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueLossFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_loss_report_counts_only_active_completed_below_cost_sales_and_revenue_matches(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $product = Product::query()->create([
            'product_code' => 'LOSS-001',
            'name' => 'Loss Test Product',
            'category_id' => $this->category()->id,
            'ownership_type' => 'company',
            'unit' => 'Piece',
            'purchase_price' => 1000,
            'selling_price' => 1500,
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
                'customer_name' => 'Loss Customer',
                'customer_phone' => '03005554444',
                'sale_date' => '2026-09-10',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'price' => 800,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $trashedSale = Sale::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), [
                'document_type' => 'sales_invoice',
                'customer_name' => 'Deleted Loss Customer',
                'customer_phone' => '03006665555',
                'sale_date' => '2026-09-10',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'price' => 700,
                    ],
                ],
                'discount_percent' => 0,
                'action_type' => 'completed',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $deletedSale = Sale::query()->latest('id')->firstOrFail();
        $deletedSale->delete();

        $lossResponse = $this->actingAs($admin)->get(route('admin.reports.loss'));
        $lossResponse->assertOk();
        $lossResponse->assertSeeText('Loss Customer');
        $lossResponse->assertDontSeeText('Deleted Loss Customer');
        $lossResponse->assertSeeText('200');

        $revenueResponse = $this->actingAs($admin)->get(route('admin.reports.revenue'));
        $revenueResponse->assertOk();
        $revenueResponse->assertSeeText('200.00');
        $revenueResponse->assertDontSeeText('Deleted Loss Customer');

        $this->assertSoftDeleted('sales', ['id' => $deletedSale->id]);
        $this->assertDatabaseHas('sales', ['id' => $trashedSale->id]);
    }

    private function category(): Category
    {
        static $category;

        if (! $category instanceof Category || ! $category->exists || ! Category::query()->whereKey($category->id)->exists()) {
            $category = Category::query()->first() ?: Category::query()->create([
                'name' => 'Loss Category',
                'slug' => 'loss-category',
                'is_active' => true,
            ]);
        }

        return $category;
    }
}
