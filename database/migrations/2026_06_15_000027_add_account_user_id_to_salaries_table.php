<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->foreignId('account_user_id')->nullable()->after('net_salary')->constrained('users')->nullOnDelete();
        });

        DB::table('salaries')
            ->whereNull('account_user_id')
            ->update([
                'account_user_id' => DB::raw('created_by'),
            ]);
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_user_id');
        });
    }
};
