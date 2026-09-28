<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

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
}
