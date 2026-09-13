<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('advance_salary', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('loan_amount', 12, 2)->default(0)->after('advance_salary');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('net_salary');
            $table->decimal('balance_amount', 12, 2)->default(0)->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['advance_salary', 'loan_amount', 'paid_amount', 'balance_amount']);
        });
    }
};
