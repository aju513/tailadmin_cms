<?php

namespace App\Http\Requests\Notice;

use App\Enums\NoticeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class IndexPublicNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['notice_type' => ['nullable', Rule::enum(NoticeType::class)], 'page' => ['nullable', 'integer', 'min:1', 'max:100000']];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
