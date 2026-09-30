<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Models\GalleryAlbum;
use App\Models\Hall;
use App\Models\HomepageSlide;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\Notice;
use App\Models\Page;
use App\Models\ResourceDocument;
use App\Models\TeamMember;
use App\Models\Video;
use App\Repositories\Contracts\FrontendRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FrontendRepository implements FrontendRepositoryInterface
{
    private function query(string $type): Builder
    {
        $query = match ($type) {
            'pages' => Page::query()->with(['bannerMedia', 'socialMedia', 'resourceCategory', 'noticeCategory']),
            'news' => News::query()->with(['thumbnailMedia', 'bannerMedia', 'socialMedia', 'category' => fn ($q) => $q->where('status', true), 'author' => fn ($q) => $q->where('status', true)]),
            'notices' => Notice::query()->with('category', 'fileMedia')->whereHas('category', fn ($q) => $q->where('is_active', true)),
            'resources' => ResourceDocument::query()->with('category', 'fileMedia')->whereHas('fileMedia')->whereHas('category', fn ($q) => $q->where('is_active', true)),
            'halls' => Hall::query()->with('thumbnailMedia', 'bannerMedia', 'socialMedia'),
            'gallery' => GalleryAlbum::query()->withCount('photos')->with(['coverMedia', 'photos' => fn ($q) => $q->with('media')->limit(1)])->whereHas('photos.media'),
            'videos' => Video::query()->with('coverMedia'),
            'team' => TeamMember::query()->with('photoMedia', 'category')->where('is_active', true)->where(fn ($q) => $q->whereNull('category_id')->orWhereHas('category', fn ($c) => $c->where('status', true))),
            'slides' => HomepageSlide::query()->with('media')->whereHas('media'),
            default => abort(404),
        };
        if ($type === 'team') {
            return $query;
        }
        $query->where('status', ContentStatus::Published);
        if ($type === 'pages') {
            // Older published pages did not require a publication timestamp.
            $query->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
        } elseif ($type !== 'slides') {
            $query->whereNotNull('published_at')->where('published_at', '<=', now());
        }

        return $query;
    }

    public function page(string $path): Page
    {
        $page = $this->query('pages')->where('path', $path)->firstOrFail();
        $page->setRelation('children', $this->query('pages')->where('parent_id', $page->id)->orderBy('sort_order')->get());

        return $page;
    }

    public function findPage(string $path): ?Page
    {
        return $this->query('pages')->where('path', $path)->first();
    }

    public function pages(): Collection
    {
        return $this->query('pages')->orderBy('sort_order')->orderBy('path')->get();
    }

    public function listing(string $type, array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($type);
        if ($type === 'resources' && ! empty($filters['resource_category_id'])) {
            $query->where('resource_category_id', $filters['resource_category_id']);
        }
        if ($type === 'notices' && ! empty($filters['notice_category_id'])) {
            $query->where('notice_category_id', $filters['notice_category_id']);
        }
        if ($type === 'team' && ! empty($filters['team_category_id'])) {
            $query->where('category_id', $filters['team_category_id']);
        }
        if (in_array($type, ['news', 'notices', 'resources']) && ! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        return $this->ordered($query, $type)->paginate(12, ['*'], $filters['page_name'] ?? 'page')->withQueryString();
    }

    public function detail(string $type, string $key): Model
    {
        $record = $this->query($type)->where($type === 'team' ? 'id' : 'slug', $key)->firstOrFail();
        if ($type === 'gallery') {
            $record->load('photos.media');
        }
        if ($type === 'halls') {
            $record->load('galleryImages.mediaAsset');
        }
        if ($type === 'news') {
            $record->load(['tags' => fn ($q) => $q->where('status', true)]);
        }

        return $record;
    }

    private function ordered(Builder $query, string $type): Builder
    {
        return in_array($type, ['news', 'notices', 'resources']) ? $query->latest('published_at')->latest('id') : $query->orderBy('sort_order')->orderBy('id');
    }

    public function recent(string $type, int $limit = 4): Collection
    {
        return $this->ordered($this->query($type), $type)->limit($limit)->get();
    }

    public function teamCategories(): Collection
    {
        return \App\Models\TeamCategory::query()->where('status', true)->whereHas('members', fn ($q) => $q->where('is_active', true))->orderBy('sort_order')->pluck('name', 'id');
    }

    public function search(string $term): array
    {
        if ($term === '') {
            return [];
        }
        $results = [];
        foreach (['pages', 'news', 'notices', 'resources', 'halls', 'gallery', 'videos'] as $type) {
            $query = $this->query($type);
            $column = in_array($type, ['pages', 'halls']) ? 'title->'.app()->getLocale() : 'title';
            $results[$type] = $query->where($column, 'like', '%'.$term.'%')->limit(10)->get();
        }

        return $results;
    }

    public function sitemapCount(string $type): int
    {
        return $this->query($type)->count();
    }

    public function sitemapRecords(string $type, int $chunk): Collection
    {
        return $this->query($type)->setEagerLoads([])->reorder()->orderBy('id')->offset(($chunk - 1) * config('frontend.sitemap_chunk_size'))->limit(config('frontend.sitemap_chunk_size'))->get();
    }

    public function mediaForOptimization(): \Illuminate\Support\LazyCollection
    {
        return MediaAsset::query()->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->lazyById(100);
    }
}
