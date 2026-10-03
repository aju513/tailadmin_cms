<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;

class EditVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
