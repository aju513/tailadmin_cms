<?php

namespace App\Http\Requests\Front\Notice;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ShowPublicNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
