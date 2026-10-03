<?php

namespace App\Http\Requests\Admin\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;

class DeleteGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
