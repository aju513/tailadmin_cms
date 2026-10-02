<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PageRepository implements PageRepositoryInterface
{
    public function lock(int $id): Page
    {
        return Page::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function find(int $id): Page
    {
        return Page::query()->findOrFail($id);
    }

    public function noticeSections(): Collection
    {
        return $this->orderedForIndex([])->filter(fn (Page $page) => $page->page_type === \App\Enums\PageType::Notices)->values();
    }

    public function hasNoticeAssignments(array $pageIds): bool
    {
        return \App\Models\Notice::query()->whereIn('notice_page_id', $pageIds)->exists();
    }

    public function paginateForIndex(array $filters): LengthAwarePaginator
    {
        return Page::query()->with('parent')->when($filters['search'] ?? null, function ($query, string $search): void {
            $query->where(fn ($query) => $query->where('title->en', 'like', "%{$search}%")->orWhere('title->ne', 'like', "%{$search}%")->orWhere('path', 'like', "%{$search}%"));
        })->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['page_type'] ?? null, fn ($query, string $pageType) => $query->where('page_type', $pageType))
            ->orderBy('path')->paginate(15)->withQueryString();
    }

    public function orderedForIndex(array $filters): Collection
    {
        $pages = Page::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(fn ($query) => $query->where('title->en', 'like', "%{$search}%")->orWhere('title->ne', 'like', "%{$search}%")->orWhere('path', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['page_type'] ?? null, fn ($query, string $pageType) => $query->where('page_type', $pageType))
            ->orderBy('sort_order')->orderBy('title->en')->get();

        if (($filters['search'] ?? null) || ($filters['status'] ?? null) || ($filters['page_type'] ?? null)) {
            return $pages;
        }

        $grouped = $pages->groupBy(fn (Page $page): string => (string) ($page->parent_id ?? 0));
        $flattened = collect();
        $append = function (int $parentId, int $level) use (&$append, $grouped, $flattened): void {
            foreach ($grouped->get((string) $parentId, collect()) as $page) {
                $page->setAttribute('tree_level', $level);
                $flattened->push($page);
                $append($page->id, $level + 1);
            }
        };
        $append(0, 0);

        return $flattened;
    }

    public function publicByPath(string $path): Page
    {
        return Page::query()->with(['children', 'bannerMedia', 'socialMedia', 'noticeCategory'])->where('path', $path)->where('status', ContentStatus::Published)->firstOrFail();
    }

    public function create(array $data): Page
    {
        return Page::query()->create($data);
    }

    public function update(Page $page, array $data): Page
    {
        $page->update($data);

        return $page->refresh();
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function findByIds(array $ids): Collection
    {
        return Page::query()->whereKey($ids)->get();
    }

    public function descendants(Page $page): Collection
    {
        return Page::query()->where('path', 'like', $page->path.'/%')->orderBy('path')->get();
    }

    public function allForParentSelect(?Page $except = null): Collection
    {
        $query = Page::query()->orderBy('path');
        if ($except) {
            $query->whereKeyNot($except->id)->where('path', 'not like', $except->path.'/%');
        }

        return $query->get();
    }

    public function reorder(array $ids): void
    {
        $pages = Page::query()->whereIn('id', $ids)->get()->keyBy('id');
        $positions = [];

        foreach ($ids as $id) {
            $page = $pages->get((int) $id);
            if (! $page) {
                continue;
            }

            $parentKey = (string) ($page->parent_id ?? 0);
            $positions[$parentKey] ??= 0;
            $page->update(['sort_order' => $positions[$parentKey]++]);
        }
    }
}
