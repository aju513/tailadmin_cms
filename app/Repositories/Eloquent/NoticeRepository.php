<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NoticeRepository implements NoticeRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        return Notice::query()->with('fileMedia', 'category')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['notice_category_id'] ?? null, fn ($query, $type) => $query->where('notice_category_id', $type))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
    }

    private function publishedQuery(): Builder
    {
        return Notice::query()->with('fileMedia', 'category')->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereHas('category', fn ($query) => $query->where('is_active', true));
    }

    public function paginatePublished(?int $categoryId = null, string $pageName = 'page'): LengthAwarePaginator
    {
        return $this->publishedQuery()->when($categoryId, fn ($query) => $query->where('notice_category_id', $categoryId))
            ->orderBy('sort_order')->latest('published_at')->latest('id')->paginate(15, ['*'], $pageName)->withQueryString();
    }

    public function publishedBySlug(string $slug): Notice
    {
        return $this->publishedQuery()->where('slug', $slug)->firstOrFail();
    }

    public function lock(Notice $notice): Notice
    {
        return Notice::query()->whereKey($notice->id)->lockForUpdate()->firstOrFail();
    }

    public function details(Notice $notice): Notice
    {
        return $notice->load('fileMedia', 'category');
    }

    public function create(array $data): Notice
    {
        return Notice::query()->create($data);
    }

    public function update(Notice $notice, array $data): Notice
    {
        $notice->update($data);

        return $notice->refresh();
    }

    public function delete(Notice $notice): void
    {
        $notice->delete();
    }

    public function slugExists(string $slug, ?Notice $except = null): bool
    {
        return Notice::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except->id))->exists();
    }

    public function nextSortOrder(): int
    {
        return ((int) Notice::query()->max('sort_order')) + 1;
    }

    public function findByIds(array $ids): Collection
    {
        return Notice::query()->whereIn('id', $ids)->get();
    }

    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            Notice::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }
}
