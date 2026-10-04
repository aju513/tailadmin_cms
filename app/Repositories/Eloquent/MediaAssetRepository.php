<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaAsset;
use App\Repositories\Contracts\MediaAssetRepositoryInterface;

class MediaAssetRepository implements MediaAssetRepositoryInterface
{
    public function create(array $data): MediaAsset
    {
        return MediaAsset::query()->create($data);
    }
}
