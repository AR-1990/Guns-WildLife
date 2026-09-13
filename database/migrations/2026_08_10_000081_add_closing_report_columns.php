<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_closings', function (Blueprint $table) {
            $table->decimal('total_subtotal', 14, 2)->default(0)->after('total_revenue');
            $table->decimal('total_discount', 14, 2)->default(0)->after('total_subtotal');
            $table->decimal('total_shipping', 14, 2)->default(0)->after('total_discount');
            $table->decimal('total_tax', 14, 2)->default(0)->after('total_shipping');
            $table->decimal('total_cogs', 14, 2)->default(0)->after('total_tax');
            $table->decimal('total_gross_profit', 14, 2)->default(0)->after('total_cogs');
            $table->decimal('gross_margin_percent', 8, 4)->default(0)->after('total_gross_profit');
            $table->decimal('total_operating_cost', 14, 2)->default(0)->after('total_salaries');
            $table->decimal('total_net_operating_income', 14, 2)->default(0)->after('total_admin_profit');
            $table->decimal('cashbook_balance', 14, 2)->default(0)->after('total_net_operating_income');
            $table->decimal('total_partner_invested_actual', 14, 2)->default(0)->after('total_released_actual');
            $table->decimal('total_partner_unified_net_payable', 14, 2)->default(0)->after('total_net_payable');
        });

        Schema::table('partner_closing_balances', function (Blueprint $table) {
            $table->decimal('total_invested_actual', 14, 2)->default(0)->after('sales_count');
            $table->decimal('unified_net_payable_amount', 14, 2)->default(0)->after('net_payable_amount');
        });
    }

    public function down(): void
    {
        Schema::table('account_closings', function (Blueprint $table) {
            $columns = [
                'total_subtotal','total_discount','total_shipping','total_tax',
                'total_cogs','total_gross_profit','gross_margin_percent',
                'total_operating_cost','total_net_operating_income','cashbook_balance',
                'total_partner_invested_actual','total_partner_unified_net_payable',
            ];
            foreach ($columns as $c) {
                if (Schema::hasColumn('account_closings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('partner_closing_balances', function (Blueprint $table) {
            $columns = ['total_invested_actual','unified_net_payable_amount'];
            foreach ($columns as $c) {
                if (Schema::hasColumn('partner_closing_balances', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
