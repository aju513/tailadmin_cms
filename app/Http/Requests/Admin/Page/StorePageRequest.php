<?php

namespace App\Http\Requests\Admin\Page;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $translations = config('settings.nepali') ? $this->input('translations') : null;
        if ($translations === null && $this->has('title')) {
            $translations = ['en' => ['title' => $this->input('title'), 'summary' => $this->input('summary'), 'body' => $this->input('body')]];
        }

        $this->merge(['page_type' => $this->input('page_type', PageType::Article->value), 'translations' => $translations]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'translations' => ['required', 'array:en,ne'],
            'translations.en' => ['required', 'array:title,summary,body'],
            'translations.en.title' => ['required', 'string', 'max:255'],
            'translations.en.summary' => ['nullable', 'string', 'max:10000'],
            'translations.en.body' => ['nullable', 'string'],
            'translations.ne' => ['nullable', 'array:title,summary,body'],
            'translations.ne.title' => ['nullable', 'string', 'max:255'],
            'translations.ne.summary' => ['nullable', 'string', 'max:10000'],
            'translations.ne.body' => ['nullable', 'string'],
            'page_type' => ['required', Rule::enum(PageType::class)],
            'resource_category_id' => ['exclude_unless:page_type,resource', 'nullable', 'integer', 'exists:resource_categories,id'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'parent_id' => ['nullable', 'integer', 'exists:pages,id'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'banner_image' => UploadProfile::rules('images.page.banner'),
            'banner_alt_text' => ['nullable', 'string', 'max:255'],
            'social_media_image' => UploadProfile::rules('images.page.social'),
            'social_media_alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
