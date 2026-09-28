<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuService
{
    public function __construct(private readonly MenuRepositoryInterface $menus) {}

    public function locations()
    {
        return $this->menus->locations();
    }

    public function availablePages(Menu $menu)
    {
        return $this->menus->availablePages($menu);
    }

    /** @param array{menu_id: int|string, page_ids: array<int, int|string>} $data */
    public function assignPages(array $data, Authenticatable $actor): Menu
    {
        return DB::transaction(function () use ($data, $actor): Menu {
            $menu = $this->menus->find((int) $data['menu_id']);
            $assigned = $this->menus->assignPages($menu, $data['page_ids']);
            activity('content')->causedBy($actor)->event('menu-pages.assigned')->withProperties([
                'menu_id' => $menu->id,
                'page_ids' => array_map('intval', $data['page_ids']),
                'created_count' => $assigned,
            ])->log('Pages assigned to menu');

            return $menu;
        });
    }

    public function delete(MenuItem $item, Authenticatable $actor): void
    {
        DB::transaction(function () use ($item, $actor): void {
            $this->menus->deleteItem($item);
            activity('content')->causedBy($actor)->event('menu-item.deleted')->withProperties(['menu_item_id' => $item->id])->log('Menu item deleted');
        });
    }

    /** @param array{menu_id: int|string, parent_id?: int|string|null, menu_items: array<int, int|string>} $data */
    public function reorder(array $data, Authenticatable $actor): void
    {
        DB::transaction(function () use ($data, $actor): void {
            $menu = $this->menus->find((int) $data['menu_id']);
            $parentId = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;
            $itemIds = array_map('intval', $data['menu_items']);
            $items = $this->menus->itemsByIds($menu, $itemIds);

            if ($items->count() !== count($itemIds)) {
                throw ValidationException::withMessages(['menu_items' => 'One or more menu items do not belong to this menu.']);
            }
            if ($parentId !== null && $this->menus->itemsByIds($menu, [$parentId])->count() !== 1) {
                throw ValidationException::withMessages(['parent_id' => 'The parent item does not belong to this menu.']);
            }
            if ($items->contains(fn (MenuItem $item): bool => $item->parent_id !== $parentId)) {
                throw ValidationException::withMessages(['menu_items' => 'Menu items can only be reordered within the same parent.']);
            }

            $siblingIds = $this->menus->siblingIds($menu, $parentId);
            $expectedIds = $siblingIds;
            $submittedIds = $itemIds;
            sort($expectedIds);
            sort($submittedIds);
            if ($expectedIds !== $submittedIds) {
                throw ValidationException::withMessages(['menu_items' => 'The complete sibling group is required for reordering.']);
            }

            $this->menus->reorderItems($menu, $parentId, $itemIds);
            activity('content')->causedBy($actor)->event('menu-items.reordered')->withProperties([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'menu_item_ids' => $itemIds,
            ])->log('Menu items reordered');
        });
    }

    /** @param array{menu_id: int|string, menu_items: array<int, int|string>} $data */
    public function bulkDelete(array $data, Authenticatable $actor): void
    {
        DB::transaction(function () use ($data, $actor): void {
            $menu = $this->menus->find((int) $data['menu_id']);
            $itemIds = array_map('intval', $data['menu_items']);
            if ($this->menus->itemsByIds($menu, $itemIds)->count() !== count($itemIds)) {
                throw ValidationException::withMessages(['menu_items' => 'One or more menu items do not belong to this menu.']);
            }

            $this->menus->deleteItems($menu, $itemIds);
            activity('content')->causedBy($actor)->event('menu-items.deleted')->withProperties([
                'menu_id' => $menu->id,
                'menu_item_ids' => $itemIds,
                'deleted_count' => count($itemIds),
            ])->log('Menu items bulk deleted');
        });
    }
}
