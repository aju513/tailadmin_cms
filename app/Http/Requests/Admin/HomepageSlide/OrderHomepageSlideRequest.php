<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;

class OrderHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'records.*' => ['required', 'integer', 'distinct', 'exists:homepage_slides,id'],
            'original_order' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'original_order.*' => ['required', 'integer', 'distinct', 'exists:homepage_slides,id'],
        ];
    }
}
