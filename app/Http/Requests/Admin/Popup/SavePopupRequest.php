<?php

namespace App\Http\Requests\Admin\Popup;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePopupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $popup = $this->route('popup');

        return ($this->user()?->can($popup ? 'popups.edit' : 'popups.create') ?? false) && (($popup?->status !== ContentStatus::Published && $this->input('status') !== 'published') || ($this->user()?->can('popups.publish') ?? false));
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'alt_text' => ['nullable', 'string', 'max:255'], 'message' => ['nullable', 'string', 'max:5000'], 'image' => UploadProfile::rules('images.popup', $this->route('popup') ? 'nullable' : 'required'), 'button_label' => ['nullable', 'required_with:button_url', 'string', 'max:100'], 'button_url' => ['nullable', 'required_with:button_label', 'url:http,https', 'max:2048'], 'status' => ['required', Rule::enum(ContentStatus::class)]];
    }
}
