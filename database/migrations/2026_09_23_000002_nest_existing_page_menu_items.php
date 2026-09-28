<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageParents = DB::table('pages')->pluck('parent_id', 'id');

        DB::table('menus')->pluck('id')->each(function ($menuId) use ($pageParents): void {
            $items = DB::table('menu_items')->where('menu_id', $menuId)->whereNotNull('page_id')->get(['id', 'page_id', 'parent_id']);
            $byPage = $items->keyBy('page_id');

            foreach ($items as $item) {
                if ($item->parent_id !== null) {
                    continue;
                }

                $parentPageId = $pageParents->get($item->page_id);
                while ($parentPageId && ! $byPage->has($parentPageId)) {
                    $parentPageId = $pageParents->get($parentPageId);
                }
                if ($parentPageId) {
                    DB::table('menu_items')->where('id', $item->id)->update(['parent_id' => $byPage->get($parentPageId)->id]);
                }
            }
        });
    }

    public function down(): void
    {
        // Existing manual parent links cannot be distinguished from migrated links.
    }
};
