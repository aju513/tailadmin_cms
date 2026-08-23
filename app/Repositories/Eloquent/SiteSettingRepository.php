<?php

namespace App\Repositories\Eloquent;

use App\Models\SiteSetting;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;

class SiteSettingRepository implements SiteSettingRepositoryInterface
{
    public function allKeyed(): array
    {
        return SiteSetting::query()->pluck('value', 'key')->all();
    }

    public function upsert(string $key, mixed $value, string $type = 'text'): SiteSetting
    {
        return SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
    }
}
