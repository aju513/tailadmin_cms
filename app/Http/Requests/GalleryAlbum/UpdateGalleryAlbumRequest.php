<?php

namespace App\Http\Requests\GalleryAlbum;

class UpdateGalleryAlbumRequest extends SaveGalleryAlbumRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.edit') ?? false;
    }
}
