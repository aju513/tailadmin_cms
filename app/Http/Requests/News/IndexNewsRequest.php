<?php

namespace App\Http\Requests\News;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(ContentStatus::class)]];
    }
}
