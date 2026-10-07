<?php

namespace App\Repositories\Contracts;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PageRepositoryInterface
{
    public function lock(int $id): Page;

    public function find(int $id): Page;

    public function noticeSections(): Collection;

    public function publicNoticeSectionIds(Page $page): array;

    public function hasNoticeAssignments(array $pageIds): bool;

    public function paginateForIndex(array $filters): LengthAwarePaginator;

    public function orderedForIndex(array $filters): Collection;

    public function publicByPath(string $path): Page;

    public function create(array $data): Page;

    public function update(Page $page, array $data): Page;

    public function delete(Page $page): void;

    /** @param array<int, int|string> $ids */
    public function findByIds(array $ids): Collection;

    public function descendants(Page $page): Collection;

    public function allForParentSelect(?Page $except = null): Collection;

    /** @param array<int, int|string> $ids */
    public function reorder(array $ids): void;
}
