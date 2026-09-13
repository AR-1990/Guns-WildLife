<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_closings', function (Blueprint $table) {
            $table->id();
            $table->string('period_name', 255);
            $table->date('closing_date')->unique();
            $table->text('notes')->nullable();
            $table->unsignedInteger('total_sales_count')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->unsignedInteger('expense_records')->default(0);
            $table->decimal('total_expenses', 14, 2)->default(0);
            $table->unsignedInteger('salary_records')->default(0);
            $table->decimal('total_salaries', 14, 2)->default(0);
            $table->unsignedInteger('withdrawal_records')->default(0);
            $table->decimal('total_withdrawals', 14, 2)->default(0);
            $table->decimal('total_withdrawn_actual', 14, 2)->default(0);
            $table->decimal('total_withdrawn_profit', 14, 2)->default(0);
            $table->decimal('total_partner_profit', 14, 2)->default(0);
            $table->decimal('total_partner_deductions', 14, 2)->default(0);
            $table->decimal('total_partner_expense_deductions', 14, 2)->default(0);
            $table->decimal('total_partner_salary_deductions', 14, 2)->default(0);
            $table->decimal('total_admin_profit', 14, 2)->default(0);
            $table->decimal('total_released_actual', 14, 2)->default(0);
            $table->decimal('total_net_payable', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_closings');
    }
};
