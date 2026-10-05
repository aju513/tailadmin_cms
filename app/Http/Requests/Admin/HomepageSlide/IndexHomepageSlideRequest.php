<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use Illuminate\Foundation\Http\FormRequest;

class IndexHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.manage') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
