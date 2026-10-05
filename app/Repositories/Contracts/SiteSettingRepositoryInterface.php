<?php

namespace App\Repositories\Contracts;

use App\Models\SiteSetting;

interface SiteSettingRepositoryInterface
{
    public function allKeyed(): array;

    public function value(string $key): ?string;

    public function upsert(string $key, mixed $value, string $type = 'text'): SiteSetting;
}
