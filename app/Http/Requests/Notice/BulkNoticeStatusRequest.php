<?php

namespace App\Http\Requests\Notice;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNoticeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notices.publish') ?? false;
    }

    public function rules(): array
    {
        return ['notices' => ['required', 'array', 'min:1'], 'notices.*' => ['integer', 'distinct', Rule::exists('notices', 'id')], 'status' => ['required', Rule::enum(ContentStatus::class)]];
    }
}
