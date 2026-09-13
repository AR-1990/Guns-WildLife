<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('advance_adjustment_amount', 12, 2)->default(0)->after('advance_salary');
            $table->decimal('advance_waived_amount', 12, 2)->default(0)->after('advance_adjustment_amount');
            $table->decimal('loan_waived_amount', 12, 2)->default(0)->after('loan_adjustment_amount');
            $table->decimal('salary_waived_amount', 12, 2)->default(0)->after('net_salary');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'advance_adjustment_amount',
                'advance_waived_amount',
                'loan_waived_amount',
                'salary_waived_amount',
            ]);
        });
    }
};
