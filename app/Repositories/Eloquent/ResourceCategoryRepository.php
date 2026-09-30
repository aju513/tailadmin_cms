<?php

namespace App\Repositories\Eloquent;

use App\Models\Page;
use App\Models\ResourceDocument;
use App\Models\ResourceCategory;
use App\Repositories\Contracts\ResourceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ResourceCategoryRepository implements ResourceCategoryRepositoryInterface
{
    public function ordered(array $filters): Collection
    {
        return ResourceCategory::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->orderBy('id')->get();
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

    public function options(bool $activeOnly = false): array
    {
        return ResourceCategory::query()->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn ($category) => [$category->id => $category->name.($category->is_active ? '' : ' (Unpublished)')])->all();
    }

    public function lockAll(): Collection
    {
        return ResourceCategory::query()->orderBy('id')->lockForUpdate()->get();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            ResourceCategory::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(): int
    {
        return ((int) ResourceCategory::query()->max('sort_order')) + 1;
    }

    public function inUse(ResourceCategory $record): bool
    {
        return ResourceDocument::query()->where('resource_category_id', $record->id)->exists()
            || Page::query()->where('resource_category_id', $record->id)->exists();
    }

}
