<?php

namespace App\Repositories\Contracts;

use App\Models\GalleryAlbum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GalleryAlbumRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function details(GalleryAlbum $record): GalleryAlbum;

    public function lock(GalleryAlbum $record): GalleryAlbum;

    public function slugExists(string $slug, ?GalleryAlbum $record): bool;

    public function create(array $data): GalleryAlbum;

    public function update(GalleryAlbum $record, array $data): GalleryAlbum;

    public function delete(GalleryAlbum $record): void;

    public function photoCount(GalleryAlbum $record): int;

    public function savePhotos(GalleryAlbum $record, array $photos, array $removeIds): void;

    public function addPhoto(GalleryAlbum $record, int $mediaId): void;

    public function lockByIds(array $ids): \Illuminate\Database\Eloquent\Collection;
}
