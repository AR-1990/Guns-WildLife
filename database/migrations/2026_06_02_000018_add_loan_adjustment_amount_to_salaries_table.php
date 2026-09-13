<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('loan_adjustment_amount', 12, 2)->default(0)->after('loan_amount');
        });

        DB::table('salaries')
            ->where('loan_amount', '>', 0)
            ->where('status', '!=', 'loan')
            ->update([
                'loan_adjustment_amount' => DB::raw('loan_amount'),
                'loan_amount' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('loan_adjustment_amount');
        });
    }
};
