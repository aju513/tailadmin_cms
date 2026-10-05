<?php

namespace App\Http\Requests\Admin\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;

class OrderGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'records.*' => ['required', 'integer', 'distinct', 'exists:gallery_albums,id'],
            'original_order' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'original_order.*' => ['required', 'integer', 'distinct', 'exists:gallery_albums,id'],
        ];
    }
}
