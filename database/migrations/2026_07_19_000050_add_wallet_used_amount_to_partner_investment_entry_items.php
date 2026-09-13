<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_investment_entry_items', function (Blueprint $table) {
            $table->decimal('wallet_used_amount', 12, 2)->default(0)->after('on_hold_amount');
        });
    }

    public function down(): void
    {
        Schema::table('partner_investment_entry_items', function (Blueprint $table) {
            $table->dropColumn('wallet_used_amount');
        });
    }
};
