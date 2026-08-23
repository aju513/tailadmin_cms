<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
