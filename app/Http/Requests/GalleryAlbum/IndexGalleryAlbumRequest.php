<?php

namespace App\Http\Requests\GalleryAlbum;

use Illuminate\Foundation\Http\FormRequest;

class IndexGalleryAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gallery.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:255']];
    }
}
