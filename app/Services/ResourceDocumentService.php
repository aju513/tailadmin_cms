<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\ResourceDocument;
use App\Repositories\Contracts\ResourceDocumentRepositoryInterface;
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

class ResourceDocumentService
{
    public function __construct(private readonly ResourceDocumentRepositoryInterface $records, private readonly MediaAssetService $media, private readonly ResourceCategoryService $categories) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->records->paginate($filters);
    }

    public function categoryOptions(): array
    {
        return $this->categories->options();
    }

    public function forPage(\App\Models\Page $page): ?LengthAwarePaginator
    {
        if ($page->page_type !== \App\Enums\PageType::Resource) {
            return null;
        }

        return $this->records->published($page->resource_category_id);
    }

    public function publicDetails(string $slug): ResourceDocument
    {
        return $this->records->publicBySlug($slug);
    }

    public function download(string $slug): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $record = $this->records->publicBySlug($slug);
        $file = $record->fileMedia;
        abort_unless($file && Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function newRecord(): ResourceDocument
    {
        return new ResourceDocument(['status' => ContentStatus::Draft, 'sort_order' => 0]);
    }

    public function details(ResourceDocument $record): ResourceDocument
    {
        return $this->records->find($record->id);
    }

    public function save(array $data, Authenticatable $actor, ?ResourceDocument $record = null): ResourceDocument
    {
        Gate::forUser($actor)->authorize($record ? 'resources.edit' : 'resources.create');
        $uploads = [];

        try {
            return DB::transaction(function () use ($data, $actor, $record, &$uploads): ResourceDocument {
                $record = $record ? $this->records->lock($record) : null;
                $this->categories->lockSelection((int) $data['resource_category_id']);
                $status = ContentStatus::from($data['status']);
                if ($status === ContentStatus::Published || $record?->status === ContentStatus::Published) {
                    Gate::forUser($actor)->authorize('resources.publish');
                }
                $data['slug'] = Str::slug(($data['slug'] ?? null) ?: ($record?->slug ?: $data['title']));
                if ($data['slug'] === '' || $this->records->slugExists($data['slug'], $record)) {
                    throw ValidationException::withMessages(['slug' => 'Enter a unique URL slug using letters or numbers.']);
                }

                $attachment = Arr::pull($data, 'attachment');
                if ($attachment instanceof UploadedFile) {
                    $asset = $this->media->store($attachment, $actor, $data['title']);
                    $uploads[] = $asset;
                    $data['file_media_id'] = $asset->id;
                }
                if (! ($data['file_media_id'] ?? $record?->file_media_id)) {
                    throw ValidationException::withMessages(['attachment' => 'Upload a document attachment.']);
                }
                $data['created_by'] = $record?->created_by ?? $actor->getAuthIdentifier();
                $data['updated_by'] = $actor->getAuthIdentifier();
                $data['published_at'] = $status === ContentStatus::Published ? ($data['published_at'] ?? $record?->published_at ?? now()) : null;
                $data['published_by'] = $status === ContentStatus::Published ? ($record?->published_by ?? $actor->getAuthIdentifier()) : null;
                $saved = $record ? $this->records->update($record, $data) : $this->records->create($data);

                activity('content')->causedBy($actor)->performedOn($saved)
                    ->event($record ? 'resources.updated' : 'resources.created')
                    ->log($record ? 'Resource updated' : 'Resource created');

                return $saved;
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
    }

    public function delete(ResourceDocument $record, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('resources.delete');
        DB::transaction(function () use ($record, $actor): void {
            $record = $this->records->lock($record);
            if ($record->status === ContentStatus::Published) {
                Gate::forUser($actor)->authorize('resources.publish');
            }
            activity('content')->causedBy($actor)->performedOn($record)->event('resources.deleted')->log('Resource deleted');
            $this->records->delete($record);
        });
    }
}
