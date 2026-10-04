<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (DB::table('menus')->where('location', 'important_links')->lockForUpdate()->get() as $menu) {
                $items = DB::table('menu_items')->where('menu_id', $menu->id)->orderBy('sort_order')->orderBy('id')->get();
                $byId = $items->keyBy('id');
                $children = $items->groupBy('parent_id');
                $ordered = [];
                $append = function ($item) use (&$append, &$ordered, $children): void {
                    if (isset($ordered[$item->id])) {
                        return;
                    }
                    $ordered[$item->id] = $item->id;
                    foreach ($children->get($item->id, collect()) as $child) {
                        $append($child);
                    }
                };
                foreach ($items->filter(fn ($item) => ! $item->parent_id || ! $byId->has($item->parent_id)) as $root) {
                    $append($root);
                }
                foreach ($items as $item) {
                    $append($item);
                }
                foreach (array_values($ordered) as $sortOrder => $id) {
                    DB::table('menu_items')->where('id', $id)->update(['parent_id' => null, 'sort_order' => $sortOrder, 'updated_at' => now()]);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep saved links and their flat order; the removed hierarchy is not recreated.
    }
};
