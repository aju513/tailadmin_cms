<?php

namespace App\Repositories\Eloquent;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class MenuRepository implements MenuRepositoryInterface
{
    public function forLocation(string $location): ?Menu
    {
        return Menu::query()->where('location', $location)->with(['items' => fn ($query) => $query->where('is_visible', true)->whereNull('parent_id')->with(['children' => fn ($query) => $query->where('is_visible', true)->with('page')])->with('page')])->first();
    }

    public function locations(): Collection
    {
        return Menu::query()->with(['items' => fn ($query) => $query->with('page', 'parent')])->orderBy('name')->get();
    }

    public function find(int $id): Menu
    {
        return Menu::query()->findOrFail($id);
    }

    public function availablePages(Menu $menu): BaseCollection
    {
        $assignedPageIds = $menu->items()->whereNotNull('page_id')->pluck('page_id');

        return Page::query()
            ->whereNotIn('id', $assignedPageIds)
            ->orderBy('path')
            ->get();
    }

    public function assignPages(Menu $menu, array $pageIds): int
    {
        $pages = Page::query()->whereIn('id', array_map('intval', $pageIds))->orderBy('path')->get();
        $existingPageIds = $menu->items()->whereIn('page_id', $pages->pluck('id'))->pluck('page_id')->all();
        $nextSortOrder = (int) $menu->items()->max('sort_order') + 1;
        $created = 0;

        foreach ($pages as $page) {
            if (in_array($page->id, $existingPageIds, true)) {
                continue;
            }

            MenuItem::query()->create([
                'menu_id' => $menu->id,
                'label' => $page->title,
                'page_id' => $page->id,
                'sort_order' => $nextSortOrder++,
                'is_visible' => true,
            ]);
            $created++;
        }

        return $created;
    }

    public function createItem(array $data): MenuItem
    {
        return MenuItem::query()->create($data);
    }

    public function updateItem(MenuItem $item, array $data): MenuItem
    {
        $item->update($data);

        return $item->refresh();
    }

    public function deleteItem(MenuItem $item): void
    {
        $item->delete();
    }
}
