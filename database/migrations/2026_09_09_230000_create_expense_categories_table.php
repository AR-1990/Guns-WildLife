<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        $rows = $this->categoryNames()
            ->map(function (string $name) use ($now) {
                static $usedSlugs = [];

                $baseSlug = Str::slug($name) ?: 'expense-category';
                $slug = $baseSlug;
                $counter = 2;

                while (in_array($slug, $usedSlugs, true)) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $usedSlugs[] = $slug;

                return [
                    'name' => $name,
                    'slug' => $slug,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        if ($rows !== []) {
            DB::table('expense_categories')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }

    private function categoryNames(): Collection
    {
        return collect([
            'Utility',
            'Fuel',
            'Rent',
            'Zakat',
            'Maintenance',
            'Office Supplies',
            'Transport',
            'Miscellaneous',
        ])->merge(
            DB::table('expenses')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->pluck('category')
        )
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name))
            ->values();
    }
};
