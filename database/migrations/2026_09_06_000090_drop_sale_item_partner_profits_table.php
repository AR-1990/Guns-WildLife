<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sale_item_partner_profits');
    }

    public function down(): void
    {
        Schema::create('sale_item_partner_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('investment_amount', 14, 2)->default(0);
            $table->decimal('ownership_percentage', 10, 4)->default(0);
            $table->decimal('profit_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['sale_item_id', 'partner_id']);
            $table->index('partner_id');
        });
    }
};
