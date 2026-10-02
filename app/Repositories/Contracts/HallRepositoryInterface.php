<?php

namespace App\Repositories\Contracts;

use App\Models\Hall;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HallRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator;

    public function details(Hall $hall): Hall;

    public function lock(Hall $hall): Hall;

    public function create(array $data): Hall;

    public function update(Hall $hall, array $data): Hall;

    public function delete(Hall $hall): void;

    public function slugExists(string $slug, ?Hall $except = null): bool;

    public function galleryCount(Hall $hall): int;

    public function removeGalleryImages(Hall $hall, array $ids): void;

    public function addGalleryImage(Hall $hall, int $mediaId): void;

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection;
}
