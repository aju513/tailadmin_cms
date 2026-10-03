<?php

namespace App\Http\Requests\Admin\News;

use Illuminate\Foundation\Http\FormRequest;

class ShowNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
