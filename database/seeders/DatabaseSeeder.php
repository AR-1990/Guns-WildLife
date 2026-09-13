<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BusinessSettingSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            SerializedGunSeeder::class,
            PartnerInvestmentSeeder::class,
            SalesSeeder::class,
            ExpenseSeeder::class,
            SalarySeeder::class,
            PartnerWithdrawalSeeder::class,
            AccountClosingSeeder::class,
            ContactRequestSeeder::class,
        ]);
    }
}
