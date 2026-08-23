<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        return ['site_name' => ['required', 'string', 'max:255'], 'office_name' => ['nullable', 'string', 'max:255'], 'logo_url' => ['nullable', 'url', 'max:1000'], 'phone' => ['nullable', 'string', 'max:100'], 'email' => ['nullable', 'email', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'], 'footer_text' => ['nullable', 'string', 'max:2000']];
    }
}
