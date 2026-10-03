<?php

namespace App\Http\Requests\Admin\News;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.delete') ?? false;
    }

    public function rules(): array
    {
        return ['news' => ['required', 'array', 'min:1'], 'news.*' => ['integer', 'distinct', Rule::exists('news', 'id')]];
    }
}
