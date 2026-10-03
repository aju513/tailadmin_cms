<?php

namespace App\Http\Requests\Admin\Page;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(ContentStatus::class)], 'page_type' => ['nullable', Rule::enum(PageType::class)]];
    }
}
