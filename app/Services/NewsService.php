<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\News;
use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsService
{
    public function __construct(private readonly NewsRepositoryInterface $news, private readonly MediaAssetService $media) {}

    public function save(array $data, Authenticatable $actor, ?News $item = null): News
    {
        return DB::transaction(function () use ($data, $actor, $item): News {
            $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['title']);
            if ($data['slug'] === '') {
                throw ValidationException::withMessages(['slug' => 'Enter a URL slug using letters or numbers.']);
            }
            if ($this->news->slugExists($data['slug'], $item)) {
                throw ValidationException::withMessages(['slug' => 'That URL slug is already in use.']);
            }

            $status = ContentStatus::from($data['status']);
            if ($status === ContentStatus::Published && ! $actor->can('news.publish')) {
                throw ValidationException::withMessages(['status' => 'You do not have permission to publish news.']);
            }
            $data['published_at'] = $status === ContentStatus::Published ? ($data['published_at'] ?? $item?->published_at ?? now()) : null;
            if ($status !== ContentStatus::Published || \Illuminate\Support\Carbon::parse($data['published_at'])->isFuture()) {
                $data['featured'] = false;
            }
            $data['published_by'] = $status === ContentStatus::Published ? ($item?->published_by ?? $actor->getAuthIdentifier()) : null;
            $data['updated_by'] = $actor->getAuthIdentifier();
            $data['created_by'] = $item?->created_by ?? $actor->getAuthIdentifier();
            $data = $this->attachMedia($data, $actor);

            if ($data['featured']) {
                $this->news->clearFeatured($item);
            }

            $saved = $item ? $this->news->update($item, $data) : $this->news->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($item ? 'news.updated' : 'news.created')
                ->withProperties(['news_id' => $saved->id, 'slug' => $saved->slug])->log($item ? 'News updated' : 'News created');

            return $saved;
        });
    }

    public function delete(News $item, Authenticatable $actor): void
    {
        DB::transaction(function () use ($item, $actor): void {
            activity('content')->causedBy($actor)->performedOn($item)->event('news.deleted')
                ->withProperties(['news_id' => $item->id, 'slug' => $item->slug])->log('News deleted');
            $this->news->delete($item);
        });
    }

    public function publish(News $item, Authenticatable $actor): News
    {
        $this->assertCanPublish($actor);
        $item = $this->news->update($item, ['status' => ContentStatus::Published, 'published_at' => now(), 'published_by' => $actor->getAuthIdentifier()]);
        activity('content')->causedBy($actor)->performedOn($item)->event('news.published')->log('News published');

        return $item;
    }

    public function unpublish(News $item, Authenticatable $actor): News
    {
        $this->assertCanPublish($actor);
        $item = $this->news->update($item, ['status' => ContentStatus::Draft, 'published_at' => null, 'published_by' => null]);
        activity('content')->causedBy($actor)->performedOn($item)->event('news.unpublished')->log('News unpublished');

        return $item;
    }

    public function bulkChangeStatus(array $ids, ContentStatus $status, Authenticatable $actor): void
    {
        if (! $actor->can('news.publish')) {
            throw ValidationException::withMessages(['status' => 'You do not have permission to publish news.']);
        }

        DB::transaction(function () use ($ids, $status, $actor): void {
            $items = $this->news->findByIds($ids);
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['news' => 'One or more selected news articles could not be found.']);
            }

            foreach ($items as $item) {
                $published = $status === ContentStatus::Published;
                $this->news->update($item, [
                    'status' => $status,
                    'published_at' => $published ? ($item->published_at ?? now()) : null,
                    'published_by' => $published ? ($item->published_by ?? $actor->getAuthIdentifier()) : null,
                    'featured' => $published ? $item->featured : false,
                    'updated_by' => $actor->getAuthIdentifier(),
                ]);
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        DB::transaction(function () use ($ids, $actor): void {
            $items = $this->news->findByIds($ids);
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['news' => 'One or more selected news articles could not be found.']);
            }

            foreach ($items as $item) {
                $this->delete($item, $actor);
            }
        });
    }

    public function reorder(array $ids, Authenticatable $actor): void
    {
        DB::transaction(function () use ($ids): void {
            $items = $this->news->findByIds($ids);
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['news' => 'One or more news articles could not be found.']);
            }

            $this->news->reorder($ids);
        });
    }

    private function assertCanPublish(Authenticatable $actor): void
    {
        if (! $actor->can('news.publish')) {
            throw ValidationException::withMessages(['status' => 'You do not have permission to publish news.']);
        }
    }

    private function attachMedia(array $data, Authenticatable $actor): array
    {
        foreach (['thumbnail' => 'thumbnail_media_id', 'banner_image' => 'banner_media_id', 'social_media_image' => 'social_media_id'] as $input => $column) {
            if (($data[$input] ?? null) instanceof \Illuminate\Http\UploadedFile) {
                $asset = $this->media->store($data[$input], $actor, $data['title'], $data[match ($input) {
                    'thumbnail' => 'thumbnail_alt_text',
                    'banner_image' => 'banner_alt_text',
                    default => 'social_media_alt_text',
                }] ?? null);
                $data[$column] = $asset->id;
            }
        }

        return Arr::except($data, ['thumbnail', 'thumbnail_alt_text', 'banner_image', 'banner_alt_text', 'social_media_image', 'social_media_alt_text']);
    }
}
