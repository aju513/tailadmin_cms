<?php

namespace App\Http\Requests\GalleryAlbum;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkGalleryAlbumStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.publish') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('gallery_albums', 'id')],
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
