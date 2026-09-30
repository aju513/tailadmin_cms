<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Repositories\Contracts\MediaAssetRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaAssetService
{
    public function __construct(private readonly MediaAssetRepositoryInterface $media) {}

    public function store(UploadedFile $file, mixed $actor, ?string $title = null, ?string $altText = null): MediaAsset
    {
        $disk = 'public';
        $path = $file->store('cms', $disk);

        if ($path === false) {
            throw \Illuminate\Validation\ValidationException::withMessages(['file' => 'The upload could not be stored. Please try again.']);
        }

        try {
            return $this->media->create(['disk' => $disk, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'title' => $title, 'alt_text' => $altText, 'uploaded_by' => $actor?->getAuthIdentifier()]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }

    public function delete(MediaAsset $asset): void
    {
        Storage::disk($asset->disk)->delete($asset->path);
        $asset->delete();
    }
}
