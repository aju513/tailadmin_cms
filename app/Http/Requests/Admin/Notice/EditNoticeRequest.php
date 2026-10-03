<?php

namespace App\Http\Requests\Admin\Notice;

use Illuminate\Foundation\Http\FormRequest;

class EditNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
