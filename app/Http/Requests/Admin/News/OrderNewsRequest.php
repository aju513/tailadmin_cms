<?php

namespace App\Http\Requests\Admin\News;

use Illuminate\Foundation\Http\FormRequest;

class OrderNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.edit') ?? false;
    }

    public function rules(): array
    {
        return ['news' => ['required', 'array', 'min:1'], 'news.*' => ['required', 'integer', 'distinct', 'exists:news,id']];
    }
}
