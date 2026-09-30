<?php

namespace App\Http\Requests\Front;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SitemapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        return [...parent::validationData(), ...$this->route()->parameters()];
    }

    public function rules(): array
    {
        return ['type' => ['sometimes', Rule::in(['pages', 'news', 'notices', 'resources', 'halls', 'gallery', 'videos', 'team'])], 'chunk' => ['sometimes', 'integer', 'min:1', 'max:100000']];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
