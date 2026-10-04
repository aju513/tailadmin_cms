<?php

namespace App\Repositories\Eloquent;

use App\Models\HomepageContent;
use App\Repositories\Contracts\HomepageContentRepositoryInterface;

class HomepageContentRepository implements HomepageContentRepositoryInterface
{
    public function find(): ?HomepageContent
    {
        return HomepageContent::query()->with(['galleryImages.mediaAsset', 'socialMedia'])->where('key', HomepageContent::KEY)->first();
    }

    public function lockForSave(array $defaults): HomepageContent
    {
        HomepageContent::query()->firstOrCreate(['key' => HomepageContent::KEY], $defaults);

        return HomepageContent::query()->where('key', HomepageContent::KEY)->lockForUpdate()->firstOrFail();
    }

    public function update(HomepageContent $content, array $data): HomepageContent
    {
        $content->update($data);

        return $content;
    }

    public function removeGalleryImages(HomepageContent $content, array $ids): void
    {
        $content->galleryImages()->whereIn('id', $ids)->get()->each->delete();
    }

    public function galleryCount(HomepageContent $content): int
    {
        return $content->galleryImages()->count();
    }

    public function addGalleryImage(HomepageContent $content, int $mediaId): void
    {
        $content->galleryImages()->create([
            'media_asset_id' => $mediaId,
            'sort_order' => ((int) $content->galleryImages()->max('sort_order')) + 1,
        ]);
    }
}
