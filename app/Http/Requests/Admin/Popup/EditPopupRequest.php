<?php

namespace App\Http\Requests\Admin\Popup;

use Illuminate\Foundation\Http\FormRequest;

class EditPopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('popups.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
