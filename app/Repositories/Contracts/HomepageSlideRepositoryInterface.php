<?php

namespace App\Repositories\Contracts;

use App\Models\HomepageSlide;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HomepageSlideRepositoryInterface
{
    public function paginateForIndex(): LengthAwarePaginator;

    public function active(): iterable;

    public function create(array $data): HomepageSlide;

    public function update(HomepageSlide $slide, array $data): HomepageSlide;

    public function delete(HomepageSlide $slide): void;

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection;
}
