<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
});

test('public menus follow current page parents instead of stale assignment parents', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::factory()->create(['title' => ['en' => 'Notice Board'], 'status' => 'published', 'published_at' => now()->subDay()]);
    $child = Page::factory()->create(['title' => ['en' => 'Tenders'], 'parent_id' => $parent->id, 'path' => $parent->path.'/tenders', 'status' => 'published', 'published_at' => now()->subDay()]);
    $grandchild = Page::factory()->create(['parent_id' => $child->id, 'path' => $child->path.'/results', 'status' => 'published', 'published_at' => now()->subDay()]);
    $repository = app(MenuRepositoryInterface::class);
    $repository->assignPages($menu, [$parent->id, $child->id, $grandchild->id]);
    $childItem = $menu->items()->where('page_id', $child->id)->firstOrFail();
    $childItem->update(['parent_id' => null]);

    $tree = $repository->forLocation('header')->items;
    expect($tree)->toHaveCount(1)
        ->and($tree->first()->children->first()->page_id)->toBe($child->id)
        ->and($tree->first()->children->first()->children->first()->page_id)->toBe($grandchild->id);
    // Rendering is read-only: it must not silently rewrite the admin menu.
    expect($childItem->fresh()->parent_id)->toBeNull();
    $this->get('/')->assertOk()->assertSee('Toggle Notice Board submenu')->assertSee('menu-branch')->assertSee('Expand Tenders');

    $child->update(['parent_id' => null, 'path' => 'tenders']);
    $tree = $repository->forLocation('header')->items;
    expect($tree)->toHaveCount(2)
        ->and($tree->firstWhere('page_id', $parent->id)->children)->toHaveCount(0)
        ->and($tree->firstWhere('page_id', $child->id)->children->first()->page_id)->toBe($grandchild->id);
});

test('hidden unpublished and scheduled assigned ancestors suppress nested header links', function (array $attributes): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
    $child = Page::factory()->create(['parent_id' => $parent->id, 'status' => 'published', 'published_at' => now()->subDay()]);
    $repository = app(MenuRepositoryInterface::class);
    $repository->assignPages($menu, [$parent->id, $child->id]);
    $menu->items()->where('page_id', $child->id)->update(['parent_id' => null]);
    if (isset($attributes['is_visible'])) {
        $menu->items()->where('page_id', $parent->id)->update($attributes);
    } else {
        $parent->update($attributes);
    }
    expect($repository->forLocation('header')->items)->toHaveCount(0);
})->with([
    'hidden' => [['is_visible' => false]],
    'draft' => [['status' => 'draft']],
    'scheduled' => [fn () => ['published_at' => now()->addDay()]],
]);

test('unassigned ancestors do not add links and manual menu nesting is preserved', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
    $middle = Page::factory()->create(['parent_id' => $parent->id]);
    $child = Page::factory()->create(['parent_id' => $middle->id, 'status' => 'published', 'published_at' => now()->subDay()]);
    $repository = app(MenuRepositoryInterface::class);
    $repository->assignPages($menu, [$parent->id, $child->id]);
    $parentItem = $menu->items()->where('page_id', $parent->id)->firstOrFail();
    $manual = MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parentItem->id, 'label' => 'Manual link', 'external_url' => '/contact', 'is_visible' => true]);
    $tree = $repository->forLocation('header')->items;
    expect($tree)->toHaveCount(1)
        ->and($tree->first()->children->pluck('id')->all())->toContain($manual->id)
        ->and($tree->first()->children->pluck('page_id')->all())->toContain($child->id)->not->toContain($middle->id);
});

test('navbar parents are dropdown buttons at every depth while leaves retain their links', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parentId = null;
    foreach (['Navbar parent', 'Navbar child', 'Navbar grandchild'] as $label) {
        $parentId = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'label' => $label,
            'external_url' => '/parent-link',
            'is_visible' => true,
        ])->id;
    }
    MenuItem::create([
        'menu_id' => $menu->id,
        'parent_id' => $parentId,
        'label' => 'Navbar leaf',
        'external_url' => 'https://example.com/navbar-leaf',
        'is_visible' => true,
    ]);
    MenuItem::create([
        'menu_id' => $menu->id,
        'label' => 'Navbar direct link',
        'external_url' => '/contact',
        'is_visible' => true,
    ]);

    $response = $this->get('/')->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);

    foreach (['Navbar parent', 'Navbar child', 'Navbar grandchild'] as $label) {
        expect($xpath->query("//header//button[@type='button' and @aria-expanded='false' and normalize-space(.)='$label']")->length)->toBe(2)
            ->and($xpath->query("//header//a[normalize-space(.)='$label']")->length)->toBe(0);
    }
    expect($xpath->query("//header//a[@href='https://example.com/navbar-leaf' and normalize-space(.)='Navbar leaf']")->length)->toBe(2)
        ->and($xpath->query("//header//a[contains(@href, '/contact') and normalize-space(.)='Navbar direct link']")->length)->toBe(2);
});
