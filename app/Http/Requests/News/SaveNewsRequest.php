<?php

namespace App\Http\Requests\News;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SaveNewsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', $this->route('news')?->status?->value ?? ContentStatus::Draft->value),
            'featured' => $this->boolean('featured'),
        ]);
    }

    public function rules(): array
    {
        $slug = Rule::unique('news', 'slug');
        if ($this->route('news')) {
            $slug->ignore($this->route('news')->id);
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', $slug],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:10000'],
            'body' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'featured' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'thumbnail_alt_text' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'image', 'max:5120'],
            'banner_alt_text' => ['nullable', 'string', 'max:255'],
            'social_media_image' => ['nullable', 'image', 'max:5120'],
            'social_media_alt_text' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
