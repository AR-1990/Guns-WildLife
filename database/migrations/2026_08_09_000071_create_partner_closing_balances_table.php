<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_closing_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_closing_id')->constrained('account_closings')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedInteger('sales_count')->default(0);

            $table->decimal('released_actual_amount', 14, 2)->default(0);
            $table->decimal('earned_profit_amount', 14, 2)->default(0);

            $table->decimal('expense_deduction_amount', 14, 2)->default(0);
            $table->decimal('salary_deduction_amount', 14, 2)->default(0);
            $table->decimal('total_deduction_amount', 14, 2)->default(0);

            $table->decimal('withdrawn_actual_amount', 14, 2)->default(0);
            $table->decimal('withdrawn_profit_amount', 14, 2)->default(0);
            $table->decimal('total_withdrawn_amount', 14, 2)->default(0);

            $table->decimal('net_payable_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();

            $table->unique(['account_closing_id', 'partner_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_closing_balances');
    }
};
