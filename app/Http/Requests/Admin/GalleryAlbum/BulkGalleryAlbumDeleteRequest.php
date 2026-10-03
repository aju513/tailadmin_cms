<?php

namespace App\Http\Requests\Admin\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkGalleryAlbumDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('gallery_albums', 'id')],

        ];
    }
}
