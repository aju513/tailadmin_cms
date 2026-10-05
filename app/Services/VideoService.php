<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Video;
use App\Repositories\Contracts\VideoRepositoryInterface;
use App\Services\Frontend\FrontendCache;
use App\Support\RecordOrder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class VideoService
{
    public function __construct(private readonly VideoRepositoryInterface $records, private readonly MediaAssetService $media, private readonly FrontendCache $cache) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->records->paginate($filters);
    }

    public function newRecord(): Video
    {
        return new Video(['status' => ContentStatus::Draft, 'sort_order' => 0]);
    }

    public function details(Video $record): Video
    {
        return $this->records->details($record);
    }

    public function save(array $data, Authenticatable $actor, ?Video $record = null): Video
    {
        Gate::forUser($actor)->authorize($record ? 'videos.edit' : 'videos.create');
        $uploads = [];

        try {
            return DB::transaction(function () use ($data, $actor, $record, &$uploads): Video {
                $record = $record ? $this->records->lock($record) : null;
                $status = ContentStatus::from($data['status']);
                if ($status === ContentStatus::Published || $record?->status === ContentStatus::Published) {
                    Gate::forUser($actor)->authorize('videos.publish');
                }
                $data['sort_order'] = $record?->sort_order ?? $this->records->nextSortOrder();
                $baseSlug = Str::limit(Str::slug($data['title']) ?: 'video', 240, '');
                $data['slug'] = $record?->slug ?: $baseSlug;
                if (! $record) {
                    $suffix = 2;
                    while ($this->records->slugExists($data['slug'], null)) {
                        $data['slug'] = $baseSlug.'-'.$suffix++;
                    }
                }

                $cover = Arr::pull($data, 'cover');
                if (Arr::pull($data, 'remove_cover', false)) {
                    $data['cover_media_id'] = null;
                }
                if ($cover instanceof UploadedFile) {
                    $asset = $this->media->store($cover, $actor, $data['title'], $data['title']);
                    $uploads[] = $asset;
                    $data['cover_media_id'] = $asset->id;
                }
                $data['created_by'] = $record?->created_by ?? $actor->getAuthIdentifier();
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['published_at'] = $status === ContentStatus::Published ? ($record?->published_at ?? now()) : null;
                $data['published_by'] = $status === ContentStatus::Published ? ($record?->published_by ?? $actor->getAuthIdentifier()) : null;
                $saved = $record ? $this->records->update($record, $data) : $this->records->create($data);

                activity('content')->causedBy($actor)->performedOn($saved)
                    ->event($record ? 'videos.updated' : 'videos.created')
                    ->log($record ? 'Video updated' : 'Video created');

                return $saved;
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
    }

    public function delete(Video $record, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('videos.delete');
        DB::transaction(function () use ($record, $actor): void {
            $record = $this->records->lock($record);
            if ($record->status === ContentStatus::Published) {
                Gate::forUser($actor)->authorize('videos.publish');
            }
            activity('content')->causedBy($actor)->performedOn($record)->event('videos.deleted')->log('Video deleted');
            $this->records->delete($record);
        });
    }

    public function reorder(array $ids, array $originalOrder, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('videos.edit');
        DB::transaction(function () use ($ids, $originalOrder, $actor): void {
            $ordered = RecordOrder::replace($ids, $originalOrder, $this->records->lockOrderedIds());
            $this->records->reorder($ordered);
            activity('content')->causedBy($actor)->event('videos.reordered')
                ->withProperties(['record_ids' => array_map('intval', $ids)])->log('Video order updated');
        });
        $this->cache->clear();
    }

    public function bulkStatus(array $ids, \App\Enums\ContentStatus $status, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('videos.publish');
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
                    ->event('videos.status-updated')->withProperties(['status' => $status->value])->log('Publication status updated');
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('videos.delete');
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
