<?php

namespace App\Repositories\Eloquent;

use App\Models\Video;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VideoRepository implements VideoRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Video::query()->with('coverMedia')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
    }

    public function details(Video $record): Video
    {
        return $record->load('coverMedia');
    }

    public function lock(Video $record): Video
    {
        return Video::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
    }

    public function slugExists(string $slug, ?Video $record): bool
    {
        return Video::query()->where('slug', $slug)
            ->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists();
    }

    public function create(array $data): Video
    {
        return Video::query()->create($data);
    }

    public function update(Video $record, array $data): Video
    {
        $record->update($data);

        return $record;
    }

    public function delete(Video $record): void
    {
        $record->delete();
    }

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return Video::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }
}
