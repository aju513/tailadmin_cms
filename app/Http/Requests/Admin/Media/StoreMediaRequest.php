<?php

namespace App\Http\Requests\Admin\Media;

use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return ['file' => UploadProfile::rules('uploads.media', 'required'), 'title' => ['nullable', 'string', 'max:255'], 'alt_text' => ['nullable', 'string', 'max:255']];
    }
}
