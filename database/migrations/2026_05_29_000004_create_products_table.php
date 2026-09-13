<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('product_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ownership_type')->default('company');
            $table->string('sub_category')->nullable();
            $table->string('unit');
            $table->string('secondary_uom')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->unsignedInteger('min_stock_level')->nullable();
            $table->unsignedInteger('max_stock_level')->nullable();
            $table->boolean('batch_expiry')->default(false);
            $table->string('status')->default('active');
            $table->string('commission_type')->default('none');
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('commission_fixed', 12, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_serialized')->default(false);
            $table->boolean('requires_license')->default(false);
            $table->string('weapon_number')->nullable();
            $table->string('license_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
