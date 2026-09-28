<?php

namespace App\Repositories\Contracts;

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NewsRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator;

    public function paginatePublished(array $filters): LengthAwarePaginator;

    public function featured(): ?News;

    public function latestPublished(?News $except = null, int $limit = 3): Collection;

    public function publishedBySlug(string $slug): News;

    public function activeCategories(): Collection;

    public function activeAuthors(): Collection;

    public function activeTags(): Collection;

    public function categoryBySlug(string $slug): \App\Models\ContentCategory;

    public function authorBySlug(string $slug): \App\Models\ContentAuthor;

    public function tagBySlug(string $slug): \App\Models\ContentTag;

    public function slugExists(string $slug, ?News $except = null): bool;

    public function create(array $data): News;

    public function update(News $news, array $data): News;

    public function syncTags(News $news, array $ids): void;

    public function clearFeatured(?News $except = null): void;

    public function delete(News $news): void;
}
