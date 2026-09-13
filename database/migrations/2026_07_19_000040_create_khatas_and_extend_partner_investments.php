<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khatas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('partner_investment_entries', function (Blueprint $table) {
            $table->foreignId('khata_id')->nullable()->after('partner_id')->constrained()->nullOnDelete();
        });

        Schema::table('partner_investment_entry_items', function (Blueprint $table) {
            $table->decimal('on_hold_amount', 12, 2)->default(0)->after('amount');
        });

        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->decimal('on_hold_amount', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->dropColumn('on_hold_amount');
        });

        Schema::table('partner_investment_entry_items', function (Blueprint $table) {
            $table->dropColumn('on_hold_amount');
        });

        Schema::table('partner_investment_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('khata_id');
        });

        Schema::dropIfExists('khatas');
    }
};
