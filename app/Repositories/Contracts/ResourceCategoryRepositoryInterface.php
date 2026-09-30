<?php

namespace App\Repositories\Contracts;

use App\Models\ResourceCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourceCategoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function lock(ResourceCategory $record): ResourceCategory;
    public function find(int $id): ResourceCategory;
    public function slugExists(string $slug, ?ResourceCategory $record): bool;
    public function create(array $data): ResourceCategory;
    public function update(ResourceCategory $record, array $data): ResourceCategory;
    public function delete(ResourceCategory $record): void;

    public function options(): array;
    public function inUse(ResourceCategory $record): bool;

}
