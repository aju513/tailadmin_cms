<?php

namespace App\Services;

use App\Models\ResourceCategory;
use App\Repositories\Contracts\ResourceCategoryRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResourceCategoryService
{
    public function __construct(private readonly ResourceCategoryRepositoryInterface $categories) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->categories->paginate($filters);
    }

    public function options(): array
    {
        return $this->categories->options();
    }

    public function lockSelection(int $id): ResourceCategory
    {
        return $this->categories->lock($this->categories->find($id));
    }

    public function newRecord(): ResourceCategory
    {
        return new ResourceCategory(['sort_order' => 0]);
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
            $data['created_by'] = $category?->created_by ?? $actor->getAuthIdentifier();
            $data['updated_by'] = $actor->getAuthIdentifier();
            $saved = $category ? $this->categories->update($category, $data) : $this->categories->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)
                ->event($category ? 'resource-category.updated' : 'resource-category.created')->log('Resource category saved');

            return $saved;
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
