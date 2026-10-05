<?php

namespace App\Services;

use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SiteSettingService
{
    public const KEYS = ['site_name', 'office_name', 'logo_url', 'phone', 'email', 'address', 'footer_text', 'meta_description', 'hero_title', 'hero_description', 'about_title', 'about_description', 'training_url', 'office_hours', 'map_url', 'facebook_url', 'youtube_url', 'linkedin_url'];

    public const DESIGN_KEYS = ['province_name', 'about_url', 'tmis_url', 'instagram_url', 'x_url', 'contact_officer_phone', 'contact_officer_photo_url', 'homepage_services', 'website_url', 'contact_officer_name', 'social_links'];

    public const ARRAY_KEYS = ['homepage_services', 'social_links'];

    public function __construct(private readonly SiteSettingRepositoryInterface $settings) {}

    public function all(): array
    {
        $values = array_replace(array_fill_keys([...self::KEYS, ...self::DESIGN_KEYS], null), ['site_name' => config('frontend.name'), 'hero_title' => config('frontend.hero_title'), 'hero_description' => config('frontend.hero_description'), 'about_title' => config('frontend.about_title'), 'about_description' => config('frontend.about_description')], $this->settings->allKeyed());
        foreach (config('frontend.defaults', []) as $key => $default) {
            $values[$key] = $values[$key] ?: $default;
        }
        foreach (self::ARRAY_KEYS as $key) {
            if ($key === 'social_links' && ! isset($values[$key])) {
                $values[$key] = json_encode(collect(['facebook', 'youtube', 'linkedin', 'instagram', 'x'])->filter(fn ($platform) => filled($values[$platform.'_url']))->map(fn ($platform) => ['label' => $platform === 'x' ? 'X' : ucfirst($platform), 'url' => $values[$platform.'_url']])->values()->all());
            }
            $values[$key] = isset($values[$key]) ? (json_decode($values[$key], true) ?: []) : config('frontend.'.$key, []);
        }

        $values['office_name'] = $values['site_name'];
        $values['social_links'] = is_array($values['social_links']) ? $values['social_links'] : [];

        unset($values['recaptcha_secret_key']);
        $values['recaptcha_site_key'] ??= null;

        return $values;
    }

    public function update(array $data, Authenticatable $actor): void
    {
        if (array_key_exists('about_description', $data)) {
            $data['about_description'] = app(\App\Services\Frontend\SafeHtml::class)->clean($data['about_description']);
        }
        DB::transaction(function () use ($data, $actor): void {
            if (array_key_exists('recaptcha_site_key', $data)) {
                $this->settings->upsert('recaptcha_site_key', $data['recaptcha_site_key']);
            }
            if (filled($data['recaptcha_secret_key'] ?? null)) {
                $this->settings->upsert('recaptcha_secret_key', Crypt::encryptString($data['recaptcha_secret_key']), 'encrypted');
            }
            foreach ([...self::KEYS, ...self::DESIGN_KEYS] as $key) {
                if (array_key_exists($key, $data)) {
                    $json = in_array($key, self::ARRAY_KEYS, true);
                    $this->settings->upsert($key, $json ? json_encode(array_values($data[$key] ?? []), JSON_THROW_ON_ERROR) : $data[$key], $json ? 'json' : 'text');
                }
            }
            activity('content')->causedBy($actor)->event('settings.updated')->withProperties(['keys' => array_keys($data)])->log('Site settings updated');
        });
    }

    public function recaptcha(): array
    {
        $secret = null;
        if ($encrypted = $this->settings->value('recaptcha_secret_key')) {
            try {
                $secret = Crypt::decryptString($encrypted);
            } catch (DecryptException $exception) {
                // An unreadable key must never bypass verification.
            }
        }

        return ['site_key' => $this->settings->value('recaptcha_site_key'), 'secret_key' => $secret];
    }

    public function recaptchaConfigured(): bool
    {
        $keys = $this->recaptcha();

        return filled($keys['site_key']) && filled($keys['secret_key']);
    }
}
