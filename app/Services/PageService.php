<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PageService
{
    public function __construct(private readonly PageRepositoryInterface $pages, private readonly MediaAssetService $media) {}

    public function create(array $data, Authenticatable $actor): Page
    {
        return DB::transaction(function () use ($data, $actor): Page {
            $data = $this->prepare($this->attachMedia($data, $actor), $actor);
            $this->assertPathAvailable($data['path']);
            $page = $this->pages->create($data);
            $this->record($actor, $page, 'page.created', 'Page created');
            if ($page->status === ContentStatus::Published) {
                $this->publish($page, $actor);
            }

            return $page;
        });
    }

    public function update(Page $page, array $data, Authenticatable $actor): Page
    {
        return DB::transaction(function () use ($page, $data, $actor): Page {
            $oldPath = $page->path;
            $descendants = $this->pages->descendants($page);
            $data = $this->prepare($this->attachMedia($data, $actor), $actor, $page);
            if ($data['path'] !== $oldPath) {
                $this->assertPathAvailable($data['path'], $page);
            }
            $page = $this->pages->update($page, $data);
            if ($data['path'] !== $oldPath) {
                foreach ($descendants as $descendant) {
                    $descendant->path = $page->path.'/'.Str::after($descendant->path, $oldPath.'/');
                    $descendant->save();
                }
            }
            $this->record($actor, $page, 'page.updated', 'Page updated');
            if ($page->status === ContentStatus::Published && ! $page->published_at) {
                $this->publish($page, $actor);
            }

            return $page;
        });
    }

    public function publish(Page $page, Authenticatable $actor): Page
    {
        $this->assertCanPublish($actor);
        $page = $this->pages->update($page, ['status' => ContentStatus::Published, 'published_at' => now(), 'published_by' => $actor->getAuthIdentifier()]);
        $this->record($actor, $page, 'page.published', 'Page published');

        return $page;
    }

    public function unpublish(Page $page, Authenticatable $actor): Page
    {
        $this->assertCanPublish($actor);
        $page = $this->pages->update($page, ['status' => ContentStatus::Draft, 'published_at' => null, 'published_by' => null]);
        $this->record($actor, $page, 'page.unpublished', 'Page unpublished');

        return $page;
    }

    public function delete(Page $page, Authenticatable $actor): void
    {
        DB::transaction(function () use ($page, $actor): void {
            $this->record($actor, $page, 'page.deleted', 'Page deleted');
            $this->pages->delete($page);
        });
    }

    /** @param array<int, int|string> $ids */
    public function reorder(array $ids, Authenticatable $actor): void
    {
        DB::transaction(function () use ($ids, $actor): void {
            $this->pages->reorder($ids);
            activity('content')->causedBy($actor)->event('page.reordered')->withProperties(['page_ids' => array_map('intval', $ids)])->log('Pages reordered');
        });
    }

    private function prepare(array $data, Authenticatable $actor, ?Page $page = null): array
    {
        $data['slug'] = Str::slug($data['title']);
        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['parent_id'] = $data['parent_id'] ?: null;
        if ($page && (int) $data['parent_id'] === $page->id) {
            throw ValidationException::withMessages(['parent_id' => 'A page cannot be its own parent.']);
        }
        if ($page && $data['parent_id'] && $this->pages->descendants($page)->pluck('id')->contains((int) $data['parent_id'])) {
            throw ValidationException::withMessages(['parent_id' => 'A page cannot be moved inside one of its descendants.']);
        }
        $parentPath = $data['parent_id'] ? Page::query()->findOrFail($data['parent_id'])->path : null;
        $data['path'] = $parentPath ? $parentPath.'/'.$data['slug'] : $data['slug'];
        $data['updated_by'] = $actor->getAuthIdentifier();
        $data['created_by'] ??= $actor->getAuthIdentifier();
        $data['status'] = $data['status'] ?? ContentStatus::Draft;
        $data['page_type'] = $data['page_type'] ?? PageType::Standard;
        if (($data['status'] instanceof ContentStatus ? $data['status'] : ContentStatus::from($data['status'])) === ContentStatus::Published) {
            $this->assertCanPublish($actor);
        }

        return Arr::only($data, (new Page)->getFillable());
    }

    private function attachMedia(array $data, Authenticatable $actor): array
    {
        foreach ([
            'banner_image' => ['id' => 'banner_media_id', 'alt' => 'banner_alt_text'],
            'social_media_image' => ['id' => 'social_media_id', 'alt' => 'social_media_alt_text'],
        ] as $fileKey => $mapping) {
            if (($data[$fileKey] ?? null) instanceof \Illuminate\Http\UploadedFile) {
                $asset = $this->media->store($data[$fileKey], $actor, $data['title'] ?? null, $data[$mapping['alt']] ?? null);
                $data[$mapping['id']] = $asset->id;
            }
        }

        return Arr::except($data, ['banner_image', 'banner_alt_text', 'social_media_image', 'social_media_alt_text']);
    }

    private function assertPathAvailable(string $path, ?Page $ignore = null): void
    {
        $query = Page::query()->where('path', $path);
        if ($ignore) {
            $query->whereKeyNot($ignore->id);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['slug' => 'That page path is already in use.']);
        }
    }

    private function assertCanPublish(Authenticatable $actor): void
    {
        if (! $actor->can('pages.publish')) {
            throw ValidationException::withMessages(['status' => 'You do not have permission to publish pages.']);
        }
    }

    private function record(Authenticatable $actor, Page $page, string $event, string $description): void
    {
        activity('content')->causedBy($actor)->performedOn($page)->event($event)->withProperties(['page_id' => $page->id, 'path' => $page->path])->log($description);
    }
}
