<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\HomepageSlide;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use App\Services\Frontend\FrontendCache;
use App\Support\RecordOrder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HomepageSlideService
{
    public function __construct(private readonly HomepageSlideRepositoryInterface $slides, private readonly MediaAssetService $media, private readonly FrontendCache $cache) {}

    public function index(): LengthAwarePaginator
    {
        return $this->slides->paginateForIndex();
    }

    public function newRecord(): HomepageSlide
    {
        return new HomepageSlide(['status' => ContentStatus::Draft]);
    }

    public function details(HomepageSlide $slide): HomepageSlide
    {
        return $this->slides->details($slide);
    }

    public function save(array $data, Authenticatable $actor, ?HomepageSlide $slide = null): HomepageSlide
    {
        Gate::forUser($actor)->authorize($slide ? 'homepage-slides.edit' : 'homepage-slides.create');
        $asset = null;
        try {
            return DB::transaction(function () use ($data, $actor, $slide, &$asset): HomepageSlide {
                $slide = $slide ? $this->slides->lock($slide) : null;
                $data['sort_order'] = $slide?->sort_order ?? $this->slides->nextSortOrder();
                if ($data['image'] ?? null) {
                    $asset = $this->media->store($data['image'], $actor, $data['title'], $data['title']);
                    $data['media_id'] = $asset->id;
                }
                unset($data['image']);
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['created_by'] = $slide?->created_by ?? $actor->getAuthIdentifier();
                $saved = $slide ? $this->slides->update($slide, $data) : $this->slides->create($data);
                activity('content')->causedBy($actor)->performedOn($saved)->event($slide ? 'homepage-slide.updated' : 'homepage-slide.created')->log($slide ? 'Homepage slide updated' : 'Homepage slide created');

                return $saved;
            });
        } catch (Throwable $exception) {
            if ($asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
    }

    public function reorder(array $ids, array $originalOrder, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('homepage-slides.edit');
        DB::transaction(function () use ($ids, $originalOrder, $actor): void {
            $ordered = RecordOrder::replace($ids, $originalOrder, $this->slides->lockOrderedIds());
            $this->slides->reorder($ordered);
            activity('content')->causedBy($actor)->event('homepage-slides.reordered')
                ->withProperties(['record_ids' => array_map('intval', $ids)])->log('Home slide order updated');
        });
        $this->cache->clear();
    }

    public function delete(HomepageSlide $slide, Authenticatable $actor): void
    {
        $this->slides->delete($slide);
        activity('content')->causedBy($actor)->event('homepage-slide.deleted')->withProperties(['slide_id' => $slide->id])->log('Homepage slide deleted');
    }

    public function bulkStatus(array $ids, \App\Enums\ContentStatus $status, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('homepage-slides.edit');
        DB::transaction(function () use ($ids, $status, $actor): void {
            $records = $this->slides->lockByIds($ids);
            if ($records->count() !== count($ids)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['records' => 'One or more selected records could not be found.']);
            }
            foreach ($records as $record) {
                $data = ['status' => $status, 'updated_by' => $actor->getAuthIdentifier()];

                $this->slides->update($record, $data);
                activity('content')->causedBy($actor)->performedOn($record)
                    ->event('homepage-slides.status-updated')->withProperties(['status' => $status->value])->log('Publication status updated');
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        \Illuminate\Support\Facades\Gate::forUser($actor)->authorize('homepage-slides.delete');
        DB::transaction(function () use ($ids, $actor): void {
            $records = $this->slides->lockByIds($ids);
            if ($records->count() !== count($ids)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['records' => 'One or more selected records could not be found.']);
            }
            foreach ($records as $record) {
                $this->delete($record, $actor);
            }
        });
    }
}
