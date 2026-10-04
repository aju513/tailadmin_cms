<?php

namespace App\Http\Requests\Admin\News;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class SaveNewsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', $this->route('news')?->status?->value ?? ContentStatus::Draft->value),
            'featured' => $this->boolean('featured'),
        ]);

        if (is_string($this->input('slug'))) {
            $this->merge(['slug' => Str::lower($this->input('slug'))]);
        }
    }

    public function rules(): array
    {
        $slug = Rule::unique('news', 'slug');
        if ($this->route('news')) {
            $slug->ignore($this->route('news')->id);
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'body' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'featured' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'thumbnail' => UploadProfile::rules('images.news.thumbnail'),
            'thumbnail_alt_text' => ['nullable', 'string', 'max:255'],
            'banner_image' => UploadProfile::rules('images.news.banner'),
            'banner_alt_text' => ['nullable', 'string', 'max:255'],
            'social_media_image' => UploadProfile::rules('images.news.social'),
            'social_media_alt_text' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Use letters and numbers separated by single hyphens for the URL slug.',
            'slug.unique' => 'That URL slug is already in use.',
        ];
    }

    public function attributes(): array
    {
        return ['slug' => 'URL slug', 'meta_title' => 'SEO title'];
    }
}
