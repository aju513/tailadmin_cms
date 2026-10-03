<?php

namespace App\Http\Requests\Admin\Notice;

use Illuminate\Foundation\Http\FormRequest;

class OrderNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.edit') ?? false;
    }

    public function rules(): array
    {
        return ['notices' => ['required', 'array', 'min:1'], 'notices.*' => ['required', 'integer', 'distinct', 'exists:notices,id']];
    }
}
