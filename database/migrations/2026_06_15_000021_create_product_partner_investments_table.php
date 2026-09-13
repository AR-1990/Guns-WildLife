<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_partner_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('ownership_percentage', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_partner_investments');
    }
};
