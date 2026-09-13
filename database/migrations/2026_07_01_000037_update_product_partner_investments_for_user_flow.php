<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->string('profit_type')->default('percent')->after('ownership_percentage');
            $table->decimal('profit_value', 12, 2)->default(0)->after('profit_type');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('product_partner_investments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['profit_type', 'profit_value']);
        });
    }
};
