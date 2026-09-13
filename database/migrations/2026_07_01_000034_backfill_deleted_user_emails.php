<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'email', 'deleted_at'])
            ->each(function ($user) {
                if (str_starts_with((string) $user->email, 'deleted-user-')) {
                    return;
                }

                $timestamp = now()->format('YmdHis');

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'email' => 'deleted-user-' . $user->id . '-' . $timestamp . '@deleted.local',
                    ]);
            });
    }

    public function down(): void
    {
    }
};
