<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('phone');
            $table->string('email');
            $table->text('address');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            'company_name' => 'Guns & Wildlife',
            'phone' => '+92 300 0000000',
            'email' => 'info@gunswildlife.pk',
            'address' => 'Main Boulevard, Lahore, Pakistan',
            'logo_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
