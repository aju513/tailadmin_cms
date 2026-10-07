<?php

namespace App\Http\Requests\Admin\Popup;

use Illuminate\Foundation\Http\FormRequest;

class DeletePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('popups.delete') ?? false) && ($this->route('popup')->status !== \App\Enums\ContentStatus::Published || $this->user()?->can('popups.publish'));
    }

    public function rules(): array
    {
        return [];
    }
}
