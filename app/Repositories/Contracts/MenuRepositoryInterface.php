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

    public function createItem(array $data): MenuItem;

    public function updateItem(MenuItem $item, array $data): MenuItem;

    public function deleteItem(MenuItem $item): void;
}
