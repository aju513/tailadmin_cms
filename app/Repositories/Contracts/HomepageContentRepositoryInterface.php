<?php

namespace App\Repositories\Contracts;

use App\Models\HomepageContent;

interface HomepageContentRepositoryInterface
{
    public function find(): ?HomepageContent;

    public function lockForSave(array $defaults): HomepageContent;

    public function update(HomepageContent $content, array $data): HomepageContent;

    public function removeGalleryImages(HomepageContent $content, array $ids): void;

    public function galleryCount(HomepageContent $content): int;

    public function addGalleryImage(HomepageContent $content, int $mediaId): void;
}
