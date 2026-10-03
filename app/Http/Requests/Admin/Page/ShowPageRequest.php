<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;

class ShowPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
