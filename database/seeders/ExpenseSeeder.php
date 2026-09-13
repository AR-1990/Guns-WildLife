<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();
        $references = ['EXP-2026-001', 'EXP-2026-002', 'EXP-2026-003'];

        Expense::withTrashed()
            ->whereIn('reference', $references)
            ->forceDelete();

        collect([
            [
                'expense_date' => '2026-08-06',
                'title' => 'Store Rent',
                'category' => 'Rent',
                'amount' => 35000,
                'account_user_id' => null,
                'payment_method' => 'bank',
                'reference' => 'EXP-2026-001',
                'notes' => json_encode([
                    'user_notes' => 'Monthly showroom rent for the admin-managed account.',
                    'deduction_from' => 'admin',
                ]),
                'created_by' => $admin->id,
            ],
            [
                'expense_date' => '2026-08-14',
                'title' => 'Electricity & Backup Power',
                'category' => 'Utility',
                'amount' => 12800,
                'account_user_id' => null,
                'payment_method' => 'cash',
                'reference' => 'EXP-2026-002',
                'notes' => json_encode([
                    'user_notes' => 'Monthly utility bill paid from admin account.',
                    'deduction_from' => 'admin',
                ]),
                'created_by' => $admin->id,
            ],
            [
                'expense_date' => '2026-08-21',
                'title' => 'Delivery and Local Transport',
                'category' => 'Transport',
                'amount' => 4200,
                'account_user_id' => null,
                'payment_method' => 'online',
                'reference' => 'EXP-2026-003',
                'notes' => json_encode([
                    'user_notes' => 'Local transport for delivered orders.',
                    'deduction_from' => 'admin',
                ]),
                'created_by' => $admin->id,
            ],
        ])->each(fn (array $expense) => Expense::query()->create($expense));
    }
}
