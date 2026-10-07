<?php

namespace App\Http\Requests\Admin\Popup;

use Illuminate\Foundation\Http\FormRequest;

class IndexPopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('popups.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:255']];
    }
}
