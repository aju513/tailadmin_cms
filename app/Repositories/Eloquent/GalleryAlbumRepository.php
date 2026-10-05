<?php

namespace App\Repositories\Eloquent;

use App\Models\GalleryAlbum;
use App\Repositories\Contracts\GalleryAlbumRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GalleryAlbumRepository implements GalleryAlbumRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return GalleryAlbum::query()->with('coverMedia')->withCount('photos')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
    }

    public function details(GalleryAlbum $record): GalleryAlbum
    {
        return $record->load('coverMedia', 'photos.media');
    }

    public function lock(GalleryAlbum $record): GalleryAlbum
    {
        return GalleryAlbum::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
    }

    public function slugExists(string $slug, ?GalleryAlbum $record): bool
    {
        return GalleryAlbum::query()->where('slug', $slug)
            ->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists();
    }

    public function create(array $data): GalleryAlbum
    {
        return GalleryAlbum::query()->create($data);
    }

    public function update(GalleryAlbum $record, array $data): GalleryAlbum
    {
        $record->update($data);

        return $record;
    }

    public function delete(GalleryAlbum $record): void
    {
        $record->delete();
    }

    public function photoCount(GalleryAlbum $record): int
    {
        return $record->photos()->count();
    }

    public function savePhotos(GalleryAlbum $record, array $photos, array $removeIds): void
    {
        $record->photos()->whereIn('id', $removeIds)->delete();
        foreach ($photos as $photo) {
            $record->photos()->whereKey($photo['id'])->update([
                'caption' => $photo['caption'] ?? null,
                'sort_order' => $photo['sort_order'],
            ]);
        }
    }

    public function addPhoto(GalleryAlbum $record, int $mediaId): void
    {
        $record->photos()->create([
            'media_asset_id' => $mediaId,
            'sort_order' => ((int) $record->photos()->max('sort_order')) + 1,
        ]);
    }

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return GalleryAlbum::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function nextSortOrder(): int
    {
        return ((int) GalleryAlbum::query()->max('sort_order')) + 1;
    }

    public function lockOrderedIds(): array
    {
        return GalleryAlbum::query()->orderBy('sort_order')->orderByDesc('id')->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            GalleryAlbum::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }
}
