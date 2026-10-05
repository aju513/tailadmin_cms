<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SaveResourceDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        $record = $this->route('resourceDocument');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('resource_documents', 'slug')->ignore($record?->id)],
            'description' => ['nullable', 'string', 'max:10000'],
            'resource_category_id' => ['required', 'integer', 'exists:resource_categories,id'],
            'attachment' => UploadProfile::rules('uploads.document', Rule::requiredIf(! $record?->file_media_id)),
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (($this->input('status') === 'published' || $this->route('resourceDocument')?->status === ContentStatus::Published)
                && ! $this->user()->can('resources.publish')) {
                $validator->errors()->add('status', 'Publishing or changing published content requires publishing permission.');
            }
        }];
    }
}
