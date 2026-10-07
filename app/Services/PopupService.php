<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Popup;
use App\Repositories\Contracts\PopupRepositoryInterface;
use App\Services\Frontend\FrontendCache;
use App\Support\RecordOrder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PopupService
{
    public function __construct(private readonly PopupRepositoryInterface $popups, private readonly MediaAssetService $media, private readonly FrontendCache $cache) {}

    public function index(array $filters): Collection
    {
        return $this->popups->listing($filters);
    }

    public function newRecord(): Popup
    {
        return new Popup(['status' => ContentStatus::Draft]);
    }

    public function details(Popup $popup): Popup
    {
        return $this->popups->details($popup);
    }

    public function active(): ?Popup
    {
        foreach ($this->popups->published() as $popup) {
            if (Storage::disk($popup->media->disk)->exists($popup->media->path)) {
                return $popup;
            }
        }

        return null;
    }

    private function authorizePublication(Authenticatable $actor, ?Popup $popup, ?string $status = null): void
    {
        if ($popup?->status === ContentStatus::Published || $status === ContentStatus::Published->value) {
            Gate::forUser($actor)->authorize('popups.publish');
        }
    }

    public function save(array $data, Authenticatable $actor, ?Popup $popup = null): Popup
    {
        Gate::forUser($actor)->authorize($popup ? 'popups.edit' : 'popups.create');
        $asset = null;
        try {
            $saved = DB::transaction(function () use ($data, $actor, $popup, &$asset): Popup {
                $popup = $popup ? $this->popups->lock($popup) : null;
                $this->authorizePublication($actor, $popup, $data['status'] ?? 'draft');
                if ($data['image'] ?? null) {
                    $asset = $this->media->store($data['image'], $actor, $data['title'], $data['alt_text'] ?? $data['title']);
                    $data['media_id'] = $asset->id;
                }
                unset($data['image']);
                if (! $asset && (! $popup || ! $this->popups->details($popup)->media)) {
                    throw ValidationException::withMessages(['image' => 'An image is required.']);
                }
                $data['sort_order'] = $popup?->sort_order ?? $this->popups->nextSortOrder();
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['created_by'] = $popup?->created_by ?? $actor->getAuthIdentifier();
                $saved = $popup ? $this->popups->update($popup, $data) : $this->popups->create($data);
                activity('content')->causedBy($actor)->performedOn($saved)->event($popup ? 'popup.updated' : 'popup.created')->log($popup ? 'Popup updated' : 'Popup created');

                return $saved;
            });
        } catch (Throwable $exception) {
            if ($asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
        $this->cache->clear();

        return $saved;
    }

    public function delete(Popup $popup, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('popups.delete');
        DB::transaction(function () use ($popup, $actor): void {
            $popup = $this->popups->lock($popup);
            $this->authorizePublication($actor, $popup);
            $this->popups->delete($popup);
            activity('content')->causedBy($actor)->event('popup.deleted')->withProperties(['popup_id' => $popup->id])->log('Popup deleted');
        });
        $this->cache->clear();
    }

    public function reorder(array $ids, array $originalOrder, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('popups.edit');
        Gate::forUser($actor)->authorize('popups.publish');
        DB::transaction(function () use ($ids, $originalOrder, $actor): void {
            $all = $this->popups->lockOrderedIds();
            if (count($all) !== count($originalOrder)) {
                throw ValidationException::withMessages(['records' => 'The list changed. Reload before reordering.']);
            }
            $ordered = RecordOrder::replace($ids, $originalOrder, $all);
            $this->popups->reorder($ordered);
            activity('content')->causedBy($actor)->event('popups.reordered')->withProperties(['record_ids' => $ordered])->log('Popup priority updated');
        });
        $this->cache->clear();
    }
}
