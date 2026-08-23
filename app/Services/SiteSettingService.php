<?php

namespace App\Services;

use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class SiteSettingService
{
    public const KEYS = ['site_name', 'office_name', 'logo_url', 'phone', 'email', 'address', 'footer_text'];

    public function __construct(private readonly SiteSettingRepositoryInterface $settings) {}

    public function all(): array
    {
        return $this->settings->allKeyed();
    }

    public function update(array $data, Authenticatable $actor): void
    {
        DB::transaction(function () use ($data, $actor): void {
            foreach (self::KEYS as $key) {
                $this->settings->upsert($key, $data[$key] ?? null);
            }
            activity('content')->causedBy($actor)->event('settings.updated')->withProperties(['keys' => self::KEYS])->log('Site settings updated');
        });
    }
}
