<?php

namespace App\Services\Frontend;

use App\Repositories\Contracts\FrontendRepositoryInterface;
use XMLWriter;

class SitemapService
{
    public const TYPES = ['pages', 'news', 'notices', 'resources', 'halls', 'gallery', 'videos', 'team'];

    public const STATIC_PATHS = ['/', '/news', '/notices', '/resources', '/halls', '/gallery', '/videos', '/team', '/contact', '/sitemap'];

    public function __construct(private readonly FrontendRepositoryInterface $content, private readonly SeoService $seo, private readonly FrontendCache $cache) {}

    public function index(): string
    {
        return $this->cache->remember('sitemap.index', function (): string {
            $xml = $this->writer('sitemapindex');
            foreach (self::TYPES as $type) {
                $chunks = max($type === 'pages' ? 1 : 0, (int) ceil($this->content->sitemapCount($type) / config('frontend.sitemap_chunk_size')));
                for ($chunk = 1; $chunk <= $chunks; $chunk++) {
                    $xml->startElement('sitemap');
                    $xml->writeElement('loc', $this->seo->absolute(route('public.sitemap.chunk', ['type' => $type, 'chunk' => $chunk], false)));
                    $xml->endElement();
                }
            }
            $xml->endElement();
            $xml->endDocument();

            return $xml->outputMemory();
        });
    }

    public function chunk(string $type, int $chunk): string
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        $count = $this->content->sitemapCount($type);
        abort_unless($chunk >= 1 && $chunk <= max($type === 'pages' ? 1 : 0, (int) ceil($count / config('frontend.sitemap_chunk_size'))), 404);

        return $this->cache->remember('sitemap.'.$type.'.'.$chunk, function () use ($type, $chunk): string {
            $xml = $this->writer('urlset');
            if ($type === 'pages' && $chunk === 1) {
                $lastModified = null;
                foreach (self::STATIC_PATHS as $path) {
                    $this->entry($xml, $path, $lastModified);
                }
            }
            foreach ($this->content->sitemapRecords($type, $chunk) as $record) {
                $path = $this->seo->recordPath($type, $record);
                if ($type === 'pages' && in_array($path, self::STATIC_PATHS, true)) {
                    continue;
                }
                $this->entry($xml, $path, $record->updated_at?->toAtomString());
                if ($type === 'pages' && config('settings.nepali') && ! empty($record->getTranslation('title', 'ne', false))) {
                    $this->entry($xml, $path.'?lang=ne', $record->updated_at?->toAtomString());
                }
            }
            $xml->endElement();
            $xml->endDocument();

            return $xml->outputMemory();
        });
    }

    private function writer(string $root): XMLWriter
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement($root);
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        return $xml;
    }

    private function entry(XMLWriter $xml, string $path, ?string $modified): void
    {
        $xml->startElement('url');
        $xml->writeElement('loc', $this->seo->absolute($path));
        if ($modified) {
            $xml->writeElement('lastmod', \Illuminate\Support\Carbon::parse($modified)->toAtomString());
        }
        $xml->endElement();
    }

    public function robots(): string
    {
        return "User-agent: *\nDisallow: /admin\nSitemap: ".$this->seo->absolute('/sitemap.xml')."\n";
    }
}
