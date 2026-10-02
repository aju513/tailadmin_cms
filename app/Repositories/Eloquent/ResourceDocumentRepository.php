<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\ResourceDocument;
use App\Repositories\Contracts\ResourceDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ResourceDocumentRepository implements ResourceDocumentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return ResourceDocument::query()->with('category', 'fileMedia')->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
    }

    public function lock(ResourceDocument $record): ResourceDocument
    {
        return ResourceDocument::query()->whereKey($record->id)->lockForUpdate()->firstOrFail();
    }

    public function find(int $id): ResourceDocument
    {
        return ResourceDocument::query()->with('category', 'fileMedia')->findOrFail($id);
    }

    public function slugExists(string $slug, ?ResourceDocument $record): bool
    {
        return ResourceDocument::query()->where('slug', $slug)->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists();
    }

    public function create(array $data): ResourceDocument
    {
        return ResourceDocument::query()->create($data);
    }

    public function update(ResourceDocument $record, array $data): ResourceDocument
    {
        $record->update($data);

        return $record;
    }

    public function delete(ResourceDocument $record): void
    {
        $record->delete();
    }

    private function publicQuery(): Builder
    {
        return ResourceDocument::query()->with('category', 'fileMedia')
            ->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereHas('fileMedia')->whereHas('category', fn ($query) => $query->where('is_active', true));
    }

    public function published(?int $categoryId): LengthAwarePaginator
    {
        return $this->publicQuery()->when($categoryId, fn ($query) => $query->where('resource_category_id', $categoryId))
            ->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(12, ['*'], 'resources_page')->withQueryString();
    }

    public function publicBySlug(string $slug): ResourceDocument
    {
        return $this->publicQuery()->where('slug', $slug)->firstOrFail();
    }

    public function findByIds(array $ids): Collection
    {
        return ResourceDocument::query()->whereIn('id', $ids)->get();
    }

    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            ResourceDocument::query()->whereKey($id)->update(['sort_order' => $position]);
        }
    }
}
