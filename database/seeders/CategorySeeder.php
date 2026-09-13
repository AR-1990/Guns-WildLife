<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Firearms',
            'Ammunition',
            'Gun Bags',
            'Gun Covers',
            'Rifle Cases',
            'Pistol Cases',
            'Holsters',
            'Magazines & Clips',
            'Cleaning Kits',
            'Optics & Scopes',
            'Tactical Belts',
            'Safety Gear',
            'Slings',
            'Gun Parts & Accessories',
        ])->each(fn (string $name) => Category::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true],
        ));
    }
}
