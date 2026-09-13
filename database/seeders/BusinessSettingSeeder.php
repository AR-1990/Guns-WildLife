<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        BusinessSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Guns & Wildlife',
                'phone' => '+92 300 0000000',
                'email' => 'admin@guns.local',
                'address' => 'Main Mall Road, Lahore',
                'logo_path' => null,
            ]
        );
    }
}
