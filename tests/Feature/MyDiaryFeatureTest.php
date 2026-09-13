<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiaryContact;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyDiaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_salesman_can_open_my_diary_and_manual_contact_can_be_added(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Diary Admin',
        ]);
        $salesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
            'name' => 'Diary Salesman',
        ]);

        DiaryContact::query()->create([
            'full_name' => 'Manual Contact',
            'phone' => '03001234567',
            'source' => 'manual',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.diary.index'))
            ->assertOk()
            ->assertSeeText('Manual Contact');

        $this->actingAs($salesman)
            ->get(route('admin.diary.index'))
            ->assertOk()
            ->assertSeeText('Manual Contact');

        $this->actingAs($salesman)
            ->post(route('admin.diary.store'), [
                'full_name' => 'Salesman Contact',
                'phone' => '03111222333',
                'email' => 'salesman@example.com',
                'address' => 'Lahore',
                'notes' => 'Created from diary form',
            ])
            ->assertRedirect(route('admin.diary.index'));

        $this->assertDatabaseHas('diary_contacts', [
            'full_name' => 'Salesman Contact',
            'phone' => '03111222333',
            'created_by' => $salesman->id,
            'source' => 'manual',
        ]);
    }

    public function test_sale_creates_diary_contact_when_customer_name_and_phone_exist(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $product = Product::query()->create([
            'product_code' => 'DIARY-001',
            'name' => 'Diary Test Product',
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
                'customer_name' => 'Sale Customer',
                'customer_phone' => '03211234567',
                'sale_date' => '2026-09-10',
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

        $sale = Sale::query()->firstOrFail();

        $this->assertDatabaseHas('diary_contacts', [
            'sale_id' => $sale->id,
            'full_name' => 'Sale Customer',
            'phone' => '03211234567',
            'source' => 'sale',
        ]);
    }

    private function category(): Category
    {
        static $category;

        if (! $category instanceof Category || ! $category->exists || ! Category::query()->whereKey($category->id)->exists()) {
            $category = Category::query()->first() ?: Category::query()->create([
                'name' => 'Diary Category',
                'slug' => 'diary-category',
                'is_active' => true,
            ]);
        }

        return $category;
    }
}
