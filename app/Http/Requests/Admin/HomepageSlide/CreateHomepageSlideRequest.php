<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;

class CreateHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
