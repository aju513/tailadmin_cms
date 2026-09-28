<?php

namespace App\Http\Requests\News;

use Illuminate\Foundation\Http\FormRequest;

class DeleteNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
