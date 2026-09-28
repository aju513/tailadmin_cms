<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menus')->where('location', 'header')->whereIn('name', ['Header Navigation', 'Header Menu'])->update(['name' => 'Main Menu']);
        DB::table('menus')->where('location', 'footer')->whereIn('name', ['Footer Navigation'])->update(['name' => 'Footer Menu']);
    }

    public function down(): void
    {
        // Existing and custom names cannot be distinguished after the update.
    }
};
