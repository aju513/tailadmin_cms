<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\Popup;
use App\Repositories\Contracts\PopupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PopupRepository implements PopupRepositoryInterface
{
    public function listing(array $filters): Collection
    {
        return Popup::query()->with('media')->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))->orderBy('sort_order')->orderBy('id')->get();
    }

    public function published(): Collection
    {
        return Popup::query()->with('media')->where('status', ContentStatus::Published)->whereHas('media', fn ($query) => $query->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']))->orderBy('sort_order')->orderBy('id')->get();
    }

    public function create(array $data): Popup
    {
        return Popup::query()->create($data);
    }

    public function update(Popup $slide, array $data): Popup
    {
        $slide->update($data);

        return $slide->refresh();
    }

    public function delete(Popup $slide): void
    {
        $slide->delete();
    }

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection
    {
        return Popup::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function details(Popup $slide): Popup
    {
        return $slide->load('media');
    }

    public function lock(Popup $slide): Popup
    {
        return Popup::query()->whereKey($slide->id)->lockForUpdate()->firstOrFail();
    }

    public function nextSortOrder(): int
    {
        return ((int) Popup::query()->max('sort_order')) + 1;
    }

    public function lockOrderedIds(): array
    {
        return Popup::query()->orderBy('sort_order')->orderBy('id')->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $position => $id) {
            Popup::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }
}
