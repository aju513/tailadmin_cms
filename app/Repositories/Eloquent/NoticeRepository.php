<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoticeRepository implements NoticeRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        return Notice::query()->with('fileMedia')->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))->orderBy('sort_order')->latest('created_at')->paginate(15)->withQueryString();
    }

    public function paginatePublished(): LengthAwarePaginator
    {
        return Notice::query()->with('fileMedia')->where('status', ContentStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('sort_order')->latest('published_at')->paginate(15);
    }

    public function publishedBySlug(string $slug): Notice
    {
        return Notice::query()->with('fileMedia')->where('slug', $slug)->where('status', ContentStatus::Published)->where('published_at', '<=', now())->firstOrFail();
    }

    public function create(array $data): Notice
    {
        return Notice::create($data);
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
        return Notice::where('slug', $slug)->when($except, fn ($q) => $q->whereKeyNot($except->id))->exists();
    }

    public function nextSortOrder(): int
    {
        return ((int) Notice::query()->max('sort_order')) + 1;
    }
}
