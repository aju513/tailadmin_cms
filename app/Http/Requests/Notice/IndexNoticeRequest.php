<?php

namespace App\Http\Requests\Notice;

use App\Enums\ContentStatus;
use App\Enums\PageType;
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
        return ['notice_page_id' => ['nullable', 'integer', Rule::exists('pages', 'id')->where('page_type', PageType::Notices->value)], 'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(ContentStatus::class)]];
    }
}
