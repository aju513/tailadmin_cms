<?php

namespace App\Services\Frontend;

use App\Models\Menu;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\Support\Collection;

class FrontendLayoutService
{
    public function __construct(
        private readonly MenuRepositoryInterface $menus,
        private readonly SiteSettingService $settings,
        private readonly FrontendCache $cache,
        private readonly SafeHtml $html,
    ) {}

    public function data(): array
    {
        $settings = $this->cache->remember('settings', fn () => $this->settings->all());
        $settings['site_name'] = $settings['site_name'] ?: config('frontend.name');
        $headerLocation = config('frontend.menu_locations.header', 'header');
        $footerLocation = config('frontend.menu_locations.footer', 'footer');
        $importantLinksLocation = config('frontend.menu_locations.important_links', 'important_links');
        $mainMenu = $this->cache->remember('menu.'.$headerLocation, fn () => $this->menus->forLocation($headerLocation));
        $footerMenu = $this->cache->remember('menu.'.$footerLocation, fn () => $this->menus->forLocation($footerLocation));
        $importantLinksMenu = $this->cache->remember('menu.'.$importantLinksLocation, fn () => $this->menus->forLocation($importantLinksLocation));

        return [
            'settings' => $settings,
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
            'importantLinksMenu' => $importantLinksMenu,
            'navigation' => $mainMenu ? $this->navigation($mainMenu->items) : $this->configuredNavigation(config('frontend.navigation.header', []), $settings),
            'footerNavigation' => $footerMenu ? $this->navigation($footerMenu->items) : $this->configuredNavigation(config('frontend.navigation.footer', []), $settings),
            'importantNavigation' => $this->importantNavigation($importantLinksMenu),
            'mapEmbedUrl' => $this->mapEmbedUrl($settings['map_url']),
            'socialLinks' => $this->socialLinks($settings['social_links']),
        ];
    }

    private function importantNavigation(?Menu $menu): array
    {
        $links = $menu
            ? $menu->items->whereNull('page_id')->map(fn ($item) => ['label' => $item->label, 'url' => $item->external_url])
            : collect(config('frontend.important_links', []));

        return $links->filter(fn ($link) => is_string($link['url'] ?? null) && $this->html->externalUrl($link['url']))
            ->map(fn ($link) => ['label' => $link['label'], 'href' => $link['url'], 'external' => true, 'children' => []])
            ->values()->all();
    }

    private function socialLinks(array $links): array
    {
        return collect($links)->filter(fn ($link) => is_array($link) && filled($link['label'] ?? null) && is_string($link['url'] ?? null) && $this->html->externalUrl($link['url']))
            ->map(function ($link): array {
                $host = preg_replace('/^www\./', '', strtolower(parse_url($link['url'], PHP_URL_HOST) ?? ''));
                $icon = match ($host) {
                    'facebook.com' => 'icon-facebook', 'instagram.com' => 'icon-instagram',
                    'linkedin.com' => 'icon-linkedin', 'youtube.com', 'youtu.be' => 'icon-youtube',
                    'x.com', 'twitter.com' => 'icon-x', default => 'icon-arrow-up-right',
                };

                return [...$link, 'icon' => $icon];
            })->values()->all();
    }

    private function navigation(Collection $items, bool $openExternalLinks = false): array
    {
        return $items->filter(fn ($item) => $this->html->safeUrl($item->url(), true))->map(fn ($item) => [
            'label' => $item->label,
            'href' => $item->url(),
            'external' => $openExternalLinks && filled($item->external_url) && in_array(strtolower(parse_url($item->external_url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true),
            'children' => $this->navigation($item->children, $openExternalLinks),
        ])->values()->all();
    }

    private function configuredNavigation(array $items, array $settings): array
    {
        return collect($items)->map(function (array $item) use ($settings): array {
            $href = isset($item['route']) ? route($item['route']) : ($settings[$item['setting'] ?? ''] ?? $item['url'] ?? '');

            return [
                'label' => $item['label'],
                'href' => $href,
                'external' => $item['external'] ?? false,
                'children' => $this->configuredNavigation($item['children'] ?? [], $settings),
            ];
        })->filter(fn ($item) => $this->html->safeUrl($item['href'], true))->values()->all();
    }

    private function mapEmbedUrl(?string $url): ?string
    {
        $host = strtolower(parse_url($url ?? '', PHP_URL_HOST) ?? '');
        $isEmbed = str_contains(parse_url($url ?? '', PHP_URL_PATH) ?? '', '/embed') || str_contains(parse_url($url ?? '', PHP_URL_QUERY) ?? '', 'output=embed');

        return $url && $this->html->externalUrl($url) && in_array($host, ['google.com', 'www.google.com', 'maps.google.com'], true) && $isEmbed ? $url : null;
    }
}
