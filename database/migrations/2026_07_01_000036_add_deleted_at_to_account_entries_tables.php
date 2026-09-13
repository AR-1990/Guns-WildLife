<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
