<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'name' => 'System Admin',
                'email' => 'admin@guns.local',
                'role' => User::ROLE_ADMIN,
                'company_name' => 'Guns & Wildlife',
                'password' => Hash::make('Admin@123'),
            ],
            [
                'name' => 'Sales Man',
                'email' => 'salesman@guns.local',
                'role' => User::ROLE_SALESMAN,
                'company_name' => 'Guns & Wildlife Sales',
                'password' => Hash::make('Sales@123'),
            ],
            [
                'name' => 'Partner One',
                'email' => 'partner@guns.local',
                'role' => User::ROLE_PARTNER,
                'company_name' => 'Frontier Trading',
                'password' => Hash::make('Partner@123'),
            ],
            [
                'name' => 'Partner Two',
                'email' => 'partner2@guns.local',
                'role' => User::ROLE_PARTNER,
                'company_name' => 'Falcon Supplies',
                'password' => Hash::make('Partner@123'),
            ],
        ])->each(function (array $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        });
    }
}
