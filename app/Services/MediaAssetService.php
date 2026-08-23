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

        return $this->media->create(['disk' => $disk, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'title' => $title, 'alt_text' => $altText, 'uploaded_by' => $actor?->getAuthIdentifier()]);
    }

    public function delete(MediaAsset $asset): void
    {
        Storage::disk($asset->disk)->delete($asset->path);
        $asset->delete();
    }
}
