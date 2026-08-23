<?php

use App\Enums\PageType;
use App\Models\ContentAuthor;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('pages support nested paths and only published pages are public', function (): void {
    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'About Office', 'status' => 'published', 'body' => '<p>About us</p>',
    ])->assertRedirect(route('admin.pages.index'));

    $parent = Page::where('slug', 'about-office')->firstOrFail();
    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Leadership', 'parent_id' => $parent->id, 'status' => 'published', 'body' => '<p>Leadership</p>',
    ])->assertRedirect(route('admin.pages.index'));

    $child = Page::where('slug', 'leadership')->firstOrFail();
    expect($child->path)->toBe('about-office/leadership');
    $this->get(route('public.page', ['path' => $child->path]))->assertOk()->assertSee('Leadership');

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Internal Draft', 'status' => 'draft', 'body' => 'Not public',
    ])->assertRedirect(route('admin.pages.index'));
    $draft = Page::where('title', 'Internal Draft')->firstOrFail();
    $this->get(route('public.page', ['path' => $draft->path]))->assertNotFound();
});

test('pages store and filter by page type', function (): void {
    $this->actingAs($this->admin)->get(route('admin.pages.create'))
        ->assertOk()
        ->assertSee('Page type')
        ->assertSee('Contact');

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Contact Page',
        'page_type' => PageType::Contact->value,
        'status' => 'draft',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('title', 'Contact Page')->firstOrFail();
    expect($page->page_type)->toBe(PageType::Contact);

    $this->actingAs($this->admin)->get(route('admin.pages.index', ['page_type' => PageType::Contact->value]))
        ->assertOk()
        ->assertSee('Contact Page')
        ->assertSee('Contact');
});

test('renaming a nested page updates descendant paths', function (): void {
    $parent = Page::create(['title' => 'Parent', 'slug' => 'parent', 'path' => 'parent', 'status' => 'published']);
    $child = Page::create(['title' => 'Child', 'slug' => 'child', 'path' => 'parent/child', 'parent_id' => $parent->id, 'status' => 'published']);

    $this->actingAs($this->admin)->put(route('admin.pages.update', $parent), [
        'title' => 'New Parent', 'status' => 'published',
    ])->assertRedirect(route('admin.pages.index'));

    expect($child->refresh()->path)->toBe('new-parent/child');
});

test('page forms expose nested pages as parent options', function (): void {
    $parent = Page::create(['title' => 'Parent Page', 'slug' => 'parent-page', 'path' => 'parent-page', 'status' => 'draft']);
    $child = Page::create(['title' => 'Child Page', 'slug' => 'child-page', 'path' => 'parent-page/child-page', 'parent_id' => $parent->id, 'status' => 'draft']);

    $this->actingAs($this->admin)->get(route('admin.pages.create'))
        ->assertOk()
        ->assertSee('value="'.$parent->id.'"', false)
        ->assertSee('-- Child Page', false);

    $this->actingAs($this->admin)->get(route('admin.pages.edit', $parent))
        ->assertOk()
        ->assertDontSee('value="'.$child->id.'"', false);
});

test('pages can be reordered and receive banner and social media uploads', function (): void {
    Storage::fake('public');

    $first = Page::create(['title' => 'First', 'slug' => 'first', 'path' => 'first', 'status' => 'draft', 'sort_order' => 0]);
    $second = Page::create(['title' => 'Second', 'slug' => 'second', 'path' => 'second', 'status' => 'draft', 'sort_order' => 1]);

    $this->actingAs($this->admin)->post(route('admin.pages.order'), [
        'pages' => [$second->id, $first->id],
    ])->assertRedirect();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Services',
        'status' => 'draft',
        'summary' => '<p>A short summary</p>',
        'body' => '<p>Services content</p>',
        'banner_image' => UploadedFile::fake()->image('banner.jpg'),
        'banner_alt_text' => 'Services banner',
        'social_media_image' => UploadedFile::fake()->image('social.jpg'),
        'social_media_alt_text' => 'Services social image',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::where('slug', 'services')->firstOrFail();
    expect($page->banner_media_id)->not->toBeNull()
        ->and($page->social_media_id)->not->toBeNull()
        ->and($page->bannerMedia->alt_text)->toBe('Services banner')
        ->and($page->socialMedia->alt_text)->toBe('Services social image');
});

test('categories tags and authors have independent admin CRUD surfaces', function (): void {
    $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Notices', 'status' => 1])->assertRedirect(route('admin.categories.index'));
    $this->actingAs($this->admin)->post(route('admin.tags.store'), ['name' => 'Public Service', 'status' => 1])->assertRedirect(route('admin.tags.index'));
    $this->actingAs($this->admin)->post(route('admin.authors.store'), ['name' => 'Office Editor', 'email' => 'editor@example.com', 'status' => 1])->assertRedirect(route('admin.authors.index'));

    expect(ContentCategory::where('slug', 'notices')->exists())->toBeTrue()
        ->and(ContentTag::where('slug', 'public-service')->exists())->toBeTrue()
        ->and(ContentAuthor::where('slug', 'office-editor')->exists())->toBeTrue();
});

test('media uploads use the local public disk', function (): void {
    Storage::fake('public');

    $this->actingAs($this->admin)->post(route('admin.media.store'), [
        'file' => UploadedFile::fake()->image('crest.png'),
        'title' => 'Office crest',
        'alt_text' => 'Office crest',
    ])->assertRedirect();

    $asset = MediaAsset::firstOrFail();
    expect($asset->disk)->toBe('public');
    Storage::disk('public')->assertExists($asset->path);
});

test('header and footer menus remain separate dynamic public positions', function (): void {
    Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Header Menu']);
    Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
    $footer = Menu::query()->where('location', 'footer')->firstOrFail();
    $page = Page::create(['title' => 'Footer Link Page', 'slug' => 'footer-link-page', 'path' => 'footer-link-page', 'status' => 'published']);

    $this->actingAs($this->admin)->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('Header Menu')
        ->assertSee('Footer Menu')
        ->assertSee('Dynamic Menus');

    $this->actingAs($this->admin)->post(route('admin.menus.store'), [
        'menu_id' => $footer->id,
        'label' => 'Footer Link',
        'page_id' => $page->id,
        'sort_order' => 1,
        'is_visible' => 1,
    ])->assertRedirect(route('admin.menus.index'));

    $this->get(route('public.home'))->assertOk()->assertSee('Footer Link');

    $header = Menu::query()->where('location', 'header')->firstOrFail();
    $secondPage = Page::create(['title' => 'Second Header Page', 'slug' => 'second-header-page', 'path' => 'second-header-page', 'status' => 'published']);
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), [
        'menu_id' => $header->id,
        'page_ids' => [$page->id, $secondPage->id],
    ])->assertRedirect(route('admin.menus.header'));

    expect($header->items()->whereIn('page_id', [$page->id, $secondPage->id])->count())->toBe(2);
});

test('content permissions are enforced for non-administrators', function (): void {
    $role = Role::create(['name' => 'content-viewer', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('admin.pages.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
});
