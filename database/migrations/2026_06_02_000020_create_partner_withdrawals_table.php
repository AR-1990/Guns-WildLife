<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('withdrawal_date');
            $table->string('entry_type', 30)->default('withdraw');
            $table->decimal('available_before', 12, 2)->default(0);
            $table->decimal('available_after', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_withdrawals');
    }
};
