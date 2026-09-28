<?php

namespace App\Repositories\Contracts;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

interface MenuRepositoryInterface
{
    public function forLocation(string $location): ?Menu;

    public function locations(): Collection;

    public function find(int $id): Menu;

    public function availablePages(Menu $menu): BaseCollection;

    /** @param array<int, int|string> $pageIds */
    public function assignPages(Menu $menu, array $pageIds): int;

    /** @param array<int, int> $itemIds */
    public function itemsByIds(Menu $menu, array $itemIds): Collection;

    /** @return array<int, int> */
    public function siblingIds(Menu $menu, ?int $parentId): array;

    /** @param array<int, int> $itemIds */
    public function reorderItems(Menu $menu, ?int $parentId, array $itemIds): void;

    /** @param array<int, int> $itemIds */
    public function deleteItems(Menu $menu, array $itemIds): void;

    public function deleteItem(MenuItem $item): void;
}
