<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }

            if (Schema::hasColumn('products', 'license_number')) {
                $table->dropColumn('license_number');
            }
        });

        Schema::table('product_units', function (Blueprint $table) {
            if (Schema::hasColumn('product_units', 'license_number')) {
                $table->dropColumn('license_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->after('id');
            }

            if (! Schema::hasColumn('products', 'license_number')) {
                $table->string('license_number')->nullable()->after('weapon_number');
            }
        });

        Schema::table('product_units', function (Blueprint $table) {
            if (! Schema::hasColumn('product_units', 'license_number')) {
                $table->string('license_number')->nullable()->after('weapon_number');
            }
        });
    }
};
