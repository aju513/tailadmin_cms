<?php

namespace App\Repositories\Contracts;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Collection;

interface MediaAssetRepositoryInterface
{
    public function create(array $data): MediaAsset;

    public function all(): Collection;
}
