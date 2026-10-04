<?php

namespace App\Http\Requests\Admin\Homepage;

use App\Models\HomepageContent;
use App\Support\UploadProfile;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('homepage.edit');
    }

    protected function prepareForValidation(): void
    {
        $translations = config('settings.nepali') ? $this->input('translations') : null;
        if ($translations === null && $this->has('title')) {
            $translations = ['en' => ['title' => $this->input('title'), 'subtitle' => $this->input('subtitle'), 'body' => $this->input('body')]];
        }
        $this->merge([
            'translations' => $translations,
            'remove_gallery_ids' => $this->input('remove_gallery_ids', []),
        ]);
    }

    public function rules(): array
    {
        $limit = config('settings.homepage.gallery_limit');
        $rules = [
            'translations' => ['required', 'array:en,ne'],
            'translations.en' => ['required', 'array:title,subtitle,body'],
            'translations.ne' => ['nullable', 'array:title,subtitle,body'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'gallery_images' => ['nullable', 'array', 'max:'.$limit],
            'gallery_images.*' => UploadProfile::rules('images.homepage.gallery', 'required'),
            'remove_gallery_ids' => ['array', 'max:'.$limit],
            'remove_gallery_ids.*' => ['required', 'integer', 'distinct', Rule::exists('homepage_gallery_images', 'id')->where(fn (Builder $query) => $query->whereIn('homepage_content_id', fn (Builder $home) => $home->select('id')->from('homepage_contents')->where('key', HomepageContent::KEY)))],
            'social_media_image' => UploadProfile::rules('images.homepage.social'),
            'social_media_alt_text' => ['nullable', 'string', 'max:255'],
            'remove_social_media_image' => ['sometimes', 'boolean'],
        ];
        foreach (['en', 'ne'] as $language) {
            $rules["translations.{$language}.title"] = [$language === 'en' ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["translations.{$language}.subtitle"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$language}.body"] = ['nullable', 'string', 'max:50000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.en.title' => 'welcome title', 'translations.ne.title' => 'Nepali welcome title',
            'translations.en.body' => 'description', 'translations.ne.body' => 'Nepali description',
            'meta_title' => 'SEO title', 'gallery_images' => 'gallery images',
            'gallery_images.*' => 'gallery image', 'remove_gallery_ids.*' => 'selected gallery image',
        ];
    }
}
