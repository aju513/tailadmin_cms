<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['header' => 'Main Menu', 'footer' => 'Footer Menu'] as $location => $name) {
            if (! DB::table('menus')->where('location', $location)->exists()) {
                DB::table('menus')->insert(['name' => $name, 'location' => $location, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Position records may have acquired menu items since installation.
    }
};
