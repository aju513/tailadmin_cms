<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Hall;
use App\Models\MediaAsset;
use App\Repositories\Contracts\HallRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class HallService
{
    public function __construct(private readonly HallRepositoryInterface $halls, private readonly MediaAssetService $media) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->halls->paginateAdmin($filters);
    }

    public function details(Hall $hall): Hall
    {
        return $this->halls->details($hall);
    }

    public function newHall(): Hall
    {
        return new Hall(['status' => ContentStatus::Draft, 'availability_status' => 'available', 'sort_order' => 0, 'amenities' => []]);
    }

    public function save(array $data, Authenticatable $actor, ?Hall $hall = null): Hall
    {
        Gate::forUser($actor)->authorize($hall ? 'halls.edit' : 'halls.create');
        $uploaded = [];

        try {
            return DB::transaction(function () use ($data, $actor, $hall, &$uploaded): Hall {
                $hall = $hall ? $this->halls->lock($hall) : null;
                $translations = Arr::pull($data, 'translations');
                $gallery = Arr::pull($data, 'gallery_images', []) ?? [];
                $removeGallery = Arr::pull($data, 'remove_gallery_ids', []);
                $data['slug'] = Str::slug(($data['slug'] ?? null) ?: ($hall?->slug ?: $translations['en']['title']));
                if ($data['slug'] === '' || $this->halls->slugExists($data['slug'], $hall)) {
                    throw ValidationException::withMessages(['slug' => $data['slug'] === '' ? 'Enter a URL slug using letters or numbers.' : 'That URL slug is already in use.']);
                }

                $status = ContentStatus::from($data['status']);
                if ($status === ContentStatus::Published || $hall?->status === ContentStatus::Published) {
                    Gate::forUser($actor)->authorize('halls.publish');
                }
                foreach (['title', 'summary', 'body', 'booking_instructions'] as $field) {
                    $values = $hall?->getTranslations($field) ?? [];
                    $values['en'] = $translations['en'][$field] ?? '';
                    if (config('settings.nepali')) {
                        unset($values['ne']);
                        if (filled($translations['ne'][$field] ?? null)) {
                            $values['ne'] = $translations['ne'][$field];
                        }
                    }
                    $data[$field] = $values;
                }
                $data['published_at'] = $status === ContentStatus::Published ? ($data['published_at'] ?? $hall?->published_at ?? now()) : null;
                $data['published_by'] = $status === ContentStatus::Published ? ($hall?->published_by ?? $actor->getAuthIdentifier()) : null;
                $data['created_by'] = $hall?->created_by ?? $actor->getAuthIdentifier();
                $data['updated_by'] = $actor->getAuthIdentifier();
                if (($data['rental_rate'] ?? null) === null) {
                    $data['rate_unit'] = null;
                }

                if ($hall) {
                    $this->halls->removeGalleryImages($hall, $removeGallery);
                }
                $existing = $hall ? $this->halls->galleryCount($hall) : 0;
                if ($existing + count($gallery) > config('halls.gallery_limit')) {
                    throw ValidationException::withMessages(['gallery_images' => 'A hall can have at most '.config('halls.gallery_limit').' gallery images. Remove existing images before adding more.']);
                }

                foreach (['thumbnail' => 'thumbnail_media_id', 'banner_image' => 'banner_media_id', 'social_media_image' => 'social_media_id'] as $input => $column) {
                    if (! empty($data['remove_'.$input])) {
                        $data[$column] = null;
                    }
                    if (($data[$input] ?? null) instanceof UploadedFile) {
                        $asset = $this->media->store($data[$input], $actor, $data['title']['en'], $data[$input.'_alt_text'] ?? $data['title']['en']);
                        $uploaded[] = $asset;
                        $data[$column] = $asset->id;
                    }
                    unset($data[$input], $data[$input.'_alt_text'], $data['remove_'.$input]);
                }

                $saved = $hall ? $this->halls->update($hall, $data) : $this->halls->create($data);
                foreach ($gallery as $file) {
                    $asset = $this->media->store($file, $actor, $data['title']['en'], $data['title']['en']);
                    $uploaded[] = $asset;
                    $this->halls->addGalleryImage($saved, $asset->id);
                }
                activity('content')->causedBy($actor)->performedOn($saved)->event($hall ? 'hall.updated' : 'hall.created')
                    ->withProperties(['hall_id' => $saved->id, 'slug' => $saved->slug, 'status' => $saved->status->value, 'availability_status' => $saved->availability_status])
                    ->log($hall ? 'Hall updated' : 'Hall created');

                return $saved;
            });
        } catch (Throwable $exception) {
            // Database rollback cannot remove newly uploaded files.
            foreach ($uploaded as $asset) {
                $this->removeFailedUpload($asset);
            }
            throw $exception;
        }
    }

    public function delete(Hall $hall, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('halls.delete');
        DB::transaction(function () use ($hall, $actor): void {
            activity('content')->causedBy($actor)->performedOn($hall)->event('hall.deleted')
                ->withProperties(['hall_id' => $hall->id, 'slug' => $hall->slug])->log('Hall deleted');
            $this->halls->delete($hall);
        });
    }

    private function removeFailedUpload(MediaAsset $asset): void
    {
        Storage::disk($asset->disk)->delete($asset->path);
    }
}
