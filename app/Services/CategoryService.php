<?php

namespace App\Services;

use App\Models\ContentCategory;
use App\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private readonly ContentRepositoryInterface $categories) {}

    public function save(array $data, Authenticatable $actor, ?ContentCategory $category = null): ContentCategory
    {
        return DB::transaction(function () use ($data, $actor, $category): ContentCategory {
            $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
            $data['updated_by'] = $actor->getAuthIdentifier();
            $data['created_by'] ??= $actor->getAuthIdentifier();
            $saved = $category ? $this->categories->update($category, $data) : $this->categories->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($category ? 'category.updated' : 'category.created')->log($category ? 'Category updated' : 'Category created');

            return $saved;
        });
    }

    public function delete(ContentCategory $category, Authenticatable $actor): void
    {
        $this->categories->delete($category);
        activity('content')->causedBy($actor)->event('category.deleted')->withProperties(['category_id' => $category->id])->log('Category deleted');
    }
}
