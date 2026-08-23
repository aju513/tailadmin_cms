<?php

namespace App\Http\Requests\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;

class DeleteHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
