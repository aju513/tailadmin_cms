<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\ContentAuthor;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\News;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsRepository implements NewsRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        return News::query()->with(['thumbnailMedia'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('sort_order')->latest('created_at')->paginate(15)->withQueryString();
    }

    public function paginatePublished(array $filters): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['category'] ?? null, fn ($query, string $slug) => $query->whereHas('category', fn ($category) => $category->where('slug', $slug)->where('status', true)))
            ->when($filters['tag'] ?? null, fn ($query, string $slug) => $query->whereHas('tags', fn ($tag) => $tag->where('slug', $slug)->where('status', true)))
            ->when($filters['author'] ?? null, fn ($query, string $slug) => $query->whereHas('author', fn ($author) => $author->where('slug', $slug)->where('status', true)))
            ->latest('published_at')->paginate(9)->withQueryString();
    }

    public function featured(): ?News
    {
        return $this->publishedQuery()->where('featured', true)->latest('published_at')->first();
    }

    public function latestPublished(?News $except = null, int $limit = 3): Collection
    {
        return $this->publishedQuery()->when($except, fn ($query) => $query->whereKeyNot($except->id))->latest('published_at')->limit($limit)->get();
    }

    public function publishedBySlug(string $slug): News
    {
        return $this->publishedQuery()->with(['tags' => fn ($query) => $query->where('status', true), 'bannerMedia', 'socialMedia'])->where('slug', $slug)->firstOrFail();
    }

    public function activeCategories(): Collection
    {
        return ContentCategory::query()->where('status', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function activeAuthors(): Collection
    {
        return ContentAuthor::query()->where('status', true)->orderBy('name')->get();
    }

    public function activeTags(): Collection
    {
        return ContentTag::query()->where('status', true)->orderBy('name')->get();
    }

    public function categoryBySlug(string $slug): ContentCategory
    {
        return ContentCategory::query()->where('slug', $slug)->where('status', true)->firstOrFail();
    }

    public function authorBySlug(string $slug): ContentAuthor
    {
        return ContentAuthor::query()->where('slug', $slug)->where('status', true)->firstOrFail();
    }

    public function tagBySlug(string $slug): ContentTag
    {
        return ContentTag::query()->where('slug', $slug)->where('status', true)->firstOrFail();
    }

    public function slugExists(string $slug, ?News $except = null): bool
    {
        return News::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except->id))->exists();
    }

    public function create(array $data): News
    {
        return News::query()->create($data);
    }

    public function update(News $news, array $data): News
    {
        $news->update($data);

        return $news->refresh();
    }

    public function syncTags(News $news, array $ids): void
    {
        $news->tags()->sync($ids);
    }

    public function clearFeatured(?News $except = null): void
    {
        News::query()->where('featured', true)->when($except, fn ($query) => $query->whereKeyNot($except->id))->update(['featured' => false]);
    }

    public function delete(News $news): void
    {
        $news->delete();
    }

    public function findByIds(array $ids): Collection
    {
        return News::query()->whereIn('id', $ids)->get();
    }

    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            News::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }

    private function publishedQuery()
    {
        return News::query()->with(['category' => fn ($query) => $query->where('status', true), 'author' => fn ($query) => $query->where('status', true), 'thumbnailMedia'])
            ->where('status', ContentStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
