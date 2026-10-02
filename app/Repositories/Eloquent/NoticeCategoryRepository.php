<?php

namespace App\Repositories\Eloquent;

use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Page;
use App\Repositories\Contracts\NoticeCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NoticeCategoryRepository implements NoticeCategoryRepositoryInterface
{
    public function ordered(array $filters): Collection
    {
        return NoticeCategory::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    public function lock(NoticeCategory $record): NoticeCategory
    {
        return NoticeCategory::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
    }

    public function find(int $id): NoticeCategory
    {
        return NoticeCategory::query()->findOrFail($id);
    }

    public function findByIds(array $ids): Collection
    {
        return NoticeCategory::query()->whereIn('id', $ids)->get();
    }

    public function slugExists(string $slug, ?NoticeCategory $record): bool
    {
        return NoticeCategory::query()->where('slug', $slug)->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists();
    }

    public function create(array $data): NoticeCategory
    {
        return NoticeCategory::query()->create($data);
    }

    public function update(NoticeCategory $record, array $data): NoticeCategory
    {
        $record->update($data);

        return $record;
    }

    public function delete(NoticeCategory $record): void
    {
        $record->delete();
    }

    public function options(bool $activeOnly = false): array
    {
        return NoticeCategory::query()->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn ($category) => [$category->id => $category->name.($category->is_active ? '' : ' (Unpublished)')])->all();
    }

    public function lockAll(): Collection
    {
        return NoticeCategory::query()->orderBy('id')->lockForUpdate()->get();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            NoticeCategory::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(): int
    {
        return ((int) NoticeCategory::query()->max('sort_order')) + 1;
    }

    public function inUse(NoticeCategory $record): bool
    {
        return Notice::query()->where('notice_category_id', $record->id)->exists()
            || Page::query()->where('notice_category_id', $record->id)->exists();
    }
}
