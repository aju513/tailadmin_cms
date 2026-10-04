<?php

namespace App\Services\Frontend;

use App\Models\GalleryAlbum;
use App\Models\Hall;
use App\Models\HomepageContent;
use App\Models\News;
use App\Models\Notice;
use App\Models\Page;
use App\Models\ResourceDocument;
use App\Models\TeamMember;
use App\Models\Video;
use Illuminate\Support\Str;

class SeoService
{
    public function absolute(string $path): string
    {
        return rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
    }

    public function recordPath(string $type, object $item): string
    {
        return $type === 'pages' ? route('public.page', ['path' => $item->path], false) : route('public.'.$type.'.show', [$type === 'team' ? $item->id : $item->slug], false);
    }

    public function make(array $data): array
    {
        $settings = $data['settings'];
        $item = $data['item'] ?? $data['page'] ?? null;
        $siteName = $settings['site_name'] ?: config('frontend.name');
        $heading = (string) ($data['heading'] ?? $item?->title ?? $item?->name ?? $siteName);
        $title = $item?->meta_title ?: ($heading === $siteName ? $siteName : $heading.' | '.$siteName);
        $description = Str::limit(trim(html_entity_decode(strip_tags($item?->meta_description ?: ($item instanceof HomepageContent ? $item->body : null) ?: $item?->summary ?: $item?->description ?: $settings['meta_description'] ?? config('frontend.hero_description')), ENT_QUOTES, 'UTF-8')), 160, '');
        $path = request()->getPathInfo();
        $canonical = $this->absolute($path);
        if (config('settings.nepali') && app()->getLocale() === 'ne') {
            $canonical .= '?lang=ne';
        }
        $filtered = collect(request()->query())->except('lang')->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
        $image = null;
        if ($item) {
            foreach (['socialMedia', 'bannerMedia', 'thumbnailMedia', 'coverMedia', 'photoMedia'] as $relation) {
                if ($item->relationLoaded($relation) && $item->getRelation($relation)) {
                    $image = $item->getRelation($relation)->url();
                    break;
                }
            }
        }
        if ($item instanceof GalleryAlbum && ! $image) {
            $image = $item->photos->first()?->media?->url();
        }
        $organization = array_filter(['@type' => 'Organization', '@id' => $this->absolute('/').'#organization', 'name' => $siteName, 'url' => $this->absolute('/'), 'logo' => ($settings['logo_url'] ?? null) ?: $this->absolute(config('frontend.branding.logo')), 'telephone' => $settings['phone'] ?? null, 'email' => $settings['email'] ?? null, 'address' => empty($settings['address']) ? null : ['@type' => 'PostalAddress', 'streetAddress' => $settings['address'], 'addressCountry' => 'NP']]);
        $graph = [$organization, ['@type' => 'WebSite', '@id' => $this->absolute('/').'#website', 'url' => $this->absolute('/'), 'name' => $siteName, 'publisher' => ['@id' => $organization['@id']]]];
        $webpage = ['@type' => 'WebPage', '@id' => $canonical.'#webpage', 'url' => $canonical, 'name' => $heading, 'description' => $description, 'isPartOf' => ['@id' => $this->absolute('/').'#website']];
        if (isset($data['items'])) {
            $webpage['@type'] = 'CollectionPage';
        }
        if ($item instanceof News) {
            $article = array_filter(['@type' => 'NewsArticle', 'headline' => $item->title, 'description' => $description, 'datePublished' => $item->published_at?->toAtomString(), 'dateModified' => $item->updated_at?->toAtomString(), 'mainEntityOfPage' => ['@id' => $canonical.'#webpage'], 'publisher' => ['@id' => $organization['@id']], 'author' => $item->author ? ['@type' => 'Person', 'name' => $item->author->name] : null, 'image' => $image ? [$image] : null]);
            $graph[] = $article;
        }
        if (($item instanceof Notice || $item instanceof ResourceDocument) && $item->fileMedia) {
            $graph[] = ['@type' => 'DigitalDocument', 'name' => $item->title, 'description' => $description, 'url' => $canonical, 'dateModified' => $item->updated_at?->toAtomString(), 'encoding' => ['@type' => 'MediaObject', 'contentUrl' => $item->fileMedia->url(), 'encodingFormat' => $item->fileMedia->mime_type]];
        }
        if ($item instanceof Hall) {
            $graph[] = array_filter(['@type' => 'Place', 'name' => $item->title, 'url' => $canonical, 'description' => $description, 'address' => $item->address ?: $item->location, 'image' => $image]);
        }
        if ($item instanceof TeamMember) {
            $graph[] = ['@type' => 'Person', 'name' => $item->name, 'jobTitle' => $item->designation, 'worksFor' => ['@id' => $organization['@id']]];
        }
        if ($item instanceof GalleryAlbum) {
            $webpage['@type'] = 'ImageGallery';
        }
        if ($item instanceof Video && $image && $item->published_at && ($embed = app(VideoEmbedService::class)->url($item->video_url))) {
            $graph[] = ['@type' => 'VideoObject', 'name' => $item->title, 'description' => $description, 'thumbnailUrl' => [$image], 'uploadDate' => $item->published_at->toAtomString(), 'embedUrl' => $embed, 'url' => $canonical];
        }
        $graph[] = $webpage;
        if ($path !== '/') {
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $this->absolute('/')], ['@type' => 'ListItem', 'position' => 2, 'name' => $heading, 'item' => $canonical]]];
        }
        $alternates = [];
        if (($item instanceof Page || $item instanceof HomepageContent) && config('settings.nepali') && $item->getTranslation('title', 'ne', false) && $item->getTranslation('body', 'ne', false)) {
            $alternates = ['en' => $this->absolute($path), 'ne' => $this->absolute($path).'?lang=ne', 'x-default' => $this->absolute($path)];
        }

        return ['alternates' => $alternates, 'title' => $title, 'description' => $description, 'canonical' => $canonical, 'image' => $image, 'robots' => $filtered || ($data['kind'] ?? '') === 'search' ? 'noindex,follow' : 'index,follow', 'type' => $item instanceof News ? 'article' : 'website', 'schema' => json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
    }
}
