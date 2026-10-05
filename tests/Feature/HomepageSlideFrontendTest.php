<?php

use App\Enums\ContentStatus;
use App\Models\HomepageSlide;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Frontend\FrontendService;
use App\Services\HomepageSlideService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['cache.default' => 'array', 'services.tims.enabled' => false]);
    Cache::flush();
    Storage::fake('public');
    $this->makeBannerImage = function (string $path): MediaAsset {
        Storage::disk('public')->put($path, 'banner image');

        return MediaAsset::create(['disk' => 'public', 'path' => $path, 'original_name' => basename($path), 'mime_type' => 'image/jpeg', 'size' => 12]);
    };
});

test('homepage banner keeps original left content and buttons alongside ordered slide images and captions', function (): void {
    $later = HomepageSlide::create(['title' => 'Later slide', 'subtitle' => 'Second subtitle', 'link_url' => 'https://example.com/later', 'media_id' => ($this->makeBannerImage)('banners/later.jpg')->id, 'status' => ContentStatus::Published, 'sort_order' => 9]);
    $first = HomepageSlide::create(['title' => 'First admin slide', 'subtitle' => 'First admin subtitle', 'link_url' => 'https://example.com/first', 'media_id' => ($this->makeBannerImage)('banners/first.jpg')->id, 'status' => ContentStatus::Published, 'sort_order' => 2]);

    $this->get(route('public.home'))->assertOk()
        ->assertViewHas('bannerSlides', fn ($slides) => $slides->pluck('id')->all() === [$first->id, $later->id])
        ->assertSee('<h1>'.e(config('frontend.hero_title')).'</h1>', false)->assertSee(config('frontend.hero_description'))
        ->assertSeeInOrder(['homepage__banner-caption">First admin slide', 'homepage__banner-caption">Later slide'], false)
        ->assertDontSee('First admin subtitle')->assertDontSee('Second subtitle')
        ->assertDontSee('https://example.com/first', false)->assertDontSee('https://example.com/later', false)
        ->assertDontSee('data-banner-title', false)->assertDontSee('data-banner-subtitle', false)->assertDontSee('data-banner-url', false)
        ->assertSee('src="'.$first->media->url().'"', false)->assertSee('src="'.$later->media->url().'"', false)
        ->assertSee('loading="eager"', false)->assertSee('fetchpriority="high"', false)->assertSee('loading="lazy"', false)
        ->assertSee('Choose a home slide')->assertSee('Apply Roaster')->assertSee('Explore Trainings')
        ->assertSee('href="https://tmis.pcgg.lumbini.gov.np/routines?status=all"', false)
        ->assertDontSee('/front/images/dynamic/homepage-banner/', false);
});

test('saved hero settings remain the static left content independently of slide titles', function (): void {
    HomepageSlide::create(['title' => 'Image caption only', 'media_id' => ($this->makeBannerImage)('banners/settings.jpg')->id, 'status' => ContentStatus::Published]);
    $data = app(FrontendService::class)->home([]);
    $data['settings']['hero_title'] = 'Configured left heading';
    $data['settings']['hero_description'] = 'Configured left description';

    $this->view('front.sections.hero', $data)->assertSee('<h1>Configured left heading</h1>', false)
        ->assertSee('Configured left description')->assertSee('homepage__banner-caption">Image caption only', false)
        ->assertSee('Apply Roaster')->assertSee('Explore Trainings');
});

test('draft home slides and slides without an attached image never appear publicly', function (): void {
    HomepageSlide::create(['title' => 'Private draft banner', 'media_id' => ($this->makeBannerImage)('banners/private.jpg')->id, 'status' => ContentStatus::Draft]);
    HomepageSlide::create(['title' => 'Missing image banner', 'status' => ContentStatus::Published]);

    $this->get(route('public.home'))->assertOk()->assertViewHas('bannerSlides', fn ($slides) => $slides->isEmpty())
        ->assertDontSee('Private draft banner')->assertDontSee('Missing image banner')
        ->assertDontSee('homepage-banner-swiper', false)->assertDontSee('/front/images/dynamic/homepage-banner/', false)
        ->assertSee('homepage__banner--empty', false)->assertSee('View all notices');
});

test('an empty slide catalogue leaves the notice bar without stock banner images', function (): void {
    $this->get(route('public.home'))->assertOk()->assertDontSee('homepage__banner-shell', false)
        ->assertDontSee('homepage-banner-swiper', false)->assertDontSee('Training of Trainers Program Inauguration')
        ->assertSee('Latest notice')->assertSee('No notices published yet.');
});

test('a single slide retains both original buttons without carousel pagination', function (): void {
    $slide = HomepageSlide::create(['title' => 'Single banner', 'media_id' => ($this->makeBannerImage)('banners/single.jpg')->id, 'status' => ContentStatus::Published]);
    $response = $this->get(route('public.home'))->assertOk()
        ->assertSee('homepage__banner-caption">Single banner', false)->assertSee('src="'.$slide->media->url().'"', false)
        ->assertSee('Apply Roaster')->assertSee('Explore Trainings')->assertDontSee('Choose a home slide');
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    try {
        $document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        $buttons = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " homepage__banner-actions ")]/a');
        expect($buttons)->toHaveCount(2);
        foreach ($buttons as $button) {
            expect($button->getAttribute('href'))->toBe('https://tmis.pcgg.lumbini.gov.np/routines?status=all')
                ->and($button->getAttribute('target'))->toBe('_blank')
                ->and($button->getAttribute('rel'))->toBe('noopener noreferrer');
        }
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
});

test('caption titles stay escaped and legacy subtitle and link values are absent from public banner data', function (string $url): void {
    $title = '<script>alert("slide-title")</script>';
    $subtitle = '<img src=x onerror="alert(1)">';
    HomepageSlide::create(['title' => $title, 'subtitle' => $subtitle, 'link_url' => $url, 'media_id' => ($this->makeBannerImage)('banners/safe.jpg')->id, 'status' => ContentStatus::Published]);

    $this->get(route('public.home'))->assertOk()->assertSee($title)->assertDontSee($subtitle)
        ->assertViewHas('bannerSlides', fn ($slides) => array_keys($slides->first()) === ['id', 'title', 'media'])
        ->assertDontSee($title, false)->assertDontSee($subtitle, false)->assertDontSee($url, false);
})->with(['javascript:alert(1)', 'ftp://example.com/file', '//example.com/redirect']);

test('admin ordering refreshes cached slide images and captions while original left content stays unchanged', function (): void {
    $first = HomepageSlide::create(['title' => 'Initially first', 'media_id' => ($this->makeBannerImage)('banners/initial.jpg')->id, 'status' => ContentStatus::Published, 'sort_order' => 0]);
    $second = HomepageSlide::create(['title' => 'Move to first', 'media_id' => ($this->makeBannerImage)('banners/new.jpg')->id, 'status' => ContentStatus::Published, 'sort_order' => 1]);
    $this->get(route('public.home'))->assertOk()
        ->assertSeeInOrder(['homepage__banner-caption">Initially first', 'homepage__banner-caption">Move to first'], false);
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->actingAs(User::findOrFail(1))->postJson(route('admin.homepage-slides.order'), ['records' => [$second->id, $first->id], 'original_order' => [$first->id, $second->id]])->assertOk();
    $this->get(route('public.home'))->assertOk()
        ->assertSeeInOrder(['homepage__banner-caption">Move to first', 'homepage__banner-caption">Initially first'], false)
        ->assertSee('<h1>'.e(config('frontend.hero_title')).'</h1>', false)->assertSee(config('frontend.hero_description'))
        ->assertSee('Apply Roaster')->assertSee('Explore Trainings')
        ->assertViewHas('bannerSlides', fn ($slides) => $slides->pluck('id')->all() === [$second->id, $first->id]);
});

test('home slide edit and service saves ignore removed fields and retain the uploaded image', function (): void {
    $slide = HomepageSlide::create(['title' => 'Old caption', 'subtitle' => 'Unused old subtitle', 'link_url' => 'https://example.com/unused', 'media_id' => ($this->makeBannerImage)('banners/edit.jpg')->id, 'status' => ContentStatus::Published]);
    $mediaId = $slide->media_id;
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $admin = User::findOrFail(1);
    $this->actingAs($admin)->get(route('admin.homepage-slides.create'))->assertOk()
        ->assertDontSee('name="subtitle"', false)->assertDontSee('name="link_url"', false);
    $this->get(route('admin.homepage-slides.edit', $slide))->assertOk()
        ->assertDontSee('name="subtitle"', false)->assertDontSee('name="link_url"', false);
    $this->get(route('admin.homepage-slides.index'))->assertOk()->assertDontSee('Unused old subtitle');
    $this->put(route('admin.homepage-slides.update', $slide), [
        'title' => 'Updated caption', 'status' => 'published', 'subtitle' => ['invalid legacy input'], 'link_url' => 'javascript:alert(1)',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.homepage-slides.index'));
    expect($slide->refresh()->title)->toBe('Updated caption')->and($slide->subtitle)->toBe('Unused old subtitle')
        ->and($slide->link_url)->toBe('https://example.com/unused')->and($slide->media_id)->toBe($mediaId);
    $saved = app(HomepageSlideService::class)->save([
        'title' => 'Service caption', 'status' => 'published', 'subtitle' => 'Unwanted subtitle', 'link_url' => 'https://example.com/unwanted',
    ], $admin, $slide);
    expect($saved->title)->toBe('Service caption')->and($saved->subtitle)->toBe('Unused old subtitle')
        ->and($saved->link_url)->toBe('https://example.com/unused')->and($saved->media_id)->toBe($mediaId);
});
