<?php

namespace App\Http\Requests\Admin\Settings;

use App\Services\SiteSettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $rules = ['site_name' => ['required', 'string', 'max:255'], 'office_name' => ['nullable', 'string', 'max:255'], 'logo_url' => ['nullable', 'url:http,https', 'max:1000'], 'phone' => ['nullable', 'string', 'max:100'], 'email' => ['nullable', 'email', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'], 'footer_text' => ['nullable', 'string', 'max:2000'], 'meta_description' => ['nullable', 'string', 'max:320'], 'hero_title' => ['nullable', 'string', 'max:180'], 'hero_description' => ['nullable', 'string', 'max:600'], 'about_title' => ['nullable', 'string', 'max:255'], 'about_description' => ['nullable', 'string', 'max:50000'], 'office_hours' => ['nullable', 'string', 'max:1000'], 'training_url' => ['nullable', 'url:http,https', 'max:1000'], 'map_url' => ['nullable', 'url:http,https', 'max:1000'], 'facebook_url' => ['nullable', 'url:http,https', 'max:1000'], 'youtube_url' => ['nullable', 'url:http,https', 'max:1000'], 'linkedin_url' => ['nullable', 'url:http,https', 'max:1000']];

        return [...$rules, ...[
            'recaptcha_site_key' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'province_name' => ['nullable', 'string', 'max:255'],
            'about_url' => ['nullable', 'url:http,https', 'max:1000'],
            'tmis_url' => ['nullable', 'url:http,https', 'max:1000'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:1000'],
            'x_url' => ['nullable', 'url:http,https', 'max:1000'],
            'contact_officer_phone' => ['nullable', 'string', 'max:100'],
            'contact_officer_photo_url' => ['nullable', 'url:http,https', 'max:1000'],
            'homepage_services' => ['nullable', 'array', 'size:4'],
            'homepage_services.*' => ['required', 'array:title,icon,description'],
            'homepage_services.*.title' => ['required', 'string', 'max:180'],
            'homepage_services.*.icon' => ['required', 'in:capacity.svg,organization.svg,research.svg,consultant.svg'],
            'homepage_services.*.description' => ['required', 'string', 'max:2000'],
            'important_links' => ['prohibited'],
            'capacity_reports' => ['prohibited'],
        ]];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $saved = app(SiteSettingService::class)->recaptcha();
            $siteKey = $this->exists('recaptcha_site_key') ? $this->input('recaptcha_site_key') : $saved['site_key'];
            $secretKey = $this->input('recaptcha_secret_key') ?: $saved['secret_key'];
            if (filled($siteKey) && blank($secretKey)) {
                $validator->errors()->add('recaptcha_secret_key', 'Enter the matching reCAPTCHA v3 secret key.');
            }
            if ($this->filled('recaptcha_secret_key') && blank($siteKey)) {
                $validator->errors()->add('recaptcha_site_key', 'Enter the matching reCAPTCHA v3 site key.');
            }
        }];
    }
}
