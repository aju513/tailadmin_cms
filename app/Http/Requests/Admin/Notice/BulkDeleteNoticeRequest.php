<?php

namespace App\Http\Requests\Admin\Notice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.delete') ?? false;
    }

    public function rules(): array
    {
        return ['notices' => ['required', 'array', 'min:1'], 'notices.*' => ['integer', 'distinct', Rule::exists('notices', 'id')]];
    }
}
