<?php

namespace App\Http\Requests\News;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNewsStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.publish') ?? false;
    }

    public function rules(): array
    {
        return ['news' => ['required', 'array', 'min:1'], 'news.*' => ['integer', 'distinct', Rule::exists('news', 'id')], 'status' => ['required', Rule::enum(ContentStatus::class)]];
    }
}
