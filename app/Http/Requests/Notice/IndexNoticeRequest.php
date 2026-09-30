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
        return ['notice_category_id' => ['nullable', 'integer', 'exists:notice_categories,id'], 'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(ContentStatus::class)]];
    }
}
