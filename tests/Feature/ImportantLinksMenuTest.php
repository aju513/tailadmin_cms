<?php

use App\Models\Menu;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\MenuService;
use App\Services\NavigationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->importantMenu = Menu::where('location', 'important_links')->firstOrFail();
    $this->importantMenu->items()->delete();
    Cache::flush();
});

test('important links have a dedicated admin manager and authorized sidebar entry', function (): void {
    $role = Role::create(['name' => 'menu-editor', 'guard_name' => 'web']);
    $role->givePermissionTo('menus.manage');
    $editor = User::factory()->create(['status' => 'active']);
    $editor->assignRole($role);
    $this->artisan('admin:menu-regenerate')->assertSuccessful();

    $this->actingAs($editor)->get(route('admin.menus.important-links'))
        ->assertOk()->assertSee('Important Links Menu Manager')
        ->assertSee('Add an external link')->assertSee('External URL')
        ->assertDontSee('Assign pages')->assertDontSee('Parent item')
        ->assertDontSee('name="parent_id"', false)->assertDontSee('name="page_ids[]"', false)
        ->assertDontSee('Main Menu Manager')->assertDontSee('Footer Menu Manager')
        ->assertDontSee('Dynamic Menus Manager');
    $this->get(route('admin.menus.index'))->assertOk()
        ->assertViewHas('importantLinksMenus', fn ($menus) => $menus->pluck('id')->all() === [$this->importantMenu->id])
        ->assertViewHas('dynamicMenus', fn ($menus) => ! $menus->contains('location', 'important_links'));
    foreach (['admin.menus.header', 'admin.menus.footer'] as $route) {
        $this->get(route($route))->assertOk()->assertSee('Assign pages')->assertSee('Parent item');
    }

    $navigation = collect(app(NavigationService::class)->forUser($editor))->firstWhere('key', 'menus');
    expect(collect($navigation['children'])->pluck('route')->all())->toBe([
        'admin.menus.header', 'admin.menus.footer', 'admin.menus.important-links',
    ]);
});

test('important link creation supports saved label and URL lengths and updates the footer cache', function (): void {
    $label = str_repeat('Link ', 40).'label';
    $url = 'https://example.test/institute?filter='.str_repeat('a', 400);
    $this->get('/')->assertOk()->assertDontSee($label);
    $this->actingAs($this->admin)->from(route('admin.menus.important-links'))
        ->post(route('admin.menus.links.store'), [
            'menu_id' => $this->importantMenu->id, 'label' => $label, 'external_url' => $url,
        ])->assertRedirect(route('admin.menus.important-links'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('menu_items', ['menu_id' => $this->importantMenu->id, 'label' => $label, 'external_url' => $url, 'parent_id' => null, 'page_id' => null]);
    $html = $this->get('/')->assertOk()->assertSee($label)->getContent();
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    expect($xpath->query("//footer//a[@href='$url' and @target='_blank' and @rel='noopener noreferrer']")->length)->toBe(1);
    $this->assertDatabaseHas('activity_log', ['event' => 'menu-link.created']);
});

test('important links reject page branch assignment', function (): void {
    $parent = Page::factory()->create(['title' => 'Important Parent', 'status' => 'published', 'published_at' => now()->subDay()]);
    $child = Page::factory()->create(['title' => 'Important Child', 'parent_id' => $parent->id, 'path' => $parent->path.'/child', 'status' => 'published', 'published_at' => now()->subDay()]);
    $payload = ['menu_id' => $this->importantMenu->id, 'page_ids' => [$parent->id]];
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), $payload)
        ->assertSessionHasErrors('menu_id');

    expect($this->importantMenu->items()->count())->toBe(0);
    foreach (['header', 'footer'] as $location) {
        expect(Menu::where('location', $location)->firstOrFail()->items()->whereIn('page_id', [$parent->id, $child->id])->exists())->toBeFalse();
    }
    $this->assertDatabaseMissing('activity_log', ['event' => 'menu-pages.assigned']);
});

test('important links reject nesting and continue to respect link visibility', function (): void {
    $parent = $this->importantMenu->items()->create(['label' => 'Useful Organizations', 'external_url' => 'https://organizations.example.test']);
    $this->actingAs($this->admin)->post(route('admin.menus.links.store'), [
        'menu_id' => $this->importantMenu->id, 'parent_id' => $parent->id,
        'label' => 'Partner Organization', 'external_url' => 'https://partner.example.test',
    ])->assertSessionHasErrors('parent_id');
    $this->assertDatabaseMissing('menu_items', ['label' => 'Partner Organization']);
    $this->get('/')->assertOk()->assertSee('Useful Organizations')->assertDontSee('Partner Organization');
    $parent->update(['is_visible' => false]);
    $this->get('/')->assertOk()->assertDontSee('Useful Organizations')->assertDontSee('Partner Organization');
});

test('important links do not publish legacy page assignments', function (string $status, bool $scheduled): void {
    $page = Page::factory()->create(['title' => 'Private Important Page', 'status' => $status, 'published_at' => $scheduled ? now()->addDay() : now()->subDay()]);
    $this->importantMenu->items()->create(['page_id' => $page->id, 'label' => $page->title]);
    $this->get('/')->assertOk()->assertDontSee('Private Important Page');
})->with(['draft' => ['draft', false], 'scheduled' => ['published', true], 'published' => ['published', false]]);

test('important links can be reordered and deleted without retaining stale public links', function (): void {
    $first = $this->importantMenu->items()->create(['label' => 'First Important Link', 'external_url' => 'https://first.example.test', 'sort_order' => 0]);
    $second = $this->importantMenu->items()->create(['label' => 'Second Important Link', 'external_url' => 'https://second.example.test', 'sort_order' => 1]);
    $this->get('/')->assertOk()->assertSeeInOrder(['First Important Link', 'Second Important Link']);
    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $this->importantMenu->id, 'menu_items' => [$second->id, $first->id],
    ])->assertOk();
    $this->get('/')->assertOk()->assertSeeInOrder(['Second Important Link', 'First Important Link']);
    $this->delete(route('admin.menus.destroy', $first))->assertRedirect();
    $this->get('/')->assertOk()->assertDontSee('First Important Link')->assertSee('Second Important Link');
    $this->delete(route('admin.menus.bulk-destroy'), ['menu_id' => $this->importantMenu->id, 'menu_items' => [$second->id]])->assertRedirect();
    $this->get('/')->assertOk()->assertDontSee('Second Important Link');
    expect($this->importantMenu->items()->count())->toBe(0);
});

test('important links reject unsafe URLs and parents from another menu', function (): void {
    $parent = Menu::where('location', 'header')->firstOrFail()->items()->create(['label' => 'Other Menu Parent', 'external_url' => '/contact']);
    $payload = ['menu_id' => $this->importantMenu->id, 'label' => 'Invalid Link', 'external_url' => 'javascript:alert(1)'];
    $this->actingAs($this->admin)->post(route('admin.menus.links.store'), $payload)->assertSessionHasErrors('external_url');
    $this->post(route('admin.menus.links.store'), [...$payload, 'external_url' => 'https://example.test', 'parent_id' => $parent->id])->assertSessionHasErrors('parent_id');
    $this->post(route('admin.menus.assign'), ['menu_id' => $this->importantMenu->id, 'page_ids' => [999999]])->assertSessionHasErrors('page_ids.0');
    expect($this->importantMenu->items()->count())->toBe(0);
});

test('important links reject cross-menu and incomplete reordering without changing order', function (): void {
    $first = $this->importantMenu->items()->create(['label' => 'First Link', 'external_url' => 'https://first.example.test', 'sort_order' => 0]);
    $second = $this->importantMenu->items()->create(['label' => 'Second Link', 'external_url' => 'https://second.example.test', 'sort_order' => 1]);
    $other = Menu::where('location', 'footer')->firstOrFail()->items()->create(['label' => 'Other Link', 'external_url' => '/contact']);
    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $this->importantMenu->id, 'menu_items' => [$other->id, $first->id],
    ])->assertUnprocessable()->assertJsonValidationErrors('menu_items');
    $this->patchJson(route('admin.menus.order'), [
        'menu_id' => $this->importantMenu->id, 'menu_items' => [$second->id],
    ])->assertUnprocessable()->assertJsonValidationErrors('menu_items');
    $this->patchJson(route('admin.menus.order'), [
        'menu_id' => $this->importantMenu->id, 'parent_id' => $first->id, 'menu_items' => [$second->id],
    ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    expect($this->importantMenu->items()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('settings-only users cannot view or modify the important links menu', function (): void {
    $role = Role::create(['name' => 'settings-editor', 'guard_name' => 'web']);
    $role->givePermissionTo('settings.manage');
    $editor = User::factory()->create(['status' => 'active']);
    $editor->assignRole($role);
    $item = $this->importantMenu->items()->create(['label' => 'Protected Link', 'external_url' => '/contact']);
    $page = Page::factory()->create();
    $this->get(route('admin.menus.important-links'))->assertRedirect(route('login'));
    $this->actingAs($editor)->get(route('admin.menus.important-links'))->assertForbidden();
    $this->post(route('admin.menus.links.store'), ['menu_id' => $this->importantMenu->id, 'label' => 'Unauthorized Link', 'external_url' => '/halls'])->assertForbidden();
    $this->post(route('admin.menus.assign'), ['menu_id' => $this->importantMenu->id, 'page_ids' => [$page->id]])->assertForbidden();
    $this->patchJson(route('admin.menus.order'), ['menu_id' => $this->importantMenu->id, 'menu_items' => [$item->id]])->assertForbidden();
    $this->delete(route('admin.menus.destroy', $item))->assertForbidden();
    $this->delete(route('admin.menus.bulk-destroy'), ['menu_id' => $this->importantMenu->id, 'menu_items' => [$item->id]])->assertForbidden();
    $this->get(route('admin.settings.edit'))->assertOk()->assertDontSee('name="important_links', false)->assertDontSee('Add important link');
    $this->put(route('admin.settings.update'), [
        'site_name' => 'Institute', 'important_links' => [['label' => 'Bypass Link', 'url' => 'https://example.test']],
    ])->assertSessionHasErrors('important_links');
    expect($this->importantMenu->items()->pluck('id')->all())->toBe([$item->id]);
});

test('important links use safe configuration fallback only when the menu does not exist', function (): void {
    config(['frontend.important_links' => [
        ['label' => 'Fallback Important Link', 'url' => 'https://fallback.example.test'],
        ['label' => 'Unsafe Fallback Link', 'url' => 'javascript:alert(1)'],
        ['label' => 'Internal Fallback Link', 'url' => '/contact'],
    ]]);
    SiteSetting::create(['key' => 'important_links', 'value' => json_encode([['label' => 'Retired Settings Link', 'url' => 'https://retired.example.test']]), 'type' => 'json']);
    $this->get('/')->assertOk()->assertDontSee('Fallback Important Link')->assertDontSee('Retired Settings Link');
    $this->importantMenu->delete();
    $this->get('/')->assertOk()->assertSee('Fallback Important Link')->assertDontSee('Unsafe Fallback Link')->assertDontSee('Internal Fallback Link')->assertDontSee('Retired Settings Link');
});

test('important links accept full HTTP and HTTPS URLs', function (string $url): void {
    $this->actingAs($this->admin)->post(route('admin.menus.links.store'), [
        'menu_id' => $this->importantMenu->id, 'label' => 'External Link', 'external_url' => $url,
    ])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('menu_items', ['menu_id' => $this->importantMenu->id, 'external_url' => $url, 'parent_id' => null, 'page_id' => null]);
})->with(['http://example.test/path?query=1#section', 'https://example.test/path?query=1#section']);

test('important links reject internal unsafe and malformed URLs', function (mixed $url): void {
    $this->actingAs($this->admin)->post(route('admin.menus.links.store'), [
        'menu_id' => $this->importantMenu->id, 'label' => 'Invalid External Link', 'external_url' => $url,
    ])->assertSessionHasErrors('external_url');
    expect($this->importantMenu->items()->count())->toBe(0);
})->with([
    'internal path' => '/contact', 'anchor' => '#organizations', 'relative protocol' => '//example.test',
    'email' => 'mailto:office@example.test', 'phone' => 'tel:+977123456', 'script' => 'javascript:alert(1)',
    'data' => 'data:text/plain,test', 'ftp' => 'ftp://example.test', 'missing host' => 'https://',
    'missing slashes' => 'https:example.test', 'whitespace' => 'https://example.test/a b',
    'backslash' => 'https://example.test\\path', 'array' => [['https://example.test']],
]);

test('important links reject injected page fields on link creation', function (string $field): void {
    $page = Page::factory()->create();
    $this->actingAs($this->admin)->post(route('admin.menus.links.store'), [
        'menu_id' => $this->importantMenu->id, 'label' => 'Injected Page', 'external_url' => 'https://example.test',
        $field => $field === 'page_id' ? $page->id : [$page->id],
    ])->assertSessionHasErrors($field);
    expect($this->importantMenu->items()->count())->toBe(0);
})->with(['page_id', 'page_ids']);

test('important link workflows reject bypasses without writing or logging', function (): void {
    $service = app(MenuService::class);
    $page = Page::factory()->create();
    $parent = $this->importantMenu->items()->create(['label' => 'Saved Link', 'external_url' => 'https://saved.example.test']);
    $data = ['menu_id' => $this->importantMenu->id, 'label' => 'Bypass Link', 'external_url' => 'https://example.test'];
    expect(fn () => $service->assignPages(['menu_id' => $this->importantMenu->id, 'page_ids' => [$page->id]], $this->admin))->toThrow(ValidationException::class)
        ->and(fn () => $service->addLink([...$data, 'external_url' => '/contact'], $this->admin))->toThrow(ValidationException::class)
        ->and(fn () => $service->addLink([...$data, 'parent_id' => $parent->id], $this->admin))->toThrow(ValidationException::class)
        ->and(fn () => $service->addLink([...$data, 'page_id' => $page->id], $this->admin))->toThrow(ValidationException::class)
        ->and(fn () => $service->reorder(['menu_id' => $this->importantMenu->id, 'parent_id' => $parent->id, 'menu_items' => [$parent->id]], $this->admin))->toThrow(ValidationException::class)
        ->and($service->availablePages($this->importantMenu))->toBeEmpty()
        ->and($this->importantMenu->items()->pluck('id')->all())->toBe([$parent->id]);
    $this->assertDatabaseMissing('activity_log', ['event' => 'menu-link.created']);
    $this->assertDatabaseMissing('activity_log', ['event' => 'menu-pages.assigned']);
});

test('important footer links omit legacy internal URLs', function (): void {
    foreach (['/contact', '#group', 'mailto:office@example.test', 'tel:+977123456', 'https://'] as $index => $url) {
        $this->importantMenu->items()->create(['label' => 'Legacy Internal Link '.$index, 'external_url' => $url]);
    }
    $this->importantMenu->items()->create(['label' => 'Visible External Link', 'external_url' => 'https://external.example.test']);
    $this->get('/')->assertOk()->assertSee('Visible External Link')->assertDontSee('Legacy Internal Link');
});

test('important links flattening preserves links and display order without changing other menus', function (): void {
    $root = $this->importantMenu->items()->create(['label' => 'Root Link', 'external_url' => 'https://root.example.test', 'sort_order' => 0]);
    $second = $this->importantMenu->items()->create(['label' => 'Second Link', 'external_url' => 'https://second.example.test', 'sort_order' => 1]);
    $child = $this->importantMenu->items()->create(['label' => 'Child Link', 'external_url' => 'https://child.example.test', 'parent_id' => $root->id, 'sort_order' => 0]);
    $page = Page::factory()->create();
    $legacy = $this->importantMenu->items()->create(['page_id' => $page->id, 'label' => 'Legacy Page Link', 'parent_id' => $child->id, 'sort_order' => 0]);
    $header = Menu::where('location', 'header')->firstOrFail();
    $headerRoot = $header->items()->create(['label' => 'Header Root', 'external_url' => '/contact']);
    $headerChild = $header->items()->create(['label' => 'Header Child', 'external_url' => '/halls', 'parent_id' => $headerRoot->id, 'sort_order' => 8]);

    $migration = require database_path('migrations/2026_10_04_120000_flatten_important_links_menu.php');
    $migration->up();
    $migration->up();

    $items = $this->importantMenu->items()->get();
    expect($items->pluck('id')->all())->toBe([$root->id, $child->id, $legacy->id, $second->id])
        ->and($items->pluck('sort_order')->all())->toBe([0, 1, 2, 3])
        ->and($items->pluck('parent_id')->unique()->all())->toBe([null])
        ->and($headerChild->fresh()->parent_id)->toBe($headerRoot->id)
        ->and($headerChild->fresh()->sort_order)->toBe(8);
    $this->assertDatabaseHas('menu_items', ['id' => $legacy->id, 'page_id' => $page->id, 'label' => 'Legacy Page Link']);
    $this->assertDatabaseHas('menu_items', ['id' => $child->id, 'label' => 'Child Link', 'external_url' => 'https://child.example.test']);
    Cache::flush();
    $document = new DOMDocument;
    @$document->loadHTML($this->get('/')->assertOk()->assertSeeInOrder(['Root Link', 'Child Link', 'Second Link'])->assertDontSee('Legacy Page Link')->getContent());
    $xpath = new DOMXPath($document);
    expect($xpath->query("//footer//li[a[normalize-space(.)='Root Link' or normalize-space(.)='Child Link' or normalize-space(.)='Second Link']]/ul")->length)->toBe(0);
});

test('important links migration preserves saved labels URLs order and the original settings row', function (): void {
    $this->importantMenu->delete();
    $links = [
        ['label' => 'Second Named Link', 'url' => 'https://second.example.test?query='.str_repeat('a', 400)],
        ['label' => 'First Named Link', 'url' => 'https://first.example.test'],
    ];
    $saved = json_encode($links, JSON_THROW_ON_ERROR);
    SiteSetting::create(['key' => 'important_links', 'value' => $saved, 'type' => 'json']);
    $migration = require database_path('migrations/2026_10_04_110000_add_important_links_menu.php');
    $migration->up();
    $migration->up();
    $menu = Menu::where('location', 'important_links')->firstOrFail();
    expect($menu->items->pluck('label')->all())->toBe(array_column($links, 'label'))
        ->and($menu->items->pluck('external_url')->all())->toBe(array_column($links, 'url'))
        ->and($menu->items->pluck('sort_order')->all())->toBe([0, 1]);
    $this->assertDatabaseHas('site_settings', ['key' => 'important_links', 'value' => $saved]);
});

test('important links migration preserves a saved empty list and imports defaults when absent', function (bool $hasSavedList): void {
    $this->importantMenu->delete();
    config(['frontend.important_links' => [['label' => 'Default Important Link', 'url' => 'https://default.example.test']]]);
    if ($hasSavedList) {
        SiteSetting::create(['key' => 'important_links', 'value' => '[]', 'type' => 'json']);
    }
    $migration = require database_path('migrations/2026_10_04_110000_add_important_links_menu.php');
    $migration->up();
    expect(Menu::where('location', 'important_links')->firstOrFail()->items()->count())->toBe($hasSavedList ? 0 : 1);
})->with(['saved empty' => true, 'no setting' => false]);

test('important links migration respects existing positions including empty menus', function (bool $withLink): void {
    if ($withLink) {
        $this->importantMenu->items()->create(['label' => 'Existing Important Link', 'external_url' => '/contact']);
    }
    $migration = require database_path('migrations/2026_10_04_110000_add_important_links_menu.php');
    $migration->up();
    expect($this->importantMenu->items()->count())->toBe($withLink ? 1 : 0);
})->with(['populated' => true, 'empty' => false]);

test('important links migration rolls back partial imports when saved entries are invalid', function (): void {
    $this->importantMenu->delete();
    $saved = json_encode([['label' => 'Valid Link', 'url' => 'https://example.test'], ['label' => 'Missing URL']], JSON_THROW_ON_ERROR);
    SiteSetting::create(['key' => 'important_links', 'value' => $saved, 'type' => 'json']);
    $migration = require database_path('migrations/2026_10_04_110000_add_important_links_menu.php');
    expect(fn () => $migration->up())->toThrow(UnexpectedValueException::class);
    $this->assertDatabaseMissing('menus', ['location' => 'important_links']);
    $this->assertDatabaseMissing('menu_items', ['label' => 'Valid Link']);
    $this->assertDatabaseHas('site_settings', ['key' => 'important_links', 'value' => $saved]);
});
