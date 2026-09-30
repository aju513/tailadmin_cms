<?php

namespace App\Services;

use App\Models\ResourceCategory;
use App\Repositories\Contracts\ResourceCategoryRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResourceCategoryService
{
    public function __construct(private readonly ResourceCategoryRepositoryInterface $categories) {}

    public function index(array $filters): Collection
    {
        return $this->categories->ordered($filters);
    }

    public function options(bool $activeOnly = false): array
    {
        return $this->categories->options($activeOnly);
    }

    public function lockSelection(int $id): ResourceCategory
    {
        return $this->categories->lock($this->categories->find($id));
    }

    public function newRecord(): ResourceCategory
    {
        return new ResourceCategory(['is_active' => true]);
    }

    public function details(ResourceCategory $category): ResourceCategory
    {
        return $this->categories->find($category->id);
    }

    public function save(array $data, Authenticatable $actor, ?ResourceCategory $category = null): ResourceCategory
    {
        Gate::forUser($actor)->authorize($category ? 'resource-categories.edit' : 'resource-categories.create');

        return DB::transaction(function () use ($data, $actor, $category): ResourceCategory {
            $category = $category ? $this->categories->lock($category) : null;
            $data['slug'] = Str::slug(($data['slug'] ?? null) ?: ($category?->slug ?: $data['name']));
            if ($data['slug'] === '' || $this->categories->slugExists($data['slug'], $category)) {
                throw ValidationException::withMessages(['slug' => 'Enter a unique URL slug using letters or numbers.']);
            }
            $data['is_active'] = (bool) $data['is_active'];
            $data['sort_order'] = $category?->sort_order ?? $this->categories->nextSortOrder();
            $data['created_by'] = $category?->created_by ?? $actor->getAuthIdentifier();
            $data['updated_by'] = $actor->getAuthIdentifier();
            $saved = $category ? $this->categories->update($category, $data) : $this->categories->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)
                ->event($category ? 'resource-category.updated' : 'resource-category.created')->log('Resource category saved');

            return $saved;
        });
    }

    public function reorder(array $ids, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('resource-categories.edit');
        DB::transaction(function () use ($ids, $actor): void {
            $records = $this->categories->lockAll();
            $expected = $records->modelKeys();
            $received = array_map('intval', $ids);
            sort($expected);
            $comparison = $received;
            sort($comparison);
            if ($comparison !== $expected) {
                throw ValidationException::withMessages(['categories' => 'The category list changed. Reload before reordering.']);
            }
            $this->categories->reorder($received);
            activity('content')->causedBy($actor)->event('resource-category.reordered')
                ->withProperties(['category_ids' => $received])->log('Resource categories reordered');
        });
    }

    public function delete(ResourceCategory $category, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('resource-categories.delete');
        DB::transaction(function () use ($category, $actor): void {
            $category = $this->categories->lock($category);
            if ($this->categories->inUse($category)) {
                throw ValidationException::withMessages(['category' => 'Move resources and change connected pages before deleting this category.']);
            }
            activity('content')->causedBy($actor)->performedOn($category)->event('resource-category.deleted')->log('Resource category deleted');
            $this->categories->delete($category);
        });
    }
}
