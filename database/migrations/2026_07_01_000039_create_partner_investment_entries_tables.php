<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('partner_investment_entry_items');
        Schema::dropIfExists('partner_investment_entries');

        Schema::create('partner_investment_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users', indexName: 'pie_partner_fk')->cascadeOnDelete();
            $table->date('investment_date');
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('created_by')->nullable()->constrained('users', indexName: 'pie_created_by_fk')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partner_investment_entry_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_investment_entry_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('amount', 12, 2);
            $table->string('profit_type')->default('percent');
            $table->decimal('profit_value', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('partner_investment_entry_id', 'piei_entry_fk')
                ->references('id')
                ->on('partner_investment_entries')
                ->cascadeOnDelete();
            $table->foreign('product_id', 'piei_product_fk')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        $groupedInvestments = DB::table('product_partner_investments')
            ->whereNull('deleted_at')
            ->orderBy('partner_id')
            ->orderBy('id')
            ->get()
            ->groupBy('partner_id');

        foreach ($groupedInvestments as $partnerId => $rows) {
            $firstRow = $rows->first();
            $entryId = DB::table('partner_investment_entries')->insertGetId([
                'partner_id' => $partnerId,
                'investment_date' => $firstRow->investment_date ?: substr((string) $firstRow->created_at, 0, 10),
                'total_amount' => round((float) $rows->sum('amount'), 2),
                'created_by' => 1,
                'notes' => 'Imported existing partner investment',
                'created_at' => $firstRow->created_at,
                'updated_at' => $firstRow->updated_at,
            ]);

            foreach ($rows as $row) {
                DB::table('partner_investment_entry_items')->insert([
                    'partner_investment_entry_id' => $entryId,
                    'product_id' => $row->product_id,
                    'amount' => $row->amount,
                    'profit_type' => $row->profit_type ?: 'percent',
                    'profit_value' => $row->profit_value ?? 0,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_investment_entry_items');
        Schema::dropIfExists('partner_investment_entries');
    }
};
