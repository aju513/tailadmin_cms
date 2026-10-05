<?php

use App\Enums\ContentStatus;
use App\Models\HomepageSlide;
use App\Models\MediaAsset;
use App\Models\News;
use App\Services\Frontend\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    config(['app.url' => 'http://localhost', 'cache.default' => 'array', 'services.tims.enabled' => false]);
    Cache::flush();
    Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
    $file = UploadedFile::fake()->image('banner.jpg', 1200, 800);
    $path = $file->storeAs('cms', 'banner.jpg', 'public');
    $this->image = MediaAsset::create(['disk' => 'public', 'path' => $path, 'original_name' => 'banner.jpg', 'mime_type' => 'image/jpeg', 'size' => $file->getSize()]);
    $this->imageDirectory = app(ImageService::class)->directory($this->image);
    Storage::disk('public')->put($this->imageDirectory.'/480.webp', 'variant');
});

test('uploaded originals and responsive images follow the frontend host scheme and port instead of APP_URL', function (): void {
    expect(config('filesystems.disks.public.url'))->toBe('/storage');
    HomepageSlide::create(['title' => 'Public banner', 'media_id' => $this->image->id, 'status' => ContentStatus::Published]);

    $this->get('https://preview.example:8443/')->assertOk()
        ->assertSee('src="https://preview.example:8443/storage/cms/banner.jpg"', false)
        ->assertSee('srcset="https://preview.example:8443/storage/'.$this->imageDirectory.'/480.webp 480w', false)
        ->assertDontSee('http://localhost/storage/', false)
        ->assertSee('rel="canonical" href="http://localhost/"', false);
});

test('detail images and social metadata keep absolute media URLs on the current site', function (): void {
    $news = News::factory()->create(['status' => ContentStatus::Published, 'published_at' => now()->subHour(), 'thumbnail_media_id' => $this->image->id]);

    $this->get('https://preview.example:8443/news/'.$news->slug)->assertOk()
        ->assertSee('src="https://preview.example:8443/storage/cms/banner.jpg"', false)
        ->assertSee('property="og:image" content="https://preview.example:8443/storage/cms/banner.jpg"', false)
        ->assertSee('https://preview.example:8443/storage/'.$this->imageDirectory.'/480.webp', false)
        ->assertDontSee('http://localhost/storage/', false);
});

test('configured absolute media URLs are preserved for CDN disks and image variants', function (): void {
    config(['filesystems.disks.public.url' => 'https://cdn.example.test/media']);
    Storage::fake('public', ['url' => config('filesystems.disks.public.url')]);
    $file = UploadedFile::fake()->image('banner.jpg', 1200, 800);
    $file->storeAs('cms', 'banner.jpg', 'public');
    Storage::disk('public')->put($this->imageDirectory.'/480.webp', 'variant');

    expect($this->image->url())->toBe('https://cdn.example.test/media/cms/banner.jpg');
    $attributes = app(ImageService::class)->attributes($this->image);
    expect($attributes['src'])->toBe('https://cdn.example.test/media/cms/banner.jpg')
        ->and($attributes['srcset'])->toContain('https://cdn.example.test/media/'.$this->imageDirectory.'/480.webp 480w');
});

test('relative public media URLs preserve an application base path', function (): void {
    URL::forceRootUrl('https://preview.example:8443/cms');
    URL::forceScheme('https');
    try {
        expect($this->image->url())->toBe('https://preview.example:8443/cms/storage/cms/banner.jpg');
        expect(app(ImageService::class)->attributes($this->image)['srcset'])->toContain('https://preview.example:8443/cms/storage/'.$this->imageDirectory.'/480.webp 480w');
    } finally {
        URL::forceRootUrl(null);
        URL::forceScheme(null);
    }
});
