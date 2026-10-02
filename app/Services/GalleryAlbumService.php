<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\GalleryAlbum;
use App\Repositories\Contracts\GalleryAlbumRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GalleryAlbumService
{
    public function __construct(private readonly GalleryAlbumRepositoryInterface $records, private readonly MediaAssetService $media) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->records->paginate($filters);
    }

    public function newRecord(): GalleryAlbum
    {
        return new GalleryAlbum(['status' => ContentStatus::Draft, 'sort_order' => 0]);
    }

    public function details(GalleryAlbum $record): GalleryAlbum
    {
        return $this->records->details($record);
    }

    public function save(array $data, Authenticatable $actor, ?GalleryAlbum $record = null): GalleryAlbum
    {
        Gate::forUser($actor)->authorize($record ? 'gallery.edit' : 'gallery.create');
        $uploads = [];

        try {
            return DB::transaction(function () use ($data, $actor, $record, &$uploads): GalleryAlbum {
                $record = $record ? $this->records->lock($record) : null;
                $status = ContentStatus::from($data['status']);
                if ($status === ContentStatus::Published || $record?->status === ContentStatus::Published) {
                    Gate::forUser($actor)->authorize('gallery.publish');
                }
                $data['slug'] = Str::slug(($data['slug'] ?? null) ?: ($record?->slug ?: $data['title']));
                if ($data['slug'] === '' || $this->records->slugExists($data['slug'], $record)) {
                    throw ValidationException::withMessages(['slug' => 'Enter a unique URL slug using letters or numbers.']);
                }

                $removeIds = Arr::pull($data, 'remove_photo_ids', []);
                $newPhotos = Arr::pull($data, 'new_photos', []) ?? [];
                if ($record) {
                    $this->records->savePhotos($record, [], $removeIds);
                }
                if (($record ? $this->records->photoCount($record) : 0) + count($newPhotos) > 100) {
                    throw ValidationException::withMessages(['new_photos' => 'A gallery can contain at most 100 images.']);
                }

                $data['created_by'] = $record?->created_by ?? $actor->getAuthIdentifier();
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['published_at'] = $status === ContentStatus::Published ? ($record?->published_at ?? now()) : null;
                $data['published_by'] = $status === ContentStatus::Published ? ($record?->published_by ?? $actor->getAuthIdentifier()) : null;
                $saved = $record ? $this->records->update($record, $data) : $this->records->create($data);

                foreach ($newPhotos as $file) {
                    $asset = $this->media->store($file, $actor, $saved->title, $saved->title);
                    $uploads[] = $asset;
                    $this->records->addPhoto($saved, $asset->id);
                }

                activity('content')->causedBy($actor)->performedOn($saved)
                    ->event($record ? 'gallery.updated' : 'gallery.created')
                    ->log($record ? 'Gallery updated' : 'Gallery created');

                return $saved;
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
    }

    public function delete(GalleryAlbum $record, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('gallery.delete');
        DB::transaction(function () use ($record, $actor): void {
            $record = $this->records->lock($record);
            if ($record->status === ContentStatus::Published) {
                Gate::forUser($actor)->authorize('gallery.publish');
            }
            activity('content')->causedBy($actor)->performedOn($record)->event('gallery.deleted')->log('Gallery deleted');
            $this->records->delete($record);
        });
    }

    public function bulkStatus(array $ids, \App\Enums\ContentStatus $status, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('gallery.publish');
        DB::transaction(function () use ($ids, $status, $actor): void {
            $records = $this->records->lockByIds($ids);
            if ($records->count() !== count($ids)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['records' => 'One or more selected records could not be found.']);
            }
            foreach ($records as $record) {
                $data = ['status' => $status, 'updated_by' => $actor->getAuthIdentifier()];
                $published = $status === \App\Enums\ContentStatus::Published;
                $data['published_at'] = $published ? now() : null;
                $data['published_by'] = $published ? $actor->getAuthIdentifier() : null;
                $this->records->update($record, $data);
                activity('content')->causedBy($actor)->performedOn($record)
                    ->event('gallery.status-updated')->withProperties(['status' => $status->value])->log('Publication status updated');
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('gallery.delete');
        DB::transaction(function () use ($ids, $actor): void {
            $records = $this->records->lockByIds($ids);
            if ($records->count() !== count($ids)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['records' => 'One or more selected records could not be found.']);
            }
            foreach ($records as $record) {
                $this->delete($record, $actor);
            }
        });
    }
}
