<?php

use App\Models\ContentAuthor;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('admin can create news with images and publish it publicly', function (): void {
    Storage::fake('public');
    $category = ContentCategory::factory()->create();
    $author = ContentAuthor::factory()->create();
    $tag = ContentTag::factory()->create();

    $this->actingAs($this->admin)->get(route('admin.news.create'))->assertOk()->assertSee('News title')->assertSee('SEO details')->assertDontSee('name="category_id"', false)->assertDontSee('name="author_id"', false)->assertDontSee('name="tag_ids[]"', false);
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Council update', 'summary' => '<p>Summary</p>', 'body' => '<p>Full story</p>',
        'status' => 'published', 'featured' => '1', 'thumbnail' => UploadedFile::fake()->image('news.jpg'),
        'thumbnail_alt_text' => 'Council building', 'meta_title' => 'Council news',
    ])->assertRedirect(route('admin.news.index'));

    $news = News::query()->where('slug', 'council-update')->firstOrFail();
    $news->update(['category_id' => $category->id, 'author_id' => $author->id, 'featured' => true]);
    $news->tags()->sync([$tag->id]);
    expect($news->thumbnailMedia->alt_text)->toBe('Council building');
    expect($news->summary)->toBe('<p>Summary</p>');
    Storage::disk('public')->assertExists($news->thumbnailMedia->path);
    $this->get(route('public.news.index'))->assertOk()->assertSee('Council update');
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
    $role->givePermissionTo(['news.create']);
    $contributor = User::factory()->create();
    $contributor->assignRole($role);

    $this->actingAs($contributor)->get(route('admin.news.create'))->assertOk()
        ->assertDontSee('Add News Category')->assertDontSee('Manage News Tags')->assertDontSee('Manage News Authors');
});

test('news suggests editable metadata and saves generated defaults without JavaScript', function (): void {
    $this->actingAs($this->admin)->get(route('admin.news.create'))->assertOk()
        ->assertSee('Save news')->assertSee('Close')->assertSee('form="news-form"', false)
        ->assertSee('id="news-form"', false)->assertSee('newsEditor(', false)
        ->assertSee('name="summary"', false)->assertDontSee('name="excerpt"', false);

    $this->post(route('admin.news.store'), [
        'title' => 'Council Meeting 2026', 'slug' => '', 'meta_title' => '',
        'summary' => '<p>Meeting summary for residents.</p>', 'status' => 'published',
    ])->assertRedirect(route('admin.news.index'))->assertSessionHasNoErrors();

    $news = News::query()->where('slug', 'council-meeting-2026')->firstOrFail();
    expect($news->meta_title)->toBe('Council Meeting 2026')
        ->and($news->summary)->toBe('<p>Meeting summary for residents.</p>');

    $this->get(route('admin.news.edit', $news))->assertOk()
        ->assertSee('Save news')->assertSee('form="news-form"', false)->assertSee('Summary');
    $this->get(route('admin.news.show', $news))->assertOk()->assertSee('Meeting summary for residents.');
    $this->get(route('public.news.show', $news->slug))->assertOk()
        ->assertSee('<title>Council Meeting 2026</title>', false)
        ->assertSee('content="Meeting summary for residents."', false);
    $this->get(route('public.search', ['q' => 'Council Meeting']))->assertOk()->assertSee('Meeting summary for residents.');
});

test('news preserves editable custom metadata and permits its own slug on update', function (): void {
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Original article', 'slug' => 'Custom-Article-2026', 'meta_title' => 'Custom search headline',
    ])->assertRedirect(route('admin.news.index'))->assertSessionHasNoErrors();

    $news = News::query()->where('slug', 'custom-article-2026')->firstOrFail();
    $this->put(route('admin.news.update', $news), [
        'title' => 'Updated article', 'slug' => $news->slug, 'meta_title' => 'Custom search headline',
        'summary' => '<p>Updated summary</p>',
    ])->assertRedirect(route('admin.news.index'))->assertSessionHasNoErrors();

    expect($news->refresh()->slug)->toBe('custom-article-2026')
        ->and($news->meta_title)->toBe('Custom search headline')
        ->and($news->summary)->toBe('<p>Updated summary</p>');

    $this->put(route('admin.news.update', $news), [
        'title' => 'Updated article', 'slug' => 'New-Article-Address', 'meta_title' => '',
    ])->assertRedirect(route('admin.news.index'))->assertSessionHasNoErrors();

    expect($news->refresh()->slug)->toBe('new-article-address')
        ->and($news->meta_title)->toBe('Updated article');
});

test('news rejects malformed slugs and oversized editor fields', function (array $input, string $field): void {
    $this->actingAs($this->admin)->post(route('admin.news.store'), array_merge(['title' => 'Valid title'], $input))
        ->assertSessionHasErrors($field);

    $this->assertDatabaseCount('news', 0);
})->with([
    'full URL' => [['slug' => 'https://example.com/news/story'], 'slug'],
    'spaces' => [['slug' => 'two words'], 'slug'],
    'underscore' => [['slug' => 'two_words'], 'slug'],
    'repeated hyphen' => [['slug' => 'two--words'], 'slug'],
    'leading hyphen' => [['slug' => '-story'], 'slug'],
    'trailing hyphen' => [['slug' => 'story-'], 'slug'],
    'non ASCII alias' => [['slug' => 'समाचार'], 'slug'],
    'long slug' => [['slug' => str_repeat('a', 256)], 'slug'],
    'long generated slug' => [['title' => str_repeat('æ', 255)], 'slug'],
    'unusable generated slug' => [['title' => '!!!'], 'slug'],
    'long SEO title' => [['meta_title' => str_repeat('a', 256)], 'meta_title'],
    'non string SEO title' => [['meta_title' => ['invalid']], 'meta_title'],
    'long summary' => [['summary' => str_repeat('a', 10001)], 'summary'],
]);

test('news validates normalized duplicate slugs and preserves input after failure', function (): void {
    $existing = News::factory()->create(['slug' => 'existing-address']);
    $news = News::factory()->create(['slug' => 'another-address']);

    $this->actingAs($this->admin)->from(route('admin.news.create'))->post(route('admin.news.store'), [
        'title' => 'New title', 'slug' => 'Existing-Address', 'meta_title' => 'Edited search title',
        'summary' => '<p>Keep my draft summary.</p>',
    ])->assertRedirect(route('admin.news.create'))->assertSessionHasErrors('slug')
        ->assertSessionHasInput('slug', 'Existing-Address')
        ->assertSessionHasInput('meta_title', 'Edited search title')
        ->assertSessionHasInput('summary', '<p>Keep my draft summary.</p>');
    $this->get(route('admin.news.create'))->assertOk()->assertSee('Edited search title')->assertSee('Keep my draft summary.');

    $this->put(route('admin.news.update', $news), [
        'title' => 'Changed title', 'slug' => $existing->slug, 'meta_title' => 'Changed SEO title',
    ])->assertSessionHasErrors('slug');
    expect($news->refresh()->slug)->toBe('another-address');
});

test('news metadata changes require edit permission', function (): void {
    $news = News::factory()->create(['slug' => 'protected-address', 'meta_title' => 'Protected SEO title']);
    $viewer = User::factory()->create();
    $role = Role::create(['name' => 'news-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('news.manage');
    $viewer->assignRole($role);

    $this->actingAs($viewer)->put(route('admin.news.update', $news), [
        'title' => 'Changed title', 'slug' => 'changed-address', 'meta_title' => 'Changed SEO title',
        'summary' => 'Changed summary',
    ])->assertForbidden();

    expect($news->refresh()->slug)->toBe('protected-address')
        ->and($news->meta_title)->toBe('Protected SEO title');
});

test('news summary migration preserves existing text in both directions', function (): void {
    $news = News::factory()->create(['summary' => '<p>Existing rich text &amp; details.</p>']);
    $migration = require database_path('migrations/2026_10_03_100000_rename_excerpt_to_summary_in_news_table.php');

    $migration->down();
    expect(Schema::hasColumn('news', 'excerpt'))->toBeTrue()
        ->and(DB::table('news')->where('id', $news->id)->value('excerpt'))->toBe('<p>Existing rich text &amp; details.</p>');

    $migration->up();
    expect(Schema::hasColumn('news', 'excerpt'))->toBeFalse()
        ->and($news->refresh()->summary)->toBe('<p>Existing rich text &amp; details.</p>');
});
