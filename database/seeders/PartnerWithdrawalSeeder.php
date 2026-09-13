<?php

namespace Database\Seeders;

use App\Models\PartnerWithdrawal;
use App\Models\User;
use App\Support\PartnerAccountSummary;
use Illuminate\Database\Seeder;

class PartnerWithdrawalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();
        $partners = User::query()
            ->whereIn('email', ['partner@guns.local', 'partner2@guns.local'])
            ->orderBy('id')
            ->get();

        PartnerWithdrawal::withTrashed()
            ->whereIn('notes', [
                'Seeded partner payout for Partner One.',
                'Seeded partner payout for Partner Two.',
            ])
            ->forceDelete();

        foreach ($partners as $index => $partner) {
            $availableBefore = round(PartnerAccountSummary::availableActual($partner->id), 2);
            $amount = min($index === 0 ? 60000 : 25000, $availableBefore);

            if ($amount <= 0) {
                continue;
            }

            PartnerWithdrawal::query()->create([
                'partner_id' => $partner->id,
                'amount' => $amount,
                'actual_component' => $amount,
                'profit_component' => 0,
                'on_hold_component' => 0,
                'investment_component' => 0,
                'withdrawal_date' => $index === 0 ? '2026-08-28' : '2026-08-29',
                'entry_type' => 'withdraw',
                'available_before' => $availableBefore,
                'available_after' => max(0, round($availableBefore - $amount, 2)),
                'source_breakdown' => [
                    'direct_payout' => $amount,
                    'payment_method' => $index === 0 ? 'bank' : 'cash',
                ],
                'investment_entry_id' => null,
                'notes' => $index === 0
                    ? 'Seeded partner payout for Partner One.'
                    : 'Seeded partner payout for Partner Two.',
                'created_by' => $admin->id,
            ]);
        }
    }
}
