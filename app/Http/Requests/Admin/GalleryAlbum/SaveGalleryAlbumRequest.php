<?php

namespace App\Http\Requests\Admin\GalleryAlbum;

use App\Enums\ContentStatus;
use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SaveGalleryAlbumRequest extends FormRequest
{
    public function rules(): array
    {
        $record = $this->route('galleryAlbum');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('gallery_albums', 'slug')->ignore($record?->id)],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'new_photos' => ['nullable', 'array', 'max:30'],
            'new_photos.*' => UploadProfile::rules('images.gallery_photo', 'required'),
            'remove_photo_ids' => ['sometimes', 'array'],
            'remove_photo_ids.*' => ['required', 'integer', 'distinct', Rule::exists('gallery_photos', 'id')->where('gallery_album_id', $record?->id ?? 0)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (($this->input('status') === 'published' || $this->route('galleryAlbum')?->status === ContentStatus::Published)
                && ! $this->user()->can('gallery.publish')) {
                $validator->errors()->add('status', 'Publishing or changing published content requires publishing permission.');
            }
        }];
    }
}
