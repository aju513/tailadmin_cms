<?php

namespace App\Http\Requests\HomepageSlide;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkHomepageSlideStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('homepage-slides.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('homepage_slides', 'id')],
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
