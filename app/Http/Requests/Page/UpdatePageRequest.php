<?php

namespace App\Http\Requests\Page;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['page_type' => $this->input('page_type', PageType::Standard->value)]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'page_type' => ['required', Rule::enum(PageType::class)],
            'parent_id' => ['nullable', 'integer', 'exists:pages,id'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'body' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'banner_image' => ['nullable', 'image', 'max:5120'],
            'banner_alt_text' => ['nullable', 'string', 'max:255'],
            'social_media_image' => ['nullable', 'image', 'max:5120'],
            'social_media_alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
