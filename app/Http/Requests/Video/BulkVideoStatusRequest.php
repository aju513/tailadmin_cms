<?php

namespace App\Http\Requests\Video;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkVideoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.publish') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('videos', 'id')],
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
