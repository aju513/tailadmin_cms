<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;
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

class NoticeService
{
    public function __construct(private readonly NoticeRepositoryInterface $records, private readonly MediaAssetService $media, private readonly NoticeCategoryService $categories) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->records->paginateAdmin($filters);
    }

    public function categoryOptions(bool $activeOnly = false): array
    {
        return $this->categories->options($activeOnly);
    }

    public function publicIndex(array $filters = []): LengthAwarePaginator
    {
        $categoryId = filled($filters['notice_category_id'] ?? null) ? (int) $filters['notice_category_id'] : null;

        return $this->records->paginatePublished($categoryId);
    }

    public function publicDetails(string $slug): Notice
    {
        return $this->records->publishedBySlug($slug);
    }

    public function forPage(\App\Models\Page $page): ?LengthAwarePaginator
    {
        return $page->page_type === \App\Enums\PageType::Notices
            ? $this->records->paginatePublished($page->notice_category_id, 'notices_page')
            : null;
    }

    public function newRecord(): Notice
    {
        return new Notice(['status' => ContentStatus::Draft, 'notice_category_id' => array_key_first($this->categories->options(true)), 'sort_order' => 0]);
    }

    public function details(Notice $record): Notice
    {
        return $this->records->details($record);
    }

    public function save(array $data, Authenticatable $actor, ?Notice $record = null): Notice
    {
        Gate::forUser($actor)->authorize($record ? 'notices.edit' : 'notices.create');
        $uploads = [];

        try {
            return DB::transaction(function () use ($data, $actor, $record, &$uploads): Notice {
                $record = $record ? $this->records->lock($record) : null;
                $this->categories->lockSelection((int) $data['notice_category_id']);
                $status = ContentStatus::from($data['status']);
                if ($status === ContentStatus::Published || $record?->status === ContentStatus::Published) {
                    Gate::forUser($actor)->authorize('notices.publish');
                }
                $data['slug'] = Str::slug(($data['slug'] ?? null) ?: ($record?->slug ?: $data['title']));
                if ($data['slug'] === '' || $this->records->slugExists($data['slug'], $record)) {
                    throw ValidationException::withMessages(['slug' => 'Enter a unique URL slug using letters or numbers.']);
                }

                $attachment = Arr::pull($data, 'file');
                if ($attachment instanceof UploadedFile) {
                    $asset = $this->media->store($attachment, $actor, $data['title'], $data['title']);
                    $uploads[] = $asset;
                    $data['file_media_id'] = $asset->id;
                }
                $data['sort_order'] ??= $record?->sort_order ?? $this->records->nextSortOrder();
                $data['created_by'] = $record?->created_by ?? $actor->getAuthIdentifier();
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['published_at'] = $status === ContentStatus::Published ? ($data['published_at'] ?? $record?->published_at ?? now()) : null;
                $data['published_by'] = $status === ContentStatus::Published ? ($record?->published_by ?? $actor->getAuthIdentifier()) : null;
                $saved = $record ? $this->records->update($record, $data) : $this->records->create($data);

                activity('content')->causedBy($actor)->performedOn($saved)
                    ->event($record ? 'notice.updated' : 'notice.created')
                    ->withProperties(['notice_id' => $saved->id, 'slug' => $saved->slug, 'notice_category_id' => $saved->notice_category_id])
                    ->log($record ? 'Notice updated' : 'Notice created');

                return $saved;
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
    }

    public function delete(Notice $record, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('notices.delete');
        DB::transaction(function () use ($record, $actor): void {
            $record = $this->records->lock($record);
            if ($record->status === ContentStatus::Published) {
                Gate::forUser($actor)->authorize('notices.publish');
            }
            activity('content')->causedBy($actor)->performedOn($record)->event('notice.deleted')
                ->withProperties(['notice_id' => $record->id])->log('Notice deleted');
            $this->records->delete($record);
        });
    }

    public function publish(Notice $record, Authenticatable $actor): Notice
    {
        Gate::forUser($actor)->authorize('notices.publish');
        $record = $this->records->update($record, ['status' => ContentStatus::Published, 'published_at' => now(), 'published_by' => $actor->getAuthIdentifier()]);
        activity('content')->causedBy($actor)->performedOn($record)->event('notice.published')->log('Notice published');

        return $record;
    }

    public function unpublish(Notice $record, Authenticatable $actor): Notice
    {
        Gate::forUser($actor)->authorize('notices.publish');
        $record = $this->records->update($record, ['status' => ContentStatus::Draft, 'published_at' => null, 'published_by' => null]);
        activity('content')->causedBy($actor)->performedOn($record)->event('notice.unpublished')->log('Notice unpublished');

        return $record;
    }

    public function bulkChangeStatus(array $ids, ContentStatus $status, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('notices.publish');
        DB::transaction(function () use ($ids, $status, $actor): void {
            $items = $this->records->findByIds($ids);
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['notices' => 'One or more selected notices could not be found.']);
            }
            foreach ($items as $item) {
                $status === ContentStatus::Published ? $this->publish($item, $actor) : $this->unpublish($item, $actor);
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        DB::transaction(function () use ($ids, $actor): void {
            $items = $this->records->findByIds($ids);
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['notices' => 'One or more selected notices could not be found.']);
            }
            foreach ($items as $item) {
                $this->delete($item, $actor);
            }
        });
    }

    public function reorder(array $ids): void
    {
        DB::transaction(fn () => $this->records->reorder($ids));
    }
}
