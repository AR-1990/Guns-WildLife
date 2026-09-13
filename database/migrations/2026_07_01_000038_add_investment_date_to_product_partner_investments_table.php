<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->date('investment_date')->nullable()->after('amount');
        });

        DB::table('product_partner_investments')
            ->whereNull('investment_date')
            ->update([
                'investment_date' => DB::raw('DATE(created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->dropColumn('investment_date');
        });
    }
};
