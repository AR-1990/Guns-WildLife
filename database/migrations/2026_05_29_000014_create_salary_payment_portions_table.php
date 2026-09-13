<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payment_portions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('portion_no');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('paid_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['salary_id', 'portion_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payment_portions');
    }
};
