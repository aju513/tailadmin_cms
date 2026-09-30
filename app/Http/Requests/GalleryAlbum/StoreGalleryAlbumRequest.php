<?php

namespace App\Http\Requests\GalleryAlbum;

class StoreGalleryAlbumRequest extends SaveGalleryAlbumRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.create') ?? false;
    }
}
