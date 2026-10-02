<?php

namespace App\Repositories\Contracts;

use App\Models\NoticeCategory;
use Illuminate\Database\Eloquent\Collection;

interface NoticeCategoryRepositoryInterface
{
    public function ordered(array $filters): Collection;

    public function lockAll(): Collection;

    public function reorder(array $ids): void;

    public function nextSortOrder(): int;

    public function lock(NoticeCategory $record): NoticeCategory;

    public function find(int $id): NoticeCategory;

    public function findByIds(array $ids): Collection;

    public function slugExists(string $slug, ?NoticeCategory $record): bool;

    public function create(array $data): NoticeCategory;

    public function update(NoticeCategory $record, array $data): NoticeCategory;

    public function delete(NoticeCategory $record): void;

    public function options(bool $activeOnly = false): array;

    public function inUse(NoticeCategory $record): bool;
}
