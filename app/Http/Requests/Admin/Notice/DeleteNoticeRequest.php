<?php

namespace App\Http\Requests\Admin\Notice;

use Illuminate\Foundation\Http\FormRequest;

class DeleteNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
