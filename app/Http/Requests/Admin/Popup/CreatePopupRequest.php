<?php

namespace App\Http\Requests\Admin\Popup;

use Illuminate\Foundation\Http\FormRequest;

class CreatePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('popups.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
