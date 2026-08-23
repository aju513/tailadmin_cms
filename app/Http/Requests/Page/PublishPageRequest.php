<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;

class PublishPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.publish') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
