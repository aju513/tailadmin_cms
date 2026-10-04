<?php

namespace App\Repositories\Contracts;

use App\Models\MediaAsset;

interface MediaAssetRepositoryInterface
{
    public function create(array $data): MediaAsset;
}
