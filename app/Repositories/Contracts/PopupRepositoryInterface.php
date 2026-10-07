<?php

namespace App\Repositories\Contracts;

use App\Models\Popup;
use Illuminate\Database\Eloquent\Collection;

interface PopupRepositoryInterface
{
    public function listing(array $filters): Collection;

    public function published(): Collection;

    public function create(array $data): Popup;

    public function update(Popup $slide, array $data): Popup;

    public function delete(Popup $slide): void;

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection;

    public function details(Popup $slide): Popup;

    public function lock(Popup $slide): Popup;

    public function nextSortOrder(): int;

    public function lockOrderedIds(): array;

    public function reorder(array $ids): void;
}
