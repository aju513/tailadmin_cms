<?php

namespace App\Http\Requests\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;

class CreateGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
