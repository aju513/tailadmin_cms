<?php

namespace App\Services;

use App\Models\TeamCategory;
use App\Repositories\Contracts\TeamCategoryRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamCategoryService
{
    public function __construct(private readonly TeamCategoryRepositoryInterface $categories) {}

    public function save(array $data, Authenticatable $actor, ?TeamCategory $category = null): TeamCategory
    {
        return DB::transaction(function () use ($data, $actor, $category) {
            $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['name']);
            $data['status'] = (bool) ($data['status'] ?? false);
            $data['created_by'] = $category?->created_by ?? $actor->getAuthIdentifier();
            $data['updated_by'] = $actor->getAuthIdentifier();
            $saved = $category ? $this->categories->update($category, $data) : $this->categories->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($category ? 'team-category.updated' : 'team-category.created')->log($category ? 'Team category updated' : 'Team category created');

            return $saved;
        });
    }

    public function delete(TeamCategory $category, Authenticatable $actor): void
    {
        DB::transaction(function () use ($category, $actor) {
            if ($category->members()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['category' => 'Remove team members from this category first.']);
            }$this->categories->delete($category);
            activity('content')->causedBy($actor)->performedOn($category)->event('team-category.deleted')->log('Team category deleted');
        });
    }
    public function bulkStatus(array $ids, bool $active): void { DB::transaction(function () use ($ids, $active): void { foreach ($this->categories->findByIds($ids) as $category) $this->categories->update($category, ['status' => $active]); }); }
    public function bulkDelete(array $ids, Authenticatable $actor): void { DB::transaction(function () use ($ids, $actor): void { foreach ($this->categories->findByIds($ids) as $category) $this->delete($category, $actor); }); }
}
