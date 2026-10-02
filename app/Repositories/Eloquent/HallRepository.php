<?php

namespace App\Repositories\Eloquent;

use App\Models\Hall;
use App\Repositories\Contracts\HallRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HallRepository implements HallRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator
    {
        return Hall::query()->with('thumbnailMedia')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('building_name', 'like', '%'.$search.'%')->orWhere('location', 'like', '%'.$search.'%');
            }))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['availability_status'] ?? null, fn ($query, $status) => $query->where('availability_status', $status))
            ->orderBy('sort_order')->latest('created_at')->paginate(15)->withQueryString();
    }

    public function details(Hall $hall): Hall
    {
        return $hall->load(['thumbnailMedia', 'bannerMedia', 'socialMedia', 'galleryImages.mediaAsset']);
    }

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return Hall::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function lock(Hall $hall): Hall
    {
        return Hall::whereKey($hall->id)->lockForUpdate()->firstOrFail();
    }

    public function create(array $data): Hall
    {
        return Hall::create($data);
    }

    public function update(Hall $hall, array $data): Hall
    {
        $hall->update($data);

        return $hall->refresh();
    }

    public function delete(Hall $hall): void
    {
        $hall->delete();
    }

    public function slugExists(string $slug, ?Hall $except = null): bool
    {
        return Hall::where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except->id))->exists();
    }

    public function galleryCount(Hall $hall): int
    {
        return $hall->galleryImages()->count();
    }

    public function removeGalleryImages(Hall $hall, array $ids): void
    {
        $hall->galleryImages()->whereKey($ids)->delete();
    }

    public function addGalleryImage(Hall $hall, int $mediaId): void
    {
        $order = ((int) $hall->galleryImages()->max('sort_order')) + 1;
        $hall->galleryImages()->create(['media_asset_id' => $mediaId, 'sort_order' => $order]);
    }
}
