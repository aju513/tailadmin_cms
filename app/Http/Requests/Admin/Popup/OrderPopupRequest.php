<?php

namespace App\Http\Requests\Admin\Popup;

use Illuminate\Foundation\Http\FormRequest;

class OrderPopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('popups.edit') ?? false) && $this->user()?->can('popups.publish');
    }

    public function rules(): array
    {
        return ['records' => ['required', 'array', 'min:1'], 'records.*' => ['required', 'integer', 'distinct', 'exists:popups,id'], 'original_order' => ['required', 'array', 'min:1'], 'original_order.*' => ['required', 'integer', 'distinct', 'exists:popups,id']];
    }
}
