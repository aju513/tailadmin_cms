<?php

use App\Models\ContentAuthor;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('admin can create news with taxonomy and images and publish it publicly', function (): void {
    Storage::fake('public');
    $category = ContentCategory::factory()->create();
    $author = ContentAuthor::factory()->create();
    $tag = ContentTag::factory()->create();

    $this->actingAs($this->admin)->get(route('admin.news.create'))->assertOk()->assertSee('News title')->assertSee('SEO details');
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Council update', 'excerpt' => '<p>Summary</p>', 'body' => '<p>Full story</p>',
        'category_id' => $category->id, 'author_id' => $author->id, 'tag_ids' => [$tag->id],
        'status' => 'published', 'featured' => '1', 'thumbnail' => UploadedFile::fake()->image('news.jpg'),
        'thumbnail_alt_text' => 'Council building', 'meta_title' => 'Council news',
    ])->assertRedirect(route('admin.news.index'));

    $news = News::query()->where('slug', 'council-update')->firstOrFail();
    expect($news->tags->modelKeys())->toBe([$tag->id]);
    expect($news->thumbnailMedia->alt_text)->toBe('Council building');
    Storage::disk('public')->assertExists($news->thumbnailMedia->path);
    $this->get(route('public.news.index'))->assertOk()->assertSee('Council update')->assertSee('Featured news');
    $this->get(route('public.news.show', $news->slug))->assertOk()->assertSee('Full story')->assertSee('Council news');
    $this->get(route('public.news.category', $category->slug))->assertOk()->assertSee('Council update');
    $this->get(route('public.news.tag', $tag->slug))->assertOk()->assertSee('Council update');
    $this->get(route('public.news.author', $author->slug))->assertOk()->assertSee('Council update');
    $this->assertDatabaseHas('activity_log', ['event' => 'news.created', 'causer_id' => $this->admin->id]);
});

test('news drafts and scheduled articles stay private and only one article is featured', function (): void {
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'First feature', 'status' => 'published', 'featured' => '1'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Second feature', 'status' => 'published', 'featured' => '1'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Private draft', 'status' => 'draft'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Future release', 'status' => 'published', 'published_at' => now()->addDays(2)->toDateString()])->assertRedirect();

    expect(News::query()->where('featured', true)->count())->toBe(1);
    expect(News::query()->where('slug', 'second-feature')->firstOrFail()->featured)->toBeTrue();
    $this->get(route('public.news.index'))->assertOk()->assertSee('First feature')->assertSee('Second feature')->assertDontSee('Private draft')->assertDontSee('Future release');
    $this->get(route('public.news.show', 'private-draft'))->assertNotFound();
    $this->get(route('public.news.show', 'future-release'))->assertNotFound();
});

test('news validates slugs, taxonomy, and published permission', function (): void {
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Existing story'])->assertRedirect();
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Existing story'])->assertSessionHasErrors('slug');
    $this->actingAs($this->admin)->post(route('admin.news.store'), ['title' => 'Other story', 'tag_ids' => [999999]])->assertSessionHasErrors('tag_ids.0');

    $editor = User::factory()->create();
    $editor->givePermissionTo(Permission::findByName('news.create', 'web'));
    $this->actingAs($editor)->post(route('admin.news.store'), ['title' => 'Editor draft', 'status' => 'draft'])->assertRedirect();
    $this->actingAs($editor)->post(route('admin.news.store'), ['title' => 'Editor published', 'status' => 'published'])->assertSessionHasErrors('status');
    $this->assertDatabaseMissing('news', ['slug' => 'editor-published']);
});

test('news routes enforce each permission and mutations are audited', function (): void {
    $news = News::factory()->create(['created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
    $this->get(route('admin.news.index'))->assertRedirect();
    $viewer = User::factory()->create();
    $viewer->givePermissionTo(Permission::findByName('news.manage', 'web'));
    $this->actingAs($viewer)->get(route('admin.news.index'))->assertOk();
    $this->actingAs($viewer)->get(route('admin.news.show', $news))->assertForbidden();
    $this->actingAs($viewer)->get(route('admin.news.create'))->assertForbidden();
    $this->actingAs($viewer)->get(route('admin.news.edit', $news))->assertForbidden();
    $this->actingAs($viewer)->delete(route('admin.news.destroy', $news))->assertForbidden();

    $this->actingAs($this->admin)->put(route('admin.news.update', $news), ['title' => 'Changed story', 'status' => 'draft'])->assertRedirect(route('admin.news.index'));
    $news->refresh();
    expect($news->slug)->toBe('changed-story');
    $this->actingAs($this->admin)->delete(route('admin.news.destroy', $news))->assertRedirect(route('admin.news.index'));
    $this->assertDatabaseMissing('news', ['id' => $news->id]);
    $this->assertDatabaseHas('activity_log', ['event' => 'news.updated', 'causer_id' => $this->admin->id]);
    $this->assertDatabaseHas('activity_log', ['event' => 'news.deleted', 'causer_id' => $this->admin->id]);
});

test('nested news navigation exposes only independently authorized actions', function (): void {
    $role = Role::create(['name' => 'news-contributor', 'guard_name' => 'web']);
    $role->givePermissionTo(['news.create', 'categories.create', 'tags.manage']);
    $contributor = User::factory()->create();
    $contributor->assignRole($role);

    $this->actingAs($contributor)->get(route('admin.news.create'))->assertOk()
        ->assertSee('Add News Category')->assertSee('Manage News Tags')
        ->assertDontSee('Manage News Categories')->assertDontSee('Add News Tag')
        ->assertDontSee('Manage News Authors')->assertDontSee('Manage News</a>', false);
    $this->get(route('admin.categories.create'))->assertOk()->assertSee('Create category');
    $this->get(route('admin.categories.index'))->assertForbidden();
    $this->post(route('admin.categories.store'), ['name' => 'Contributor category', 'status' => 1])->assertRedirect(route('admin.categories.index'));
    $this->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
    $this->get(route('admin.tags.index'))->assertOk();
    $this->get(route('admin.tags.create'))->assertForbidden();
    $this->post(route('admin.tags.store'), ['name' => 'Unauthorized tag'])->assertForbidden();
    $this->get(route('admin.authors.create'))->assertForbidden();
});
