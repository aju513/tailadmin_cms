<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

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
            'capacity_reports' => ['nullable', 'array', 'max:20'],
            'capacity_reports.*' => ['required', 'array:year,development,collaboration'],
            'capacity_reports.*.year' => ['required', 'string', 'max:20', 'distinct'],
            'capacity_reports.*.development' => ['required', 'array:training_programs,participants,in_service_programs,in_service_participants,materials,dialogues,research,consultancy'],
            'capacity_reports.*.development.training_programs' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.participants' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.in_service_programs' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.in_service_participants' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.materials' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.dialogues' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.research' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.development.consultancy' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration' => ['required', 'array:training_programs,participants,in_service_programs,in_service_participants,materials,dialogues,research,consultancy'],
            'capacity_reports.*.collaboration.training_programs' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.participants' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.in_service_programs' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.in_service_participants' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.materials' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.dialogues' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.research' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'capacity_reports.*.collaboration.consultancy' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
        ]];
    }
}
