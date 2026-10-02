<?php

use App\Enums\PageType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Page;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->board = Page::factory()->create(['title' => 'Notice Board', 'slug' => 'notice-board', 'path' => 'notice-board', 'page_type' => PageType::Notices]);
    $this->child = Page::factory()->create(['title' => 'Tenders', 'parent_id' => $this->board->id, 'slug' => 'tenders', 'path' => 'notice-board/tenders', 'page_type' => PageType::Notices]);
});

test('notice section choices include parent and unpublished children without page management permission', function (): void {
    Page::factory()->create(['title' => 'Not a notice section', 'page_type' => PageType::Article]);
    $editor = User::factory()->create();
    $editor->givePermissionTo('notices.create');
    $this->actingAs($editor)->get(route('admin.notices.create'))->assertOk()
        ->assertSee('Notice Section')->assertSee('-- Tenders')->assertSee('Unpublished')
        ->assertSee('Notice Board')->assertDontSee('Not a notice section')->assertDontSee('Notice category');
    $this->post(route('admin.notices.store'), ['title' => 'Direct board notice', 'notice_page_id' => $this->board->id, 'status' => 'draft'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseHas('notices', ['title' => 'Direct board notice', 'notice_page_id' => $this->board->id]);
});

test('notice section validation rejects missing invalid and non-notices pages and unauthorized writes', function (): void {
    $article = Page::factory()->create(['page_type' => PageType::Article]);
    foreach ([null, 999999, $article->id] as $id) {
        $this->actingAs($this->admin)->postJson(route('admin.notices.store'), ['title' => 'Invalid assignment', 'notice_page_id' => $id, 'status' => 'draft'])
            ->assertUnprocessable()->assertJsonValidationErrors('notice_page_id');
    }
    $this->actingAs(User::factory()->create())->postJson(route('admin.notices.store'), ['title' => 'Forbidden assignment', 'notice_page_id' => $this->child->id, 'status' => 'draft'])->assertForbidden();
    $this->assertDatabaseCount('notices', 0);
});

test('legacy notices remain unassigned and keep category visibility after reassignment', function (): void {
    $category = NoticeCategory::factory()->create(['is_active' => false]);
    $legacy = Notice::factory()->create(['title' => 'Legacy announcement', 'notice_category_id' => $category->id, 'notice_page_id' => null, 'status' => 'published', 'published_at' => now()->subHour()]);
    $this->actingAs($this->admin)->get(route('admin.notices.index'))->assertOk()->assertSee('Unassigned');
    $this->put(route('admin.notices.update', $legacy), ['title' => 'Legacy announcement', 'status' => 'published', 'notice_page_id' => $this->child->id, 'notice_category_id' => 999999])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($legacy->fresh()->notice_page_id)->toBe($this->child->id)->and($legacy->fresh()->notice_category_id)->toBe($category->id);
    $this->get(route('public.notices.show', $legacy))->assertNotFound();
});

test('new section notices use active compatibility categories without reactivating inactive general categories', function (): void {
    NoticeCategory::where('slug', 'general')->update(['is_active' => false]);
    $this->actingAs($this->admin)->post(route('admin.notices.store'), ['title' => 'New section publication', 'notice_page_id' => $this->child->id, 'status' => 'published'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $notice = Notice::where('title', 'New section publication')->firstOrFail();
    expect($notice->category->is_active)->toBeTrue()->and($notice->category->slug)->toBe('notice-sections-default');
    expect(NoticeCategory::where('slug', 'general')->firstOrFail()->is_active)->toBeFalse();
    $this->get(route('public.notices.show', $notice))->assertOk();
});

test('editing a legacy notice without a category preserves its previous public visibility', function (): void {
    $notice = Notice::factory()->create(['notice_category_id' => null, 'notice_page_id' => null, 'status' => 'published', 'published_at' => now()->subHour()]);
    $this->actingAs($this->admin)->put(route('admin.notices.update', $notice), ['title' => $notice->title, 'notice_page_id' => $this->board->id, 'status' => 'published'])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($notice->fresh()->notice_category_id)->toBeNull();
    $this->get(route('public.notices.show', $notice))->assertNotFound();
});

test('section filters include descendants but exclude other sections', function (): void {
    Notice::factory()->create(['title' => 'Board-only record', 'notice_page_id' => $this->board->id]);
    Notice::factory()->create(['title' => 'Tender-only record', 'notice_page_id' => $this->child->id]);
    Notice::factory()->create(['title' => 'Unassigned record', 'notice_page_id' => null]);
    $this->actingAs($this->admin)->get(route('admin.notices.index', ['notice_page_id' => $this->board->id]))
        ->assertOk()->assertSee('Board-only record')->assertSee('Tender-only record')->assertDontSee('Unassigned record');
    $this->get(route('admin.notices.index', ['notice_page_id' => $this->child->id]))
        ->assertOk()->assertSee('Tender-only record')->assertDontSee('Board-only record');
});

test('empty section selector disables saving and category admin routes are retired', function (): void {
    $this->child->delete();
    $this->board->delete();
    $this->actingAs($this->admin)->get(route('admin.notices.create'))->assertOk()
        ->assertSee('Create a Page with type Notices')->assertSee('disabled', false)->assertSee('Create a Notice Section in Pages');
    $this->get('/admin/notice-categories')->assertNotFound();
    expect(\Illuminate\Support\Facades\Route::has('admin.notice-categories.create'))->toBeFalse();
});

test('assigned sections and ancestors cannot be deleted and assigned sections cannot change type', function (): void {
    $notice = Notice::factory()->create(['notice_page_id' => $this->child->id]);
    $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $this->board))->assertSessionHasErrors('page');
    $this->delete(route('admin.pages.destroy', $this->child))->assertSessionHasErrors('page');
    $this->delete(route('admin.pages.bulk-destroy'), ['pages' => [$this->board->id, $this->child->id]])->assertSessionHasErrors('page');
    $this->put(route('admin.pages.update', $this->child), ['title' => 'Tenders', 'slug' => 'tenders', 'parent_id' => $this->board->id, 'page_type' => 'article', 'status' => 'draft'])->assertSessionHasErrors('page_type');
    expect($this->child->fresh()->page_type)->toBe(PageType::Notices)->and($notice->fresh()->notice_page_id)->toBe($this->child->id);
    $notice->update(['notice_page_id' => $this->board->id]);
    $this->delete(route('admin.pages.destroy', $this->child))->assertRedirect()->assertSessionHasNoErrors();
});

test('menu assignment includes notice ancestors once without siblings and protects required parents', function (): void {
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $sibling = Page::factory()->create(['parent_id' => $this->board->id, 'path' => 'notice-board/jobs', 'page_type' => PageType::Notices]);
    $this->actingAs($this->admin)->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$this->child->id]])->assertRedirect()->assertSessionHasNoErrors();
    $boardItem = MenuItem::where('menu_id', $menu->id)->where('page_id', $this->board->id)->firstOrFail();
    $childItem = MenuItem::where('menu_id', $menu->id)->where('page_id', $this->child->id)->firstOrFail();
    expect($childItem->parent_id)->toBe($boardItem->id);
    $this->assertDatabaseMissing('menu_items', ['menu_id' => $menu->id, 'page_id' => $sibling->id]);
    $this->post(route('admin.menus.assign'), ['menu_id' => $menu->id, 'page_ids' => [$this->child->id]])->assertRedirect();
    expect($menu->items()->whereIn('page_id', [$this->board->id, $this->child->id])->count())->toBe(2);
    $this->delete(route('admin.menus.destroy', $boardItem))->assertSessionHasErrors('menu_items');
    $this->delete(route('admin.menus.bulk-destroy'), ['menu_id' => $menu->id, 'menu_items' => [$boardItem->id]])->assertSessionHasErrors('menu_items');
    $this->delete(route('admin.menus.bulk-destroy'), ['menu_id' => $menu->id, 'menu_items' => [$boardItem->id, $childItem->id]])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('menu_items', ['id' => $boardItem->id]);
    $this->assertDatabaseMissing('menu_items', ['id' => $childItem->id]);
});
