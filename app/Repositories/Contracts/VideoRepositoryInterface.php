<?php

namespace App\Repositories\Contracts;

use App\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VideoRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function details(Video $record): Video;

    public function lock(Video $record): Video;

    public function slugExists(string $slug, ?Video $record): bool;

    public function create(array $data): Video;

    public function update(Video $record, array $data): Video;

    public function delete(Video $record): void;

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection;
}
