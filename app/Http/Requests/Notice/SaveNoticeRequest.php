<?php

namespace App\Http\Requests\Notice;

use App\Enums\ContentStatus;
use App\Enums\NoticeType;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SaveNoticeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['notice_type' => $this->input('notice_type', $this->route('notice')?->notice_type?->value ?? 'general'), 'status' => $this->input('status', $this->route('notice')?->status?->value ?? 'draft')]);
    }

    public function rules(): array
    {
        $u = Rule::unique('notices', 'slug');
        if ($this->route('notice')) {
            $u->ignore($this->route('notice')->id);
        }

        return ['notice_type' => ['required', Rule::enum(NoticeType::class)], 'deadline_at' => ['nullable', 'date'], 'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', $u], 'description' => ['nullable', 'string', 'max:20000'], 'sort_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'], 'status' => ['required', Rule::enum(ContentStatus::class)], 'published_at' => ['nullable', 'date'], 'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:10240'], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:1000']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (($this->input('status') === 'published' || $this->route('notice')?->status === ContentStatus::Published)
                && ! $this->user()->can('notices.publish')) {
                $validator->errors()->add('status', 'Publishing or changing published notices requires publishing permission.');
            }
        }];
    }
}
