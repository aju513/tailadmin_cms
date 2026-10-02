<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamCategory;
use App\Repositories\Contracts\TeamCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TeamCategoryRepository implements TeamCategoryRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return TeamCategory::query()->withCount('members')->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();
    }

    public function active(): Collection
    {
        return TeamCategory::where('status', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function create(array $data): TeamCategory
    {
        return TeamCategory::create($data);
    }

    public function update(TeamCategory $category, array $data): TeamCategory
    {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(TeamCategory $category): void
    {
        $category->delete();
    }
    public function findByIds(array $ids): Collection { return TeamCategory::query()->whereIn('id', $ids)->get(); }
}
