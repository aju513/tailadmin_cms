<?php

namespace App\Services;

use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class SiteSettingService
{
    public const KEYS = ['site_name', 'office_name', 'logo_url', 'phone', 'email', 'address', 'footer_text', 'meta_description', 'hero_title', 'hero_description', 'about_title', 'about_description', 'training_url', 'office_hours', 'map_url', 'facebook_url', 'youtube_url', 'linkedin_url'];

    public const DESIGN_KEYS = ['province_name', 'about_url', 'tmis_url', 'instagram_url', 'x_url', 'contact_officer_phone', 'contact_officer_photo_url', 'homepage_services', 'capacity_reports', 'important_links'];

    public const ARRAY_KEYS = ['homepage_services', 'capacity_reports', 'important_links'];

    public function __construct(private readonly SiteSettingRepositoryInterface $settings) {}

    public function all(): array
    {
        $values = array_replace(array_fill_keys([...self::KEYS, ...self::DESIGN_KEYS], null), ['site_name' => config('frontend.name'), 'hero_title' => config('frontend.hero_title'), 'hero_description' => config('frontend.hero_description'), 'about_title' => config('frontend.about_title'), 'about_description' => config('frontend.about_description')], $this->settings->allKeyed());
        foreach (config('frontend.defaults', []) as $key => $default) {
            $values[$key] = $values[$key] ?: $default;
        }
        foreach (self::ARRAY_KEYS as $key) {
            $values[$key] = isset($values[$key]) ? (json_decode($values[$key], true) ?: []) : config('frontend.'.$key, []);
        }

        return $values;
    }

    public function update(array $data, Authenticatable $actor): void
    {
        if (array_key_exists('about_description', $data)) {
            $data['about_description'] = app(\App\Services\Frontend\SafeHtml::class)->clean($data['about_description']);
        }
        DB::transaction(function () use ($data, $actor): void {
            foreach ([...self::KEYS, ...self::DESIGN_KEYS] as $key) {
                if (array_key_exists($key, $data)) {
                    $json = in_array($key, self::ARRAY_KEYS, true);
                    $this->settings->upsert($key, $json ? json_encode(array_values($data[$key] ?? []), JSON_THROW_ON_ERROR) : $data[$key], $json ? 'json' : 'text');
                }
            }
            activity('content')->causedBy($actor)->event('settings.updated')->withProperties(['keys' => array_keys($data)])->log('Site settings updated');
        });
    }
}
