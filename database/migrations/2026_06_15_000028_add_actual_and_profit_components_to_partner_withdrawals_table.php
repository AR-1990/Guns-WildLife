<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->decimal('actual_component', 12, 2)->default(0)->after('amount');
            $table->decimal('profit_component', 12, 2)->default(0)->after('actual_component');
        });

        DB::table('partner_withdrawals')
            ->whereNull('profit_component')
            ->orWhere('profit_component', 0)
            ->update([
                'profit_component' => DB::raw('amount'),
                'actual_component' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['actual_component', 'profit_component']);
        });
    }
};
