<?php

use App\Enums\PageType;
use App\Models\MediaAsset;
use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Page;
use App\Models\ResourceCategory;
use App\Models\ResourceDocument;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    config(['cache.default' => 'array', 'settings.nepali' => false]);
    Cache::flush();
});

test('resource category survives admin saves and scopes public page documents', function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $admin = User::findOrFail(1);
    $selected = ResourceCategory::factory()->create(['is_active' => true]);
    $other = ResourceCategory::factory()->create(['is_active' => true]);
    $asset = MediaAsset::create(['disk' => 'public', 'path' => 'documents/scope.pdf', 'original_name' => 'scope.pdf', 'mime_type' => 'application/pdf', 'size' => 10]);
    $this->actingAs($admin)->post(route('admin.pages.store'), ['title' => 'Scoped resources', 'slug' => 'scoped-resources', 'page_type' => 'resource', 'resource_category_id' => $selected->id, 'status' => 'published'])->assertSessionHasNoErrors();
    $page = Page::where('path', 'scoped-resources')->firstOrFail();
    expect($page->resource_category_id)->toBe($selected->id);
    $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Resource category')->assertSee($selected->name);
    $this->put(route('admin.pages.update', $page), ['title' => 'Scoped resources', 'slug' => 'scoped-resources', 'page_type' => 'resource', 'status' => 'published'])->assertSessionHasNoErrors();
    expect($page->fresh()->resource_category_id)->toBe($selected->id);
    ResourceDocument::factory()->create(['title' => 'Matching document', 'resource_category_id' => $selected->id, 'file_media_id' => $asset->id, 'status' => 'published', 'published_at' => now()->subDay()]);
    ResourceDocument::factory()->create(['title' => 'Unrelated document', 'resource_category_id' => $other->id, 'file_media_id' => $asset->id, 'status' => 'published', 'published_at' => now()->subDay()]);
    $this->get('/scoped-resources')->assertOk()->assertSee('Matching document')->assertDontSee('Unrelated document');
    $this->put(route('admin.pages.update', $page), ['title' => 'Scoped resources', 'slug' => 'scoped-resources', 'page_type' => 'resource', 'resource_category_id' => '', 'status' => 'published'])->assertSessionHasNoErrors();
    expect($page->fresh()->resource_category_id)->toBeNull();
    $response = $this->get('/scoped-resources')->assertOk()->assertSee('Matching document')->assertSee('Unrelated document');
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);
    $panel = $xpath->query('//section[@id="resources-panel-'.$selected->id.'"]')->item(0);
    expect($panel->textContent)->toContain('Matching document')->not->toContain('Unrelated document');
});

test('page category changes validate input enforce permissions and clear assignments on type change', function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $admin = User::findOrFail(1);
    $page = Page::factory()->create(['page_type' => PageType::Resource, 'resource_category_id' => ResourceCategory::factory()->create()->id]);
    $payload = ['title' => 'Category page', 'slug' => $page->slug, 'page_type' => 'resource', 'status' => 'draft', 'resource_category_id' => 999999];
    $this->actingAs($admin)->put(route('admin.pages.update', $page), $payload)->assertSessionHasErrors('resource_category_id');
    expect($page->fresh()->resource_category_id)->toBe($page->resource_category_id);
    $this->actingAs(User::factory()->create(['status' => 'active']))->put(route('admin.pages.update', $page), $payload)->assertForbidden();
    $this->actingAs($admin)->put(route('admin.pages.update', $page), [...$payload, 'page_type' => 'article'])->assertSessionHasNoErrors();
    expect($page->fresh()->resource_category_id)->toBeNull();
});

test('notices pages aggregate only their published assigned sections and preserve search scope', function (): void {
    $board = Page::factory()->create(['path' => 'scoped-board', 'slug' => 'scoped-board', 'page_type' => PageType::Notices, 'status' => 'published']);
    $child = Page::factory()->create(['parent_id' => $board->id, 'path' => 'scoped-board/tenders', 'page_type' => PageType::Notices, 'status' => 'published']);
    $hidden = Page::factory()->create(['parent_id' => $board->id, 'path' => 'scoped-board/hidden', 'page_type' => PageType::Notices, 'status' => 'draft']);
    $hiddenChild = Page::factory()->create(['parent_id' => $hidden->id, 'path' => 'scoped-board/hidden/child', 'page_type' => PageType::Notices, 'status' => 'published']);
    $unrelated = Page::factory()->create(['path' => 'other-board', 'page_type' => PageType::Notices, 'status' => 'published']);
    $category = NoticeCategory::factory()->create(['is_active' => true]);
    $attributes = ['notice_category_id' => $category->id, 'status' => 'published', 'published_at' => now()->subDay()];
    Notice::factory()->create([...$attributes, 'title' => 'Board training notice', 'notice_page_id' => $board->id]);
    Notice::factory()->count(16)->create([...$attributes, 'title' => 'Child training notice', 'notice_page_id' => $child->id]);
    foreach ([$hidden, $hiddenChild, $unrelated] as $section) {
        Notice::factory()->create([...$attributes, 'title' => 'Excluded training '.$section->id, 'notice_page_id' => $section->id]);
    }
    Notice::factory()->create([...$attributes, 'title' => 'Unassigned training', 'notice_page_id' => null]);
    Notice::factory()->create([...$attributes, 'title' => 'Scheduled training', 'notice_page_id' => $child->id, 'published_at' => now()->addDay()]);
    $response = $this->get('/scoped-board?q=training&notice_category_id='.$category->id)->assertOk()->assertDontSee('Excluded training')->assertDontSee('Unassigned training')->assertDontSee('Scheduled training');
    expect($response->viewData('items')->total())->toBe(17);
    expect($response->viewData('items')->url(2))->toContain('notices_page=2', 'q=training');
    $response = $this->get('/scoped-board/tenders?notices_page=2&q=training')->assertOk()->assertDontSee('Board training notice');
    expect($response->viewData('items')->total())->toBe(16);
});

test('editing legacy notice pages preserves category scope and notice details keep their sidebar in the same section', function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $category = NoticeCategory::factory()->create(['is_active' => true]);
    $page = Page::factory()->create(['slug' => 'legacy-board', 'path' => 'legacy-board', 'page_type' => PageType::Notices, 'notice_category_id' => $category->id, 'status' => 'published']);
    $this->actingAs(User::findOrFail(1))->put(route('admin.pages.update', $page), ['title' => 'Legacy board', 'slug' => $page->slug, 'page_type' => 'notices', 'status' => 'published'])->assertSessionHasNoErrors();
    expect($page->fresh()->notice_category_id)->toBe($category->id);
    $attributes = ['notice_category_id' => $category->id, 'status' => 'published', 'published_at' => now()->subDay()];
    $current = Notice::factory()->create([...$attributes, 'notice_page_id' => $page->id]);
    Notice::factory()->create([...$attributes, 'title' => 'Same section notice', 'notice_page_id' => $page->id]);
    Notice::factory()->create([...$attributes, 'title' => 'Other section notice', 'notice_page_id' => Page::factory()->create(['page_type' => PageType::Notices])->id]);
    Notice::factory()->create([...$attributes, 'title' => 'Unassigned legacy notice']);
    $this->get('/legacy-board')->assertOk()->assertSee('Unassigned legacy notice');
    $this->get(route('public.notices.show', $current->slug))->assertOk()->assertSee('Same section notice')->assertDontSee('Other section notice')->assertDontSee('Unassigned legacy notice');
});
