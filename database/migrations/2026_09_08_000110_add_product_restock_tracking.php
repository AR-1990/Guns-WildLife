<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_restock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('remaining_quantity');
            $table->decimal('unit_purchase_price', 14, 2)->default(0);
            $table->date('restocked_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_opening')->default(false);
            $table->timestamps();
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->foreignId('product_restock_entry_id')->nullable()->after('product_id')->constrained('product_restock_entries')->nullOnDelete();
            $table->decimal('unit_purchase_price', 14, 2)->default(0)->after('status');
        });

        Schema::create('sale_item_stock_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_restock_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_purchase_price', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->timestamps();
        });

        $products = DB::table('products')
            ->select(['id', 'purchase_price', 'stock_quantity', 'is_serialized', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get();

        foreach ($products as $product) {
            $restockedAt = $product->created_at
                ? \Illuminate\Support\Carbon::parse($product->created_at)->toDateString()
                : now()->toDateString();

            if ((bool) $product->is_serialized) {
                $units = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->orderBy('id')
                    ->get(['id', 'status']);

                if ($units->isEmpty()) {
                    continue;
                }

                $entryId = DB::table('product_restock_entries')->insertGetId([
                    'product_id' => $product->id,
                    'quantity' => $units->count(),
                    'remaining_quantity' => $units->where('status', 'available')->count(),
                    'unit_purchase_price' => (float) $product->purchase_price,
                    'restocked_at' => $restockedAt,
                    'notes' => 'Opening stock migrated from existing product record.',
                    'created_by' => null,
                    'is_opening' => true,
                    'created_at' => $product->created_at ?: now(),
                    'updated_at' => $product->updated_at ?: now(),
                ]);

                DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->update([
                        'product_restock_entry_id' => $entryId,
                        'unit_purchase_price' => (float) $product->purchase_price,
                    ]);

                continue;
            }

            if ((int) $product->stock_quantity <= 0) {
                continue;
            }

            DB::table('product_restock_entries')->insert([
                'product_id' => $product->id,
                'quantity' => (int) $product->stock_quantity,
                'remaining_quantity' => (int) $product->stock_quantity,
                'unit_purchase_price' => (float) $product->purchase_price,
                'restocked_at' => $restockedAt,
                'notes' => 'Opening stock migrated from existing product record.',
                'created_by' => null,
                'is_opening' => true,
                'created_at' => $product->created_at ?: now(),
                'updated_at' => $product->updated_at ?: now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_stock_allocations');

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_restock_entry_id');
            $table->dropColumn('unit_purchase_price');
        });

        Schema::dropIfExists('product_restock_entries');
    }
};
