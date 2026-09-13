<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->decimal('on_hold_component', 12, 2)->default(0)->after('profit_component');
            $table->decimal('investment_component', 12, 2)->default(0)->after('on_hold_component');
            $table->json('source_breakdown')->nullable()->after('available_after');
            $table->foreignId('investment_entry_id')
                ->nullable()
                ->after('source_breakdown')
                ->constrained('partner_investment_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partner_withdrawals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('investment_entry_id');
            $table->dropColumn(['on_hold_component', 'investment_component', 'source_breakdown']);
        });
    }
};
