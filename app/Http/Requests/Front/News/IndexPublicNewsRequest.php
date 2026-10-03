<?php

namespace App\Http\Requests\Front\News;

use Illuminate\Foundation\Http\FormRequest;

class IndexPublicNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100']];
    }
}
