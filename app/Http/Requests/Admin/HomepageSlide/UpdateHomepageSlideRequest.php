<?php

namespace App\Http\Requests\Admin\HomepageSlide;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.edit') ?? false;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'subtitle' => ['nullable', 'string', 'max:500'], 'link_url' => ['nullable', 'url', 'max:1000'], 'image' => UploadProfile::rules('images.homepage_slide'), 'status' => ['required', Rule::enum(ContentStatus::class)]];
    }
}
