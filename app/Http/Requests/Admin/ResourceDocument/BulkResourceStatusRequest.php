<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkResourceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.publish') ?? false;
    }

    public function rules(): array
    {
        return ['resources' => ['required', 'array', 'min:1'], 'resources.*' => ['integer', 'distinct', Rule::exists('resource_documents', 'id')], 'status' => ['required', Rule::enum(ContentStatus::class)]];
    }
}
