<?php

use App\Enums\PageType;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('public homepage renders without a page model', function (): void {
    $this->get(route('public.home'))->assertOk();
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
    $draft = Page::where('slug', 'internal-draft')->firstOrFail();
    $this->get(route('public.page', ['path' => $draft->path]))->assertNotFound();
});

test('pages store and filter by page type', function (): void {
    config()->set('settings.nepali', true);

    $response = $this->actingAs($this->admin)->get(route('admin.pages.create'))
        ->assertOk()
        ->assertSee('Page type')
        ->assertSee('Contact')
        ->assertSee('vendor/ckeditor/ckeditor.js')
        ->assertSee('vendor/ckeditor/admin-init.js')
        ->assertSee('name="translations[en][summary]"', false)
        ->assertSee('name="translations[ne][summary]"', false)
        ->assertSee('name="translations[en][body]"', false)
        ->assertSee('name="translations[ne][body]"', false)
        ->assertSee('page-tab-en')
        ->assertSee('page-tab-ne')
        ->assertSee('page-shared-tab-banner')
        ->assertSee('page-shared-tab-social')
        ->assertSee('SEO Details')
        ->assertSee('name="meta_title"', false)
        ->assertSee('name="meta_description"', false)
        ->assertDontSee('page-shared-tab-seo')
        ->assertDontSee('page-shared-panel-seo')
        ->assertSee('images/flags/en.svg')
        ->assertSee('images/flags/np.svg')
        ->assertDontSee('Shared by English and Nepali pages.')
        ->assertDontSee('SEO (shared)')
        ->assertDontSee('HTML is submitted; sanitize rich text on the server before storing or rendering it.')
        ->assertSee('js-rich-text-editor');

    $html = $response->getContent();
    preg_match('/window\.CKEDITOR_BASEPATH\s*=\s*("[^"]*");/', $html, $basePathMatch);
    expect($basePathMatch)->toHaveKey(1)
        ->and(json_decode($basePathMatch[1], true))->toEndWith('/vendor/ckeditor/');
    expect(strpos($html, 'name="translations[ne][body]"'))->toBeLessThan(strpos($html, 'vendor/ckeditor/admin-init.js'));

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Contact Page',
        'page_type' => PageType::ContactUs->value,
        'status' => 'draft',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('slug', 'contact-page')->firstOrFail();
    expect($page->page_type)->toBe(PageType::ContactUs);

    $this->actingAs($this->admin)->get(route('admin.pages.index', ['page_type' => PageType::ContactUs->value]))
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

test('pages accept a custom URL slug', function (): void {
    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'title' => 'Office Contact', 'slug' => 'contact-office', 'status' => 'draft',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('slug', 'contact-office')->firstOrFail();
    expect($page->path)->toBe('contact-office');
});

test('page translations are saved, edited, and displayed by language', function (): void {
    config()->set('settings.nepali', true);

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'translations' => [
            'en' => ['title' => 'Welcome Office', 'summary' => '<p>English summary</p>', 'body' => '<p>English body</p>'],
            'ne' => ['title' => 'स्वागत कार्यालय', 'summary' => '<p>नेपाली सारांश</p>', 'body' => '<p>नेपाली सामग्री</p>'],
        ],
        'status' => 'published',
        'meta_title' => 'Shared SEO title',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('slug', 'welcome-office')->firstOrFail();
    expect($page->getTranslation('title', 'en'))->toBe('Welcome Office')
        ->and($page->getTranslation('title', 'ne'))->toBe('स्वागत कार्यालय')
        ->and($page->getTranslation('summary', 'ne'))->toBe('<p>नेपाली सारांश</p>')
        ->and($page->getTranslation('body', 'ne'))->toBe('<p>नेपाली सामग्री</p>')
        ->and($page->meta_title)->toBe('Shared SEO title');

    $this->actingAs($this->admin)->get(route('admin.pages.index', ['search' => 'स्वागत']))
        ->assertOk()->assertSee('Welcome Office');

    $this->get(route('public.page', ['path' => $page->path]))->assertOk()->assertSee('English body');
    $this->get(route('public.page', ['path' => $page->path, 'lang' => 'ne']))->assertOk()->assertSee('स्वागत कार्यालय')->assertSee('नेपाली सामग्री')->assertSee('lang="ne"', false);
    $this->get(route('public.page', ['path' => $page->path]))->assertOk()->assertSee('नेपाली सामग्री');
    $this->actingAs($this->admin)->get(route('admin.pages.edit', $page))->assertOk()->assertSee('स्वागत कार्यालय');

    $this->actingAs($this->admin)->put(route('admin.pages.update', $page), [
        'translations' => [
            'en' => ['title' => 'Welcome Office', 'summary' => 'English summary', 'body' => 'English body'],
            'ne' => ['title' => 'नयाँ शीर्षक', 'summary' => 'नयाँ सारांश', 'body' => 'नयाँ सामग्री'],
        ],
        'status' => 'published',
    ])->assertRedirect(route('admin.pages.index'));

    expect($page->refresh()->path)->toBe('welcome-office')
        ->and($page->getTranslation('title', 'ne'))->toBe('नयाँ शीर्षक');
});

test('missing Nepali content falls back to English and English title is required', function (): void {
    config()->set('settings.nepali', true);

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'translations' => ['en' => ['title' => '', 'body' => 'Body']], 'status' => 'draft',
    ])->assertSessionHasErrors('translations.en.title');

    $this->actingAs($this->admin)->post(route('admin.pages.store'), [
        'translations' => ['en' => ['title' => 'English Only', 'body' => '<p>Fallback body</p>']], 'status' => 'published',
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('slug', 'english-only')->firstOrFail();
    $this->get(route('public.page', ['path' => $page->path, 'lang' => 'ne']))
        ->assertOk()->assertSee('English Only')->assertSee('Fallback body');
    $this->get(route('public.page', ['path' => $page->path, 'lang' => 'fr']))->assertNotFound();
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

test('page index shows compact columns and selected-page actions', function (): void {
    Page::factory()->create(['title' => 'Selected Page']);

    $this->actingAs($this->admin)->get(route('admin.pages.index'))
        ->assertOk()
        ->assertSee('Bulk delete')
        ->assertSee('Publish')
        ->assertSee('Unpublish')
        ->assertSee('Select all displayed pages')
        ->assertSee('table-checkbox-tick', false)
        ->assertSee('page-status-control', false)
        ->assertSee('page-status-icon h-7 w-7', false)
        ->assertSee('x-model="selected"', false)
        ->assertSee('x-effect="$el.indeterminate', false)
        ->assertDontSee('>Order</th>', false)
        ->assertSee('Created date / Actions')
        ->assertDontSee('title="Edit page"', false)
        ->assertDontSee('group-hover:block')
        ->assertSee('Selected Page')
        ->assertDontSee('S.N.')
        ->assertDontSee('<th class="px-3 py-3">Type</th>', false)
        ->assertDontSee('<th class="px-3 py-3">Path</th>', false);
});

test('selected pages can be published, unpublished, and deleted together', function (): void {
    $first = Page::factory()->create();
    $second = Page::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.pages.bulk-status'), [
        'pages' => [$first->id, $second->id], 'status' => 'published',
    ])->assertRedirect();

    expect($first->refresh()->status->value)->toBe('published')
        ->and($first->published_at)->not->toBeNull()
        ->and($second->refresh()->status->value)->toBe('published');

    $this->actingAs($this->admin)->patch(route('admin.pages.bulk-status'), [
        'pages' => [$first->id, $second->id], 'status' => 'draft',
    ])->assertRedirect();

    expect($first->refresh()->status->value)->toBe('draft')
        ->and($first->published_at)->toBeNull()
        ->and($second->refresh()->status->value)->toBe('draft');

    $this->actingAs($this->admin)->delete(route('admin.pages.bulk-destroy'), [
        'pages' => [$first->id, $second->id],
    ])->assertRedirect();

    expect(Page::query()->whereKey([$first->id, $second->id])->count())->toBe(0);
});

test('bulk page actions reject invalid selections and unauthorized users', function (): void {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.pages.bulk-status'), [
        'pages' => [$page->id, $page->id], 'status' => 'published',
    ])->assertSessionHasErrors('pages.1');

    $this->actingAs($this->admin)->patch(route('admin.pages.bulk-status'), [
        'pages' => [$page->id], 'status' => 'invalid',
    ])->assertSessionHasErrors('status');

    $this->actingAs($this->admin)->delete(route('admin.pages.bulk-destroy'), [
        'pages' => [$page->id, 999999],
    ])->assertSessionHasErrors('pages.1');

    expect(Page::query()->whereKey($page->id)->exists())->toBeTrue();

    $role = Role::create(['name' => 'page-viewer', 'guard_name' => 'web']);
    $role->givePermissionTo('pages.manage');
    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    $this->actingAs($viewer)->get(route('admin.pages.index'))
        ->assertOk()
        ->assertDontSee('Bulk delete')
        ->assertDontSee('name="status" value="published"', false);

    $this->actingAs($viewer)->patch(route('admin.pages.bulk-status'), [
        'pages' => [$page->id], 'status' => 'published',
    ])->assertForbidden();

    $this->actingAs($viewer)->delete(route('admin.pages.bulk-destroy'), [
        'pages' => [$page->id],
    ])->assertForbidden();
});

test('news taxonomy is not exposed in the admin', function (): void {
    $this->actingAs($this->admin)->get(route('admin.news.create'))
        ->assertOk()
        ->assertDontSee('name="category_id"', false)
        ->assertDontSee('name="author_id"', false)
        ->assertDontSee('name="tag_ids[]"', false);
});

test('team members can be created searched edited and deleted from the admin', function (): void {
    Storage::fake('public');

    $this->actingAs($this->admin)->get(route('admin.team-members.index'))
        ->assertOk()
        ->assertSee('Team member manager')
        ->assertSee('Add Team Member')
        ->assertSee('Team Members')
        ->assertSee(route('admin.team-members.index'));

    $this->actingAs($this->admin)->post(route('admin.team-members.store'), [
        'name' => 'Asha Sharma',
        'designation' => 'Program Director',
        'bio' => 'Leads community programs.',
        'photo' => UploadedFile::fake()->image('asha.jpg'),
        'photo_alt_text' => 'Asha Sharma',
        'is_active' => '1',
    ])->assertRedirect(route('admin.team-members.index'));

    $member = TeamMember::query()->with('photoMedia')->firstOrFail();
    expect($member->name)->toBe('Asha Sharma')
        ->and($member->designation)->toBe('Program Director')
        ->and($member->is_active)->toBeTrue()
        ->and($member->photoMedia->alt_text)->toBe('Asha Sharma');
    Storage::disk('public')->assertExists($member->photoMedia->path);

    $this->actingAs($this->admin)->get(route('admin.team-members.index', ['search' => 'Program', 'status' => 'active']))
        ->assertOk()
        ->assertSee('Asha Sharma')
        ->assertSee('Program Director');

    $photoId = $member->photo_media_id;
    $this->actingAs($this->admin)->get(route('admin.team-members.edit', $member))
        ->assertOk()
        ->assertSee('Asha Sharma')
        ->assertSee('Member photo');

    $this->actingAs($this->admin)->put(route('admin.team-members.update', $member), [
        'name' => 'Asha Sharma',
        'designation' => 'Executive Director',
        'bio' => 'Updated biography.',
        'is_active' => '0',
    ])->assertRedirect(route('admin.team-members.index'));

    expect($member->fresh()->designation)->toBe('Executive Director')
        ->and($member->fresh()->bio)->toBe('Updated biography.')
        ->and($member->fresh()->is_active)->toBeFalse()
        ->and($member->fresh()->photo_media_id)->toBe($photoId);

    $this->actingAs($this->admin)->delete(route('admin.team-members.destroy', $member))
        ->assertRedirect()
        ->assertSessionHas('success');
    expect(TeamMember::query()->whereKey($member->id)->exists())->toBeFalse();
    Storage::disk('public')->assertExists($member->photoMedia->path);
});

test('team member requests validate required fields and enforce permissions', function (): void {
    $this->actingAs($this->admin)->post(route('admin.team-members.store'), [
        'name' => '',
        'designation' => '',
    ])->assertSessionHasErrors(['name', 'designation', 'photo']);

    $role = Role::create(['name' => 'team-member-viewer', 'guard_name' => 'web']);
    $role->givePermissionTo('team-members.manage');
    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    $this->actingAs($viewer)->get(route('admin.team-members.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.team-members.create'))->assertForbidden();
    $this->actingAs($viewer)->post(route('admin.team-members.store'), [])->assertForbidden();
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

test('main and footer menus remain separate dynamic public positions', function (): void {
    Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
    $footer = Menu::query()->where('location', 'footer')->firstOrFail();
    $page = Page::create(['title' => 'Footer Link Page', 'slug' => 'footer-link-page', 'path' => 'footer-link-page', 'status' => 'published']);

    $this->actingAs($this->admin)->get(route('admin.menus.index'))
        ->assertOk()
        ->assertSee('Main Menu')
        ->assertSee('Footer Menu')
        ->assertSee('Dynamic Menus');

    $this->actingAs($this->admin)->post(route('admin.menus.assign'), [
        'menu_id' => $footer->id,
        'page_ids' => [$page->id],
    ])->assertRedirect(route('admin.menus.footer'));

    $this->get(route('public.home'))->assertOk()->assertSee('Footer Link Page');

    $header = Menu::query()->where('location', 'header')->firstOrFail();
    $secondPage = Page::create(['title' => 'Second Header Page', 'slug' => 'second-header-page', 'path' => 'second-header-page', 'status' => 'published']);
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), [
        'menu_id' => $header->id,
        'page_ids' => [$page->id, $secondPage->id],
    ])->assertRedirect(route('admin.menus.header'));

    expect($header->items()->whereIn('page_id', [$page->id, $secondPage->id])->count())->toBe(2);
});

test('menu position pages always show the assignment dropdown', function (): void {
    $main = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);

    $this->actingAs($this->admin)->get(route('admin.menus.header'))
        ->assertOk()->assertSee('Assign pages')->assertSee('Search and select pages')->assertSee('name="page_ids[]"', false);

    $page = Page::create(['title' => 'Menu Selectable', 'slug' => 'menu-selectable', 'path' => 'menu-selectable', 'status' => 'published']);
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $main->id, 'page_ids' => [$page->id]])->assertRedirect(route('admin.menus.header'));
    $this->actingAs($this->admin)->get(route('admin.menus.header'))
        ->assertOk()->assertSee('Assign pages')->assertSee('Search and select pages')->assertSee('name="page_ids[]"', false);
    $this->actingAs($this->admin)->get(route('admin.menus.footer'))
        ->assertOk()->assertSee('Assign pages')->assertSee('name="page_ids[]"', false);
    $this->actingAs($this->admin)->get(route('admin.pages.create'))->assertOk()->assertDontSee('Show in menus');
});

test('assigning nested pages keeps their menu hierarchy even when the parent is assigned later', function (): void {
    $menu = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::create(['title' => 'About', 'slug' => 'about', 'path' => 'about', 'status' => 'published']);
    $child = Page::create(['title' => 'Team', 'parent_id' => $parent->id, 'slug' => 'team', 'path' => 'about/team', 'status' => 'published']);
    $grandchild = Page::create(['title' => 'Leadership', 'parent_id' => $child->id, 'slug' => 'leadership', 'path' => 'about/team/leadership', 'status' => 'published']);

    $this->actingAs($this->admin)->get(route('admin.menus.header'))->assertOk()->assertSee('Team (about', false);
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$grandchild->id, $child->id]])->assertRedirect();
    $childItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $child->id)->firstOrFail();
    $grandchildItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $grandchild->id)->firstOrFail();
    expect($childItem->parent_id)->toBeNull();
    expect($grandchildItem->parent_id)->toBe($childItem->id);

    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$parent->id]])->assertRedirect();
    $parentItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $parent->id)->firstOrFail();
    expect($childItem->fresh()->parent_id)->toBe($parentItem->id);
    expect($grandchildItem->fresh()->parent_id)->toBe($childItem->id);
    $this->get(route('public.home'))->assertOk()->assertSeeInOrder(['About', 'Team', 'Leadership']);

    $this->actingAs($this->admin)->delete(route('admin.menus.destroy', $parentItem))->assertRedirect();
    expect($childItem->fresh()->parent_id)->toBeNull();
    expect($grandchildItem->fresh()->parent_id)->toBe($childItem->id);
});

test('menu positions assign pages on their own screen without separate item forms', function (): void {
    $main = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $footer = Menu::query()->firstOrCreate(['location' => 'footer'], ['name' => 'Footer Menu']);
    $page = Page::create(['title' => 'Menu Target', 'slug' => 'menu-target', 'path' => 'menu-target', 'status' => 'published']);

    $this->actingAs($this->admin)->get(route('admin.menus.header'))
        ->assertOk()->assertSee('Assign Menu')->assertDontSee('Add menu item');
    $this->actingAs($this->admin)->get('/admin/menus/create')->assertNotFound();
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $main->id, 'page_ids' => [$page->id]])->assertRedirect(route('admin.menus.header'));
    $this->actingAs($this->admin)->get(route('admin.menus.header'))->assertSee('Menu Target')->assertSee('Delete');
    expect($main->items()->where('page_id', $page->id)->exists())->toBeTrue();
    expect($footer->items()->where('page_id', $page->id)->exists())->toBeFalse();
});

test('menu manager renders reference controls and reorders sibling groups', function (): void {
    $menu = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::create(['title' => 'About', 'slug' => 'about-menu', 'path' => 'about-menu', 'status' => 'published']);
    $otherRoot = Page::create(['title' => 'Contact', 'slug' => 'contact-menu', 'path' => 'contact-menu', 'status' => 'published']);
    $firstChild = Page::create(['title' => 'History', 'parent_id' => $parent->id, 'slug' => 'history-menu', 'path' => 'about-menu/history-menu', 'status' => 'published']);
    $secondChild = Page::create(['title' => 'Team', 'parent_id' => $parent->id, 'slug' => 'team-menu', 'path' => 'about-menu/team-menu', 'status' => 'published']);

    $this->actingAs($this->admin)->post(route('admin.menus.assign'), [
        'menu_id' => $menu->id,
        'page_ids' => [$parent->id, $otherRoot->id, $firstChild->id, $secondChild->id],
    ])->assertRedirect(route('admin.menus.header'));

    $this->actingAs($this->admin)->get(route('admin.menus.header'))
        ->assertOk()
        ->assertSee('Main Menu Manager')
        ->assertSee('S.N.')
        ->assertSee('Order')
        ->assertSee('Bulk Delete')
        ->assertSee('data-drag-handle', false);

    $parentItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $parent->id)->firstOrFail();
    $otherRootItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $otherRoot->id)->firstOrFail();
    $firstChildItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $firstChild->id)->firstOrFail();
    $secondChildItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $secondChild->id)->firstOrFail();

    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $menu->id,
        'parent_id' => null,
        'menu_items' => [$otherRootItem->id, $parentItem->id],
    ])->assertOk()->assertJson(['message' => 'Menu order updated.']);

    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $menu->id,
        'parent_id' => $parentItem->id,
        'menu_items' => [$secondChildItem->id, $firstChildItem->id],
    ])->assertOk();

    expect($otherRootItem->fresh()->sort_order)->toBe(0)
        ->and($parentItem->fresh()->sort_order)->toBe(1)
        ->and($secondChildItem->fresh()->sort_order)->toBe(0)
        ->and($firstChildItem->fresh()->sort_order)->toBe(1);

    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $menu->id,
        'parent_id' => null,
        'menu_items' => [$parentItem->id],
    ])->assertUnprocessable()->assertJsonValidationErrors('menu_items');

    $this->actingAs($this->admin)->patchJson(route('admin.menus.order'), [
        'menu_id' => $menu->id,
        'parent_id' => null,
        'menu_items' => [$parentItem->id, $firstChildItem->id],
    ])->assertUnprocessable()->assertJsonValidationErrors('menu_items');
});

test('menu manager bulk deletes selections and preserves surviving descendants', function (): void {
    $menu = Menu::query()->firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $parent = Page::create(['title' => 'Parent Link', 'slug' => 'parent-link', 'path' => 'parent-link', 'status' => 'published']);
    $child = Page::create(['title' => 'Child Link', 'parent_id' => $parent->id, 'slug' => 'child-link', 'path' => 'parent-link/child-link', 'status' => 'published']);
    $grandchild = Page::create(['title' => 'Surviving Link', 'parent_id' => $child->id, 'slug' => 'surviving-link', 'path' => 'parent-link/child-link/surviving-link', 'status' => 'published']);

    $this->actingAs($this->admin)->post(route('admin.menus.assign'), [
        'menu_id' => $menu->id,
        'page_ids' => [$parent->id, $child->id, $grandchild->id],
    ])->assertRedirect();

    $parentItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $parent->id)->firstOrFail();
    $childItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $child->id)->firstOrFail();
    $grandchildItem = MenuItem::query()->where('menu_id', $menu->id)->where('page_id', $grandchild->id)->firstOrFail();

    $this->actingAs($this->admin)->delete(route('admin.menus.bulk-destroy'), [
        'menu_id' => $menu->id,
        'menu_items' => [$parentItem->id, $childItem->id],
    ])->assertRedirect()->assertSessionHas('success');

    expect(MenuItem::query()->whereKey([$parentItem->id, $childItem->id])->count())->toBe(0)
        ->and($grandchildItem->fresh()->parent_id)->toBeNull();

    $this->actingAs($this->admin)->delete(route('admin.menus.bulk-destroy'), [
        'menu_id' => $menu->id,
        'menu_items' => [$grandchildItem->id, $grandchildItem->id],
    ])->assertSessionHasErrors('menu_items.1');

    $viewer = User::factory()->create();
    $this->actingAs($viewer)->patchJson(route('admin.menus.order'), [
        'menu_id' => $menu->id,
        'parent_id' => null,
        'menu_items' => [$grandchildItem->id],
    ])->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.menus.bulk-destroy'), [
        'menu_id' => $menu->id,
        'menu_items' => [$grandchildItem->id],
    ])->assertForbidden();
});

test('content permissions are enforced for non-administrators', function (): void {
    $role = Role::create(['name' => 'content-viewer', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('admin.pages.index'))->assertForbidden();
});
