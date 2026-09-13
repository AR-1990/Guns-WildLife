<?php

namespace Database\Seeders;

use App\Models\PartnerInvestmentEntry;
use App\Models\PartnerInvestmentEntryItem;
use App\Models\Product;
use App\Models\User;
use App\Support\PartnerInvestmentSyncService;
use Illuminate\Database\Seeder;

class PartnerInvestmentSeeder extends Seeder
{
    public function run(): void
    {
        $partnerOne = User::query()->where('email', 'partner@guns.local')->firstOrFail();
        $partnerTwo = User::query()->where('email', 'partner2@guns.local')->firstOrFail();
        $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();

        PartnerInvestmentEntry::withTrashed()
            ->whereIn('notes', [
                'Seeded investment batch for Partner One.',
                'Seeded investment batch for Partner Two.',
            ])
            ->forceDelete();

        $this->createEntry($partnerOne->id, $admin->id, '2026-08-01', [
            ['product' => 'CQ-A Tactical Rifle', 'amount' => 220000],
            ['product' => 'Hunter Pro Shotgun', 'amount' => 110000],
        ], 'Seeded investment batch for Partner One.');

        $this->createEntry($partnerTwo->id, $admin->id, '2026-08-05', [
            ['product' => 'CQ-A Tactical Rifle', 'amount' => 110000],
            ['product' => 'Hunter Pro Shotgun', 'amount' => 55000],
        ], 'Seeded investment batch for Partner Two.');

        PartnerInvestmentSyncService::rebuildPartnerAssignments($partnerOne->id);
        PartnerInvestmentSyncService::rebuildPartnerAssignments($partnerTwo->id);
    }

    private function createEntry(int $partnerId, int $createdBy, string $investmentDate, array $products, string $notes): void
    {
        $totalAmount = round(collect($products)->sum('amount'), 2);

        $entry = PartnerInvestmentEntry::query()->create([
            'partner_id' => $partnerId,
            'investment_date' => $investmentDate,
            'total_amount' => $totalAmount,
            'created_by' => $createdBy,
            'notes' => $notes,
        ]);

        foreach ($products as $productData) {
            $product = Product::query()->where('name', $productData['product'])->firstOrFail();

            PartnerInvestmentEntryItem::query()->create([
                'partner_investment_entry_id' => $entry->id,
                'product_id' => $product->id,
                'amount' => round((float) $productData['amount'], 2),
                'on_hold_amount' => 0,
                'wallet_used_amount' => 0,
                'profit_type' => 'percent',
                'profit_value' => 0,
            ]);
        }
    }
}
