<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;

class EditHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
