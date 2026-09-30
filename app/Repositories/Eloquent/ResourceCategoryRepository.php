<?php

namespace App\Repositories\Eloquent;

use App\Models\Page;
use App\Models\ResourceDocument;
use App\Models\ResourceCategory;
use App\Repositories\Contracts\ResourceCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResourceCategoryRepository implements ResourceCategoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return ResourceCategory::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
    }

    public function lock(ResourceCategory $record): ResourceCategory
    {
        return ResourceCategory::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
    }

    public function find(int $id): ResourceCategory
    {
        return ResourceCategory::query()->findOrFail($id);
    }

    public function slugExists(string $slug, ?ResourceCategory $record): bool
    {
        return ResourceCategory::query()->where('slug', $slug)->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists();
    }

    public function create(array $data): ResourceCategory
    {
        return ResourceCategory::query()->create($data);
    }

    public function update(ResourceCategory $record, array $data): ResourceCategory
    {
        $record->update($data);

        return $record;
    }

    public function delete(ResourceCategory $record): void
    {
        $record->delete();
    }

    public function options(): array
    {
        return ResourceCategory::query()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all();
    }

    public function inUse(ResourceCategory $record): bool
    {
        return ResourceDocument::query()->where('resource_category_id', $record->id)->exists()
            || Page::query()->where('resource_category_id', $record->id)->exists();
    }

}
