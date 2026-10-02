<?php

namespace App\Repositories\Contracts;

use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ResourceCategoryRepositoryInterface
{
    public function ordered(array $filters): Collection;

    public function lockAll(): Collection;

    public function reorder(array $ids): void;

    public function nextSortOrder(): int;

    public function lock(ResourceCategory $record): ResourceCategory;

    public function find(int $id): ResourceCategory;

    public function findByIds(array $ids): Collection;

    public function slugExists(string $slug, ?ResourceCategory $record): bool;

    public function create(array $data): ResourceCategory;

    public function update(ResourceCategory $record, array $data): ResourceCategory;

    public function delete(ResourceCategory $record): void;

    public function options(bool $activeOnly = false): array;

    public function inUse(ResourceCategory $record): bool;
}
