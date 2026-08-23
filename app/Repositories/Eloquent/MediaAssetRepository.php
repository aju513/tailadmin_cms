<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaAsset;
use App\Repositories\Contracts\MediaAssetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MediaAssetRepository implements MediaAssetRepositoryInterface
{
    public function create(array $data): MediaAsset
    {
        return MediaAsset::query()->create($data);
    }

    public function all(): Collection
    {
        return MediaAsset::query()->latest()->get();
    }
}
