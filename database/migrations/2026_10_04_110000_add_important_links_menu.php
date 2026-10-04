<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site Settings already accepts URLs up to 1,000 characters.
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->text('external_url')->nullable()->change();
        });

        DB::transaction(function (): void {
            // Respect an existing position, including an intentionally empty one.
            if (DB::table('menus')->where('location', 'important_links')->exists()) {
                return;
            }

            $saved = DB::table('site_settings')->where('key', 'important_links')->value('value');
            $links = $saved === null ? config('frontend.important_links', []) : json_decode($saved ?: '[]', true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($links)) {
                throw new UnexpectedValueException('Important Links must contain a JSON array before migration.');
            }

            $menuId = DB::table('menus')->insertGetId([
                'name' => 'Important Links Menu',
                'location' => 'important_links',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (array_values($links) as $index => $link) {
                if (! is_array($link) || ! is_string($link['label'] ?? null) || ! is_string($link['url'] ?? null)) {
                    throw new UnexpectedValueException('Each Important Link must have a label and URL before migration.');
                }

                DB::table('menu_items')->insert([
                    'menu_id' => $menuId,
                    'label' => $link['label'],
                    'external_url' => $link['url'],
                    'sort_order' => $index,
                    'is_visible' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Retain imported/edited links and URL capacity on rollback. The original
        // Site Settings row is also retained as a record of the imported list.
    }
};
