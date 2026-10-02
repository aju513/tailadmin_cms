<?php

namespace App\Repositories\Contracts;

use App\Models\TeamCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TeamCategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function active(): Collection;

    public function create(array $data): TeamCategory;

    public function update(TeamCategory $category, array $data): TeamCategory;

    public function delete(TeamCategory $category): void;
    public function findByIds(array $ids): Collection;
}
