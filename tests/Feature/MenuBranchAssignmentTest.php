<?php

use App\Models\Menu;
use App\Models\Page;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('assigning a parent assigns the complete branch once and preserves nesting', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::factory()->create(['status' => 'published']);
    $child = Page::factory()->create(['parent_id' => $parent->id, 'path' => $parent->path.'/child', 'status' => 'published']);
    $grandchild = Page::factory()->create(['parent_id' => $child->id, 'path' => $child->path.'/grandchild']);
    $sibling = Page::factory()->create(['parent_id' => $parent->id, 'path' => $parent->path.'/sibling']);
    $other = Page::factory()->create();
    $payload = ['menu_id' => $menu->id, 'page_ids' => [$parent->id]];
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), $payload)->assertRedirect()->assertSessionHasNoErrors();
    $this->post(route('admin.menus.assign'), $payload)->assertRedirect()->assertSessionHasNoErrors();
    $items = $menu->items()->get()->keyBy('page_id');
    expect($items)->toHaveCount(4)
        ->and($items->get($child->id)->parent_id)->toBe($items->get($parent->id)->id)
        ->and($items->get($grandchild->id)->parent_id)->toBe($items->get($child->id)->id)
        ->and($items->has($sibling->id))->toBeTrue()
        ->and($items->has($other->id))->toBeFalse();
    $this->get(route('admin.menus.header'))->assertOk();
});

test('branch assignment still validates input and denies unauthorized users', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $page = Page::factory()->create();
    $this->actingAs(User::factory()->create(['status' => 'active']))
        ->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$page->id]])->assertForbidden();
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [999999]])->assertSessionHasErrors('page_ids.0');
    $this->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$page->id, $page->id]])->assertSessionHasErrors('page_ids.1');
    expect($menu->items()->count())->toBe(0);
});

test('menu link validation restores the custom link panel with the submitted values', function (): void {
    $menu = Menu::where('location', 'header')->firstOrFail();
    $this->actingAs($this->admin)->from(route('admin.menus.header'))
        ->post(route('admin.menus.links.store'), [
            'menu_id' => $menu->id, 'label' => 'Link to correct', 'external_url' => 'javascript:alert(1)',
        ])->assertRedirect(route('admin.menus.header'))->assertSessionHasErrors('external_url');

    $response = $this->get(route('admin.menus.header'))->assertOk()->assertSee('Link to correct');
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $panel = $xpath->query("//*[@id='menu-{$menu->id}-link-panel']")->item(0);
    expect($panel)->not->toBeNull()
        ->and($panel->getAttribute('style'))->toBe('')
        ->and($xpath->query("//*[@id='menu-{$menu->id}-url' and @value='javascript:alert(1)']")->length)->toBe(1);
    $this->assertDatabaseMissing('menu_items', ['menu_id' => $menu->id, 'label' => 'Link to correct']);
});
