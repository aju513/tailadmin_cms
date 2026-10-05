<?php

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Mail\FrontendContactMessage;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\News;
use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Page;
use App\Models\ResourceCategory;
use App\Models\ResourceDocument;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Frontend\SafeHtml;
use App\Services\Frontend\VideoEmbedService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['cache.default' => 'array', 'app.url' => 'https://lumbini.example', 'frontend.sitemap_chunk_size' => 2]);
    Cache::flush();
});

test('contact messages validate fields and use the configured office recipient', function (): void {
    Mail::fake();
    SiteSetting::create(['key' => 'email', 'value' => 'office@example.test', 'type' => 'text']);
    $message = ['name' => 'Visitor', 'mail' => 'visitor@example.test', 'phone' => '9800000000', 'country' => 'NEP', 'message' => 'Please share information about the hall.'];

    $this->from('/contact')->post('/contact', [...$message, 'mail' => 'invalid'])->assertSessionHasErrors('mail');
    Mail::assertNothingSent();
    $this->from('/contact')->post('/contact', $message)->assertRedirect('/contact')->assertSessionHas('contact_success');
    Mail::assertSent(FrontendContactMessage::class, fn ($mail) => $mail->hasTo('office@example.test') && $mail->details['message'] === $message['message']);
});

test('contact failures never show a false delivery confirmation', function (): void {
    Mail::fake();
    $this->from('/contact')->post('/contact', ['name' => 'Visitor', 'mail' => 'visitor@example.test', 'phone' => '9800000000', 'country' => 'NEP', 'message' => 'Hello'])->assertSessionHasErrors('message')->assertSessionMissing('contact_success');
    Mail::assertNothingSent();
});

test('design settings require authorization and cannot overwrite dedicated capacity reports', function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $admin = User::findOrFail(1);
    $ordinary = User::factory()->create(['status' => 'active']);
    $this->actingAs($ordinary)->put(route('admin.settings.update'), ['site_name' => 'Institute'])->assertForbidden();
    $report = ['year' => '2081/82', 'development' => ['training_programs' => -1], 'collaboration' => ['participants' => 20]];
    $this->actingAs($admin)->put(route('admin.settings.update'), ['site_name' => 'Institute', 'capacity_reports' => [$report]])->assertSessionHasErrors('capacity_reports');
    $this->actingAs($admin)->put(route('admin.settings.update'), ['site_name' => 'Institute'])->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('site_settings', ['key' => 'capacity_reports']);
});

test('sitemaps exclude private publications and split public catalogues', function (): void {
    News::factory()->count(3)->create(['status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    News::factory()->create(['slug' => 'private-story', 'status' => ContentStatus::Draft]);
    News::factory()->create(['slug' => 'scheduled-story', 'status' => ContentStatus::Published, 'published_at' => now()->addDay()]);

    $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('/sitemaps/news-2.xml');
    $first = simplexml_load_string($this->get('/sitemaps/news-1.xml')->assertOk()->getContent());
    $second = simplexml_load_string($this->get('/sitemaps/news-2.xml')->assertOk()->getContent());
    expect(count($first->url))->toBe(2)->and(count($second->url))->toBe(1);
    foreach ([$first, $second] as $xml) {
        foreach ($xml->url as $entry) {
            expect((string) $entry->loc)->toStartWith('https://lumbini.example/news/')->not->toContain('private-story')->not->toContain('scheduled-story');
            expect((string) $entry->lastmod)->not->toBe('');
        }
    }
    $this->get('/sitemaps/news-3.xml')->assertNotFound();
    $this->get('/sitemaps/users-1.xml')->assertNotFound();
    $this->get('/sitemaps/news-0.xml')->assertNotFound();
});

test('inactive notice categories prevent discovery and direct access', function (): void {
    $category = NoticeCategory::factory()->create(['is_active' => false]);
    $notice = Notice::factory()->create(['title' => 'Hidden category notice', 'notice_category_id' => $category->id, 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    $this->get('/notices/'.$notice->slug)->assertNotFound();
    $this->get('/search?q=Hidden')->assertOk()->assertDontSee('Hidden category notice');
    $this->get('/sitemap.xml')->assertOk()->assertDontSee('sitemaps/notices-1.xml');
});

test('a resource page at the catalogue URL keeps its category selection', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('documents/example.pdf', 'example');
    $asset = MediaAsset::create(['disk' => 'public', 'path' => 'documents/example.pdf', 'original_name' => 'example.pdf', 'mime_type' => 'application/pdf', 'size' => 7]);
    $selected = ResourceCategory::factory()->create(['is_active' => true]);
    $other = ResourceCategory::factory()->create(['is_active' => true]);
    Page::factory()->create(['path' => 'resources', 'slug' => 'resources', 'title' => ['en' => 'Legal Documents'], 'page_type' => PageType::Resource, 'resource_category_id' => $selected->id, 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    ResourceDocument::factory()->create(['title' => 'Selected publication', 'resource_category_id' => $selected->id, 'file_media_id' => $asset->id, 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    ResourceDocument::factory()->create(['title' => 'Other publication', 'resource_category_id' => $other->id, 'file_media_id' => $asset->id, 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    $this->get('/resources')->assertOk()->assertSee('Legal Documents')->assertSee('Selected publication')->assertDontSee('Other publication');
});

test('public HTML strips scripts and unsafe links and preserves editor formatting', function (): void {
    $page = Page::factory()->create(['body' => ['en' => '<h2>Welcome</h2><p><strong>Safe text</strong><script>alert(1)</script><a href="javascript:alert(1)" onclick="alert(1)">Link</a></p>'], 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    $this->get('/'.$page->path)->assertOk()->assertSee('<strong>Safe text</strong>', false)->assertDontSee('javascript:alert', false)->assertDontSee('<script>alert', false)->assertDontSee('onclick=', false);
});

test('news schema is valid JSON with canonical URLs and escaped script delimiters', function (): void {
    $news = News::factory()->create(['title' => 'Council "update" </script>', 'slug' => 'council-update', 'body' => '<p>Public story</p>', 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    $html = $this->get('/news/'.$news->slug)->assertOk()->getContent();
    preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $match);
    $schema = json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
    expect(collect($schema['@graph'])->pluck('@type'))->toContain('NewsArticle', 'Organization', 'BreadcrumbList');
    expect($html)->toContain('https://lumbini.example/news/council-update')->not->toContain('</script>"');
});

test('query validation fails safely and search pages are excluded from indexing', function (): void {
    $this->get('/search?q[]=invalid')->assertNotFound();
    $this->get('/halls?page=-1')->assertNotFound();
    $this->get('/search?q=training')->assertOk()->assertSee('name="robots" content="noindex,follow"', false);
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('https://lumbini.example/sitemap.xml');
});

test('inactive team members remain private', function (): void {
    $member = TeamMember::factory()->create(['is_active' => false]);
    $this->get('/team/'.$member->id)->assertNotFound();
    $this->get('/team')->assertOk()->assertDontSee($member->name);
});

test('video providers reject lookalike domains and unsafe protocols', function (): void {
    $embeds = app(VideoEmbedService::class);
    expect($embeds->url('https://youtu.be/dQw4w9WgXcQ'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    expect($embeds->url('https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ'))->toBeNull();
    expect($embeds->url('javascript:alert(1)'))->toBeNull();
    expect(app(SafeHtml::class)->safeUrl('//evil.example'))->toBeFalse();
});

test('menu links require permission and validate URL protocols', function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $admin = User::findOrFail(1);
    $menu = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Menu']);
    $user = User::factory()->create(['status' => 'active']);
    $this->actingAs($user)->post(route('admin.menus.links.store'), ['menu_id' => $menu->id, 'label' => 'Halls', 'external_url' => '/halls'])->assertForbidden();
    $this->actingAs($admin)->post(route('admin.menus.links.store'), ['menu_id' => $menu->id, 'label' => 'Unsafe', 'external_url' => 'javascript:alert(1)'])->assertSessionHasErrors('external_url');
    $this->actingAs($admin)->post(route('admin.menus.links.store'), ['menu_id' => $menu->id, 'label' => 'Our Halls', 'external_url' => '/halls'])->assertRedirect();
    $this->assertDatabaseHas('menu_items', ['menu_id' => $menu->id, 'label' => 'Our Halls', 'external_url' => '/halls']);
});
