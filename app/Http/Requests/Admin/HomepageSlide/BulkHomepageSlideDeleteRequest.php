<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkHomepageSlideDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('homepage_slides', 'id')],

        ];
    }
}
