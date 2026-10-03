<?php

namespace App\Http\Requests\Admin\Video;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SaveVideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'cover' => UploadProfile::rules('images.video_cover'),
            'remove_cover' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'video_url' => ['required', 'url:http,https', 'max:2048'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (($this->input('status') === 'published' || $this->route('video')?->status === ContentStatus::Published)
                && ! $this->user()->can('videos.publish')) {
                $validator->errors()->add('status', 'Publishing or changing published content requires publishing permission.');
            }
        }];
    }
}
