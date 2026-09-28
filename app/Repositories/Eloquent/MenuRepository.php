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
        $menu = Menu::query()->where('location', $location)->with(['items' => fn ($query) => $query->where('is_visible', true)->with('page')])->first();
        if (! $menu) {
            return null;
        }

        $items = $menu->items;
        $byId = $items->keyBy('id');
        foreach ($items as $item) {
            $item->setRelation('children', new Collection);
        }
        $roots = new Collection;
        foreach ($items as $item) {
            $parent = $byId->get($item->parent_id);
            if ($parent) {
                $parent->children->push($item);
            } else {
                $roots->push($item);
            }
        }
        $menu->setRelation('items', $roots);

        return $menu;
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
        return Page::query()
            ->orderBy('path')
            ->get();
    }

    public function assignPages(Menu $menu, array $pageIds): int
    {
        $pages = Page::query()->whereIn('id', array_map('intval', $pageIds))->orderBy('path')->get();
        $items = $menu->items()->whereNotNull('page_id')->get()->keyBy('page_id');
        $nextSortOrder = (int) $menu->items()->max('sort_order') + 1;
        $created = 0;

        foreach ($pages as $page) {
            if ($items->has($page->id)) {
                continue;
            }

            $item = MenuItem::query()->create([
                'menu_id' => $menu->id,
                'label' => $page->title,
                'page_id' => $page->id,
                'sort_order' => $nextSortOrder++,
                'is_visible' => true,
            ]);
            $items->put($page->id, $item);
            $created++;
        }

        // Rebuild links after every assignment so a parent added later adopts its
        // previously assigned children. The nearest assigned ancestor wins.
        $pagesById = Page::query()->get(['id', 'parent_id'])->keyBy('id');
        foreach ($items as $pageId => $item) {
            $page = $pagesById->get($pageId);
            $parentId = $page?->parent_id;
            while ($parentId && ! $items->has($parentId)) {
                $parentId = $pagesById->get($parentId)?->parent_id;
            }
            $desiredParentId = $parentId ? $items->get($parentId)->id : null;
            if ($item->parent_id !== $desiredParentId) {
                $item->update(['parent_id' => $desiredParentId]);
            }
        }

        return $created;
    }

    public function deleteItem(MenuItem $item): void
    {
        $menu = $item->menu;
        $item->children()->update(['parent_id' => $item->parent_id]);
        $item->delete();
        $this->assignPages($menu, []);
    }
}
