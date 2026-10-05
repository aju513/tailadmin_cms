<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\HomepageSlide;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HomepageSlideRepository implements HomepageSlideRepositoryInterface
{
    public function paginateForIndex(): LengthAwarePaginator
    {
        return HomepageSlide::query()->with('media')->orderBy('sort_order')->orderBy('id')->paginate(15);
    }

    public function active(): iterable
    {
        return HomepageSlide::query()->with('media')->where('status', ContentStatus::Published)->orderBy('sort_order')->orderBy('id')->get();
    }

    public function create(array $data): HomepageSlide
    {
        return HomepageSlide::query()->create($data);
    }

    public function update(HomepageSlide $slide, array $data): HomepageSlide
    {
        $slide->update($data);

        return $slide->refresh();
    }

    public function delete(HomepageSlide $slide): void
    {
        $slide->delete();
    }

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return HomepageSlide::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function details(HomepageSlide $slide): HomepageSlide
    {
        return $slide->load('media');
    }

    public function lock(HomepageSlide $slide): HomepageSlide
    {
        return HomepageSlide::query()->whereKey($slide->id)->lockForUpdate()->firstOrFail();
    }

    public function nextSortOrder(): int
    {
        return ((int) HomepageSlide::query()->max('sort_order')) + 1;
    }

    public function lockOrderedIds(): array
    {
        return HomepageSlide::query()->orderBy('sort_order')->orderBy('id')->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            HomepageSlide::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }
}
