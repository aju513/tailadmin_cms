<?php

namespace App\Http\Requests\Admin\Hall;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SaveHallRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $translations = config('settings.nepali') ? $this->input('translations') : null;
        if ($translations === null && $this->has('title')) {
            $translations = ['en' => [
                'title' => $this->input('title'), 'summary' => $this->input('summary'),
                'body' => $this->input('body'), 'booking_instructions' => $this->input('booking_instructions'),
            ]];
        }
        $this->merge([
            'translations' => $translations,
            'status' => $this->input('status', $this->route('hall')?->status?->value ?? 'draft'),
            'amenities' => $this->input('amenities', []),
            'remove_gallery_ids' => $this->input('remove_gallery_ids', []),
        ]);
    }

    public function rules(): array
    {
        $hall = $this->route('hall');
        $slug = Rule::unique('halls', 'slug')->ignore($hall?->id);
        $rules = [
            'translations' => ['required', 'array:en,ne'],
            'translations.en' => ['required', 'array:title,summary,body,booking_instructions'],
            'translations.ne' => ['nullable', 'array:title,summary,body,booking_instructions'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', $slug],
            'building_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'floor_area' => ['nullable', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'rental_rate' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'rate_unit' => ['nullable', 'required_with:rental_rate', Rule::in(array_keys(config('halls.rate_units')))],
            'amenities' => ['array', 'max:'.count(config('halls.amenities'))],
            'amenities.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('halls.amenities')))],
            'availability_status' => ['required', Rule::in(array_keys(config('halls.availability')))],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'published_at' => ['nullable', 'date'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\s.\-]+$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'map_url' => ['nullable', 'url:http,https', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'gallery_images' => ['nullable', 'array', 'max:'.config('halls.gallery_limit')],
            'gallery_images.*' => UploadProfile::rules('images.hall.gallery', 'required'),
            'remove_gallery_ids' => ['array'],
            'remove_gallery_ids.*' => ['required', 'integer', 'distinct', Rule::exists('hall_gallery_images', 'id')->where('hall_id', $hall?->id ?? 0)],
        ];
        foreach (['en', 'ne'] as $language) {
            $rules["translations.{$language}.title"] = [$language === 'en' ? 'required' : 'nullable', 'string', 'max:255'];
            foreach (['summary', 'body', 'booking_instructions'] as $field) {
                $rules["translations.{$language}.{$field}"] = ['nullable', 'string', 'max:50000'];
            }
        }
        foreach (['thumbnail', 'banner_image', 'social_media_image'] as $field) {
            $rules[$field] = UploadProfile::rules('images.hall.'.match ($field) {
                'banner_image' => 'banner',
                'social_media_image' => 'social',
                default => 'thumbnail',
            });
            $rules[$field.'_alt_text'] = ['nullable', 'string', 'max:255'];
            $rules['remove_'.$field] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (($this->input('status') === 'published' || $this->route('hall')?->status === ContentStatus::Published)
                && ! $this->user()->can('halls.publish')) {
                $validator->errors()->add('status', 'Publishing or changing published halls requires the hall publishing permission.');
            }
        }];
    }
}
