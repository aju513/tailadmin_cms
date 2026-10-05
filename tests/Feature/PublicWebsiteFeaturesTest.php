<?php

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\GalleryAlbum;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Video;
use App\Services\SiteSettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['app.url' => 'https://lumbini.example', 'cache.default' => 'array', 'frontend.translation.mode' => 'gtranslate']);
    Cache::flush();
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->published = ['status' => ContentStatus::Published, 'published_at' => now()->subHour()];
    $this->photo = MediaAsset::create(['disk' => 'public', 'path' => 'gallery/photo.jpg', 'original_name' => 'photo.jpg', 'mime_type' => 'image/jpeg', 'size' => 100]);
    Storage::disk('public')->put($this->photo->path, 'image');
});

test('website search includes descriptive content and active team members but excludes hidden records', function (): void {
    Page::factory()->create([...$this->published, 'title' => ['en' => 'Public document'], 'body' => ['en' => '<p>Needle content</p>']]);
    Video::factory()->create([...$this->published, 'title' => 'Public video', 'description' => 'Needle content']);
    TeamMember::factory()->create(['name' => 'Needle Person', 'is_active' => true]);
    TeamMember::factory()->create(['name' => 'Needle Hidden Person', 'is_active' => false]);
    News::factory()->create(['title' => 'Needle draft', 'status' => ContentStatus::Draft]);
    Video::factory()->create([...$this->published, 'title' => 'Needle scheduled', 'published_at' => now()->addDay()]);
    $this->get('/search?q=Needle')->assertOk()->assertSee('Public document')->assertSee('Public video')->assertSee('Needle Person')
        ->assertDontSee('Needle Hidden Person')->assertDontSee('Needle draft')->assertDontSee('Needle scheduled');
    $this->get('/search?search=Needle')->assertOk()->assertSee('Public video');
    $this->get('/search')->assertOk()->assertSee('Enter a term');
    $this->get('/search?q=absent-query')->assertOk()->assertSee('No results found.');
    $this->get('/search?videos_page=-1')->assertNotFound();
});

test('website search has independent group pagination without truncating available matches', function (): void {
    Video::factory()->count(13)->sequence(fn ($sequence) => ['title' => 'Matching video '.$sequence->index, 'sort_order' => $sequence->index])->create($this->published);
    News::factory()->count(13)->create([...$this->published, 'title' => 'Matching news']);
    $this->get('/search?q=Matching&videos_page=2')->assertOk()->assertSee('Matching video 12')->assertDontSee('Matching video 0')
        ->assertViewHas('results', fn ($results) => $results['videos']->currentPage() === 2 && $results['videos']->total() === 13 && $results['news']->currentPage() === 1)
        ->assertSee('news_page=2', false)->assertSee('q=Matching', false);
});

test('video catalogues and typed CMS pages share searchable paginated lightbox cards', function (): void {
    $selected = Video::factory()->create([...$this->published, 'title' => 'Selected video']);
    Video::factory()->create([...$this->published, 'title' => 'Other video']);
    Page::factory()->create([...$this->published, 'title' => ['en' => 'Media videos'], 'slug' => 'training-videos', 'path' => 'media/training-videos', 'page_type' => PageType::Videos]);
    foreach (['/videos', '/media/training-videos'] as $path) {
        $this->get($path.'?q=Selected')->assertOk()->assertSee('Selected video')->assertDontSee('Other video')
            ->assertSee('data-fancybox="video-catalogue"', false)->assertSee('data-type="iframe"', false)->assertSee('youtube-nocookie.com/embed/', false)->assertDontSee('<iframe', false);
    }
    $this->get('/videos/'.$selected->slug)->assertOk()->assertSee('data-fancybox="video-detail"', false);
    Video::factory()->count(12)->create($this->published);
    $this->get('/videos')->assertOk()->assertSee('page=2', false);
    $this->get('/videos?q=missing')->assertOk()->assertSee('No videos match your search.');
});

test('unsupported video providers stay external and do not become arbitrary iframe sources', function (): void {
    $video = Video::factory()->create([...$this->published, 'video_url' => 'https://example.test/video']);
    $this->get('/videos/'.$video->slug)->assertOk()->assertSee('href="https://example.test/video"', false)->assertSee('rel="noopener noreferrer"', false)->assertDontSee('data-fancybox="video-detail"', false);
});

test('albums open a full ordered photo grid and both media page types use the existing catalogue', function (): void {
    $album = GalleryAlbum::factory()->create([...$this->published, 'title' => 'Training album']);
    $album->photos()->create(['media_asset_id' => $this->photo->id, 'sort_order' => 1, 'caption' => '<script>alert(1)</script>']);
    $second = MediaAsset::create(['disk' => 'public', 'path' => 'gallery/second.jpg', 'original_name' => 'second.jpg', 'mime_type' => 'image/jpeg', 'size' => 100]);
    $album->photos()->create(['media_asset_id' => $second->id, 'sort_order' => 2]);
    Page::factory()->create([...$this->published, 'title' => ['en' => 'Our albums'], 'slug' => 'albums', 'path' => 'media/albums', 'page_type' => PageType::Gallery]);
    foreach (['/gallery', '/media/albums'] as $path) {
        $this->get($path.'?q=Training')->assertOk()->assertSee('Open album: Training album')->assertSee(route('public.gallery.show', $album->slug), false)->assertSee('2 photos');
    }
    $this->get('/gallery/'.$album->slug)->assertOk()->assertSee($this->photo->url(), false)->assertSee($second->url(), false)->assertSee('data-fancybox="album-'.$album->id.'"', false)->assertDontSee('data-caption="<script>', false);
    $this->get('/gallery?q=missing')->assertOk()->assertSee('No albums match your search.');
});

test('homepage photos and supported videos expose lightbox controls', function (): void {
    $album = GalleryAlbum::factory()->create($this->published);
    $album->photos()->create(['media_asset_id' => $this->photo->id, 'sort_order' => 0]);
    Video::factory()->create($this->published);
    $this->get('/')->assertOk()->assertSee('data-home-gallery-open', false)->assertSee('data-fancybox="homepage-gallery"', false)
        ->assertSee('data-fancybox="homepage-video"', false)->assertSee('data-type="iframe"', false);
});

test('site settings update public identity contact information social links and organization schema', function (): void {
    $this->get('/contact')->assertOk();
    $data = ['site_name' => 'Managed Organization', 'website_url' => 'https://office.example.test', 'email' => 'site@example.test', 'address' => 'Managed Address', 'phone' => '0100000', 'contact_officer_name' => 'Contact Person', 'contact_officer_phone' => '9800000000', 'map_url' => 'https://www.google.com/maps/embed?pb=test', 'social_links' => [['label' => 'YouTube', 'url' => 'https://youtube.com/@office'], ['label' => 'Hidden Facebook', 'url' => '#'], ['label' => 'Blank Link', 'url' => null]]];
    $this->actingAs($this->admin)->put(route('admin.settings.update'), $data)->assertSessionHasNoErrors();
    $this->get('/contact')->assertOk()->assertSee('Managed Organization')->assertSee('Managed Address')->assertSee('site@example.test')->assertSee('Contact Person')->assertSee('9800000000')
        ->assertSee('https://office.example.test', false)->assertSee('https://youtube.com/@office', false)->assertDontSee('Hidden Facebook')->assertDontSee('Blank Link')
        ->assertSee('Office location: Managed Organization')->assertSee('lg:grid-cols-2', false)->assertDontSee('action="https://lumbini.example/contact"', false)->assertDontSee('Send us a message');
    $html = $this->get('/')->assertOk()->getContent();
    preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $match);
    $organization = collect(json_decode($match[1], true)['@graph'])->firstWhere('@type', 'Organization');
    expect($organization['name'])->toBe('Managed Organization')->and($organization['url'])->toBe('https://office.example.test')->and($organization['sameAs'])->toBe(['https://youtube.com/@office']);
});

test('social links retain legacy values until first save and an explicitly empty list stays empty', function (): void {
    SiteSetting::create(['key' => 'facebook_url', 'value' => 'https://facebook.com/legacy', 'type' => 'text']);
    expect(app(SiteSettingService::class)->all()['social_links'])->toBe([['label' => 'Facebook', 'url' => 'https://facebook.com/legacy']]);
    $this->actingAs($this->admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Organization name')->assertSee('Contact person 1 name')->assertSee('Add social link')->assertViewHas('settings', fn ($settings) => $settings['social_links'][0]['url'] === 'https://facebook.com/legacy');
    $this->put(route('admin.settings.update'), ['site_name' => 'Office', 'social_links_present' => 1])->assertSessionHasNoErrors();
    expect(app(SiteSettingService::class)->all()['social_links'])->toBe([]);
    $this->get('/contact')->assertOk()->assertDontSee('facebook.com/legacy');
    $this->assertDatabaseHas('site_settings', ['key' => 'facebook_url', 'value' => 'https://facebook.com/legacy']);
});

test('social links and new settings reject unsafe input and require settings permission', function (): void {
    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['site_name' => 'Office', 'website_url' => 'javascript:alert(1)', 'social_links' => [['label' => 'Bad', 'url' => 'javascript:alert(1)']]])->assertSessionHasErrors(['website_url', 'social_links.0.url']);
    $this->put(route('admin.settings.update'), ['site_name' => 'Office', 'social_links' => [['label' => '', 'url' => 'https://facebook.com/office']]])->assertSessionHasErrors('social_links.0.label');
    $this->actingAs(User::factory()->create(['status' => 'active']))->put(route('admin.settings.update'), ['site_name' => 'Office', 'website_url' => 'https://example.test'])->assertForbidden();
});

test('contact pages omit the form and never invent an office map', function (): void {
    $this->get('/contact')->assertOk()->assertDontSee('<iframe', false)->assertDontSee('name="message"', false)->assertDontSee('maps?q=Nepalgunj', false);
    app(SiteSettingService::class)->update(['map_url' => 'https://maps.app.goo.gl/example'], $this->admin);
    $this->get('/contact')->assertOk()->assertSee('View office location')->assertDontSee('<iframe', false);
    Page::factory()->create([...$this->published, 'slug' => 'contact-office', 'path' => 'contact-office', 'page_type' => PageType::ContactUs]);
    $this->get('/contact-office')->assertOk()->assertSee('View office location')->assertDontSee('name="message"', false);
});

test('automatic translation uses English source HTML independently of Nepali editing and keeps canonical URLs stable', function (): void {
    config(['settings.nepali' => true]);
    $page = Page::factory()->create([...$this->published, 'title' => ['en' => 'English title', 'ne' => 'Nepali saved title'], 'body' => ['en' => '<p>English content</p>', 'ne' => '<p>Nepali saved content</p>']]);
    $this->get('/'.$page->path.'?lang=ne')->assertOk()->assertSee('English content')->assertDontSee('Nepali saved content')->assertSee('lang="en"', false)
        ->assertSee('data-translation-mode="gtranslate"', false)->assertSee('cdn.gtranslate.net/widgets/latest/float.js', false)->assertDontSee('hreflang="ne"', false)
        ->assertSee('rel="canonical" href="https://lumbini.example/'.$page->path.'"', false);
    expect($page->fresh()->getTranslation('body', 'ne'))->toBe('<p>Nepali saved content</p>');
    config(['settings.nepali' => false]);
    $this->get('/')->assertOk()->assertSee('cdn.gtranslate.net/widgets/latest/float.js', false);
});
