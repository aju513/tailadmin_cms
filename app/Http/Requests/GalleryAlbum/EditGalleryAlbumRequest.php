<?php

namespace App\Http\Requests\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;

class EditGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
