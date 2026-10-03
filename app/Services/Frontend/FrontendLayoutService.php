<?php

namespace App\Services\Frontend;

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
        $mainMenu = $this->cache->remember('menu.'.$headerLocation, fn () => $this->menus->forLocation($headerLocation));
        $footerMenu = $this->cache->remember('menu.'.$footerLocation, fn () => $this->menus->forLocation($footerLocation));

        return [
            'settings' => $settings,
            'mainMenu' => $mainMenu,
            'footerMenu' => $footerMenu,
            'navigation' => $mainMenu ? $this->navigation($mainMenu->items) : $this->configuredNavigation(config('frontend.navigation.header', []), $settings),
            'footerNavigation' => $footerMenu ? $this->navigation($footerMenu->items) : $this->configuredNavigation(config('frontend.navigation.footer', []), $settings),
            'importantNavigation' => collect($settings['important_links'])->map(fn ($link) => ['label' => $link['label'], 'href' => $link['url']])->all(),
            'mapEmbedUrl' => $this->mapEmbedUrl($settings['map_url']),
        ];
    }

    private function navigation(Collection $items): array
    {
        return $items->filter(fn ($item) => $this->html->safeUrl($item->url(), true))->map(fn ($item) => [
            'label' => $item->label,
            'href' => $item->url(),
            'children' => $this->navigation($item->children),
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

        return $url && in_array($host, ['google.com', 'www.google.com', 'maps.google.com'], true) && $isEmbed ? $url : null;
    }
}
