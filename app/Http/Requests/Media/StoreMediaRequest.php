<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return ['file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:10240'], 'title' => ['nullable', 'string', 'max:255'], 'alt_text' => ['nullable', 'string', 'max:255']];
    }
}
