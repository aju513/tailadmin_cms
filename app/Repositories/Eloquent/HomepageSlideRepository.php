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
        return HomepageSlide::query()->with('media')->orderBy('sort_order')->paginate(15);
    }

    public function active(): iterable
    {
        return HomepageSlide::query()->with('media')->where('status', ContentStatus::Published)->orderBy('sort_order')->get();
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
}
