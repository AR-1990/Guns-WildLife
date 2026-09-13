<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_closings')) {
            Schema::table('account_closings', function (Blueprint $table) {
                if (Schema::hasColumn('account_closings', 'zakat_records')) {
                    $table->dropColumn('zakat_records');
                }

                if (Schema::hasColumn('account_closings', 'total_zakat')) {
                    $table->dropColumn('total_zakat');
                }
            });
        }

        Schema::dropIfExists('zakat_payment_portions');
        Schema::dropIfExists('zakat_payments');
    }

    public function down(): void
    {
        if (! Schema::hasTable('zakat_payments')) {
            Schema::create('zakat_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->date('range_start')->nullable();
                $table->date('range_end')->nullable();
                $table->string('period_type')->default('yearly');
                $table->unsignedSmallInteger('period_year');
                $table->unsignedTinyInteger('period_month')->nullable();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('zakat_amount', 12, 2)->default(0);
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('balance_amount', 12, 2)->default(0);
                $table->string('status')->default('unpaid');
                $table->date('paid_date')->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('zakat_payment_portions')) {
            Schema::create('zakat_payment_portions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('zakat_payment_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('portion_no');
                $table->decimal('amount', 12, 2)->default(0);
                $table->date('paid_date');
                $table->text('notes')->nullable();
                $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
                $table->json('expense_ids')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['zakat_payment_id', 'portion_no']);
            });
        }

        if (Schema::hasTable('account_closings')) {
            Schema::table('account_closings', function (Blueprint $table) {
                if (! Schema::hasColumn('account_closings', 'zakat_records')) {
                    $table->unsignedInteger('zakat_records')->default(0)->after('total_salaries');
                }

                if (! Schema::hasColumn('account_closings', 'total_zakat')) {
                    $table->decimal('total_zakat', 14, 2)->default(0)->after('zakat_records');
                }
            });
        }
    }
};
