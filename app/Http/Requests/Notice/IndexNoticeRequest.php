<?php

namespace App\Http\Requests\Notice;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(ContentStatus::class)]];
    }
}
