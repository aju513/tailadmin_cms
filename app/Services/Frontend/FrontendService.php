<?php

namespace App\Services\Frontend;

use App\Enums\PageType;
use App\Repositories\Contracts\FrontendRepositoryInterface;
use App\Repositories\Contracts\HomepageContentRepositoryInterface;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Services\CapacityReportService;
use App\Services\NoticeService;
use App\Services\ResourceDocumentService;
use App\Services\SiteSettingService;

class FrontendService
{
    public function __construct(private readonly FrontendRepositoryInterface $content, private readonly FrontendLayoutService $layout, private readonly NewsRepositoryInterface $news, private readonly SeoService $seo, private readonly NoticeService $notices, private readonly ResourceDocumentService $resources, private readonly FrontendCache $cache, private readonly HomepageContentRepositoryInterface $homepage, private readonly CapacityReportService $capacityReports, private readonly PublicTrainingService $trainings) {}

    private function setLocale(array $filters): void
    {
        $locale = config('settings.nepali') ? ($filters['lang'] ?? request()->session()->get('page_locale', 'en')) : 'en';
        app()->setLocale($locale);
        if (config('settings.nepali') && isset($filters['lang'])) {
            request()->session()->put('page_locale', $locale);
        }
    }

    private function prepare(array $data, array $filters = []): array
    {
        $this->setLocale($filters);
        $data = [...$data, ...$this->layout->data()];
        if (($data['kind'] ?? null) === 'grievance') {
            $data['recaptchaConfigured'] = app(SiteSettingService::class)->recaptchaConfigured();
        }
        if (($data['kind'] ?? null) === 'home') {
            $data['trainingCatalogue'] = $this->trainings->homepage();
            $data['capacityReports'] = $this->cache->remember('capacity-reports', fn () => $this->capacityReports->publicReports());
            $data['homepageImages'] = $data['homepageContent']
                ? $data['homepageContent']->galleryImages->pluck('mediaAsset')->filter()->values()
                : $data['galleries']->map(fn ($gallery) => $gallery->photos->first()?->media)->filter()->values();
            $data['item'] = $data['homepageContent'];
            $data['heading'] = $data['settings']['site_name'];
        }
        if (isset($data['latestResources'])) {
            $data['resourceGroups'] = $data['latestResources']->groupBy('resource_category_id');
        }
        if (($data['kind'] ?? null) === 'team' && isset($data['items'])) {
            $data['teamCategories'] = $this->cache->remember('team.categories', fn () => $this->content->teamCategories());
        }
        if (($data['kind'] ?? null) === 'news' && isset($data['items'])) {
            $data['newsCategories'] = $this->cache->remember('news.categories', fn () => $this->news->activeCategories());
        }

        return [...$data, 'seo' => $this->seo->make($data)];
    }

    public function home(array $filters): array
    {
        $this->setLocale($filters);

        return $this->prepare($this->cache->remember('homepage', fn () => ['kind' => 'home', 'homepageContent' => $this->homepage->find(), 'slides' => $this->content->recent('slides', 12), 'latestNews' => $this->content->recent('news', 3), 'latestNotices' => $this->content->recent('notices', 4), 'latestResources' => $this->content->recent('resources', 24), 'galleries' => $this->content->recent('gallery', 8), 'halls' => $this->content->recent('halls', 1), 'team' => $this->content->recent('team', 4), 'videos' => $this->content->recent('videos', 2)]), $filters);
    }

    public function page(string $path, array $filters): array
    {
        $this->setLocale($filters);
        $page = $this->content->page($path);
        $kind = match ($page->page_type) {
            PageType::Grievance => 'grievance',PageType::News => 'news',PageType::Notices => 'notices',PageType::Resource => 'resources',PageType::Team => 'team',PageType::Hall => 'halls',PageType::Gallery => 'gallery',PageType::Videos => 'videos',PageType::ContactUs => 'contact',PageType::Sitemap => 'sitemap',default => 'article'
        };
        $data = ['kind' => $kind, 'page' => $page, 'heading' => $page->title];
        if ($kind === 'resources') {
            $data['items'] = $this->resources->forPage($page);
        } elseif ($kind === 'notices') {
            $data['items'] = $this->notices->forPage($page, $filters);
        } elseif (in_array($kind, ['news', 'team', 'halls', 'gallery', 'videos'])) {
            $data['items'] = $kind === 'news' ? $this->news->paginatePublished($filters) : $this->content->listing($kind, $filters);
        } elseif ($kind === 'sitemap') {
            $data['pages'] = $this->content->pages();
        }

        return $this->prepare($data, $filters);
    }

    public function listing(string $kind, array $filters): array
    {
        $this->setLocale($filters);
        if ($this->content->findPage($kind)) {
            return $this->page($kind, $filters);
        }
        $heading = ['news' => 'News', 'notices' => 'Notices', 'resources' => 'Resources', 'halls' => 'Our Halls', 'team' => 'Our Team', 'gallery' => 'Photo Gallery', 'videos' => 'Videos'][$kind] ?? abort(404);

        return $this->prepare(['kind' => $kind, 'heading' => $heading, 'items' => $this->content->listing($kind, $filters), 'categories' => $kind === 'notices' ? $this->notices->categoryOptions(true) : []], $filters);
    }

    public function newsListing(array $filters, ?string $taxonomy = null, ?string $slug = null): array
    {
        if (! $taxonomy && $this->content->findPage('news')) {
            return $this->page('news', $filters);
        }
        $record = match ($taxonomy) {
            'category' => $this->news->categoryBySlug($slug),'tag' => $this->news->tagBySlug($slug),'author' => $this->news->authorBySlug($slug),default => null
        };
        if ($record) {
            $filters[$taxonomy] = $slug;
        }

        return $this->prepare(['kind' => 'news', 'heading' => $record?->name ?? 'News', 'items' => $this->news->paginatePublished($filters)], $filters);
    }

    public function detail(string $kind, string $slug, array $filters): array
    {
        try {
            $item = $this->content->detail($kind, $slug);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            if ($this->content->findPage($kind.'/'.$slug)) {
                return [...$this->page($kind.'/'.$slug, $filters), 'view' => 'front.pages.page'];
            }
            throw $exception;
        }

        return $this->prepare(['kind' => $kind, 'item' => $item, 'relatedNews' => $kind === 'news' ? $this->news->latestPublished($item, 3) : collect(), 'recentNotices' => $kind === 'notices' ? $this->notices->recentPublished($item) : collect()], $filters);
    }

    public function search(array $filters): array
    {
        $this->setLocale($filters);
        $term = trim($filters['q'] ?? '');

        return $this->prepare(['kind' => 'search', 'heading' => 'Search', 'term' => $term, 'results' => $this->content->search($term)], $filters);
    }

    public function contact(array $filters): array
    {
        if ($this->content->findPage('contact')) {
            return $this->page('contact', $filters);
        }

        return $this->prepare(['kind' => 'contact', 'heading' => 'Contact Us'], $filters);
    }

    public function sitemap(array $filters): array
    {
        if ($this->content->findPage('sitemap')) {
            return $this->page('sitemap', $filters);
        }

        return $this->prepare(['kind' => 'sitemap', 'heading' => 'Sitemap', 'pages' => $this->content->pages()], $filters);
    }
}
