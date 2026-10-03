<?php

namespace App\Http\Requests\Admin\News;

use Illuminate\Foundation\Http\FormRequest;

class PublishNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.publish') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
