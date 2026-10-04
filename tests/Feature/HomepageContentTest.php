<?php

use App\Models\GalleryAlbum;
use App\Models\HomepageContent;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\MediaAssetService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['settings.nepali' => false, 'cache.default' => 'array']);
    Cache::flush();
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('homepage editor follows the existing form and gallery patterns', function (): void {
    SiteSetting::create(['key' => 'about_title', 'value' => 'Existing welcome title', 'type' => 'text']);
    SiteSetting::create(['key' => 'about_description', 'value' => '<p>Existing welcome copy.</p>', 'type' => 'text']);

    $this->actingAs($this->admin)->get(route('admin.homepage.edit'))->assertOk()
        ->assertSee('Welcome title')->assertSee('Existing welcome title')->assertSee('Existing welcome copy.')
        ->assertSee('Description')->assertSee('Gallery Images')->assertSee('Social Media Image')->assertSee('SEO Details')
        ->assertSee('Save homepage')->assertSee('Close')->assertSee('form="homepage-form"', false)
        ->assertSee('name="gallery_images[]"', false)->assertSee('1200')->assertSee('950')
        ->assertDontSee('name="thumbnail"', false);

    $this->get(route('admin.settings.edit'))->assertOk()
        ->assertSee(route('admin.homepage.edit'), false)->assertDontSee('name="about_description"', false);
    $this->assertDatabaseCount('homepage_contents', 0);
});

test('homepage permission boundaries protect the editor and live mutations', function (): void {
    $this->get(route('admin.homepage.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.homepage.update'), ['title' => 'Blocked'])->assertRedirect(route('login'));
    $ordinary = User::factory()->create();
    $this->actingAs($ordinary)->get(route('admin.homepage.edit'))->assertForbidden();
    $this->put(route('admin.homepage.update'), ['title' => 'Blocked'])->assertForbidden();

    $role = Role::create(['name' => 'homepage-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('homepage.manage');
    $ordinary->assignRole($role);
    $this->get(route('admin.homepage.edit'))->assertOk()->assertDontSee('Save homepage');
    $this->put(route('admin.homepage.update'), ['title' => 'Blocked'])->assertForbidden();
    $this->assertDatabaseCount('homepage_contents', 0);
});

test('homepage saves welcome content gallery and social metadata and refreshes the public cache', function (): void {
    $this->get(route('public.home'))->assertOk();
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Welcome to the Institute', 'subtitle' => 'Who we are',
        'body' => '<p>Learn about our work.</p><script>alert(1)</script>',
        'meta_title' => 'Institute homepage', 'meta_keywords' => 'training, governance',
        'meta_description' => 'Training and research in Lumbini.',
        'gallery_images' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->image('second.png')],
        'social_media_image' => UploadedFile::fake()->image('social.jpg'), 'social_media_alt_text' => 'Institute sharing image',
        'key' => 'untrusted', 'social_media_id' => 999,
    ])->assertRedirect(route('admin.homepage.edit'))->assertSessionHasNoErrors();

    $content = HomepageContent::query()->with(['galleryImages.mediaAsset', 'socialMedia'])->firstOrFail();
    expect($content->key)->toBe('home')->and($content->title)->toBe('Welcome to the Institute')
        ->and($content->body)->toBe('<p>Learn about our work.</p>')
        ->and($content->galleryImages)->toHaveCount(2)
        ->and($content->galleryImages->pluck('sort_order')->all())->toBe([1, 2])
        ->and($content->socialMedia->alt_text)->toBe('Institute sharing image');
    foreach ($content->galleryImages as $image) {
        Storage::disk('public')->assertExists($image->mediaAsset->path);
    }
    $this->get(route('admin.homepage.edit'))->assertOk()->assertSee('Remove image')->assertSee('Remove current social media image');
    $this->get(route('public.home'))->assertOk()->assertSee('Welcome to the Institute')->assertSee('Who we are')
        ->assertSee('Learn about our work.')->assertSee('<title>Institute homepage</title>', false)
        ->assertSee('<meta name="keywords" content="training, governance">', false)
        ->assertSee($content->socialMedia->url(), false)->assertSee($content->galleryImages->first()->mediaAsset->url(), false);
    $this->assertDatabaseHas('activity_log', ['event' => 'homepage.updated', 'causer_id' => $this->admin->id]);
});

test('homepage remains a single record and generates editable SEO titles without JavaScript', function (): void {
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), ['title' => 'First welcome', 'meta_title' => ''])->assertSessionHasNoErrors();
    $content = HomepageContent::query()->firstOrFail();
    expect($content->meta_title)->toBe('First welcome');
    $this->put(route('admin.homepage.update'), ['title' => 'Second welcome', 'meta_title' => 'Custom search title'])->assertSessionHasNoErrors();
    expect($content->refresh()->meta_title)->toBe('Custom search title')->and($content->title)->toBe('Second welcome');
    $this->assertDatabaseCount('homepage_contents', 1);
});

test('homepage gallery additions and removals preserve reusable media assets', function (): void {
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Gallery welcome', 'gallery_images' => [UploadedFile::fake()->image('old.jpg')],
        'social_media_image' => UploadedFile::fake()->image('social.jpg'),
    ])->assertSessionHasNoErrors();
    $content = HomepageContent::query()->with(['galleryImages.mediaAsset', 'socialMedia'])->firstOrFail();
    $image = $content->galleryImages->first();
    $oldAsset = $image->mediaAsset;
    $socialId = $content->social_media_id;

    $this->put(route('admin.homepage.update'), [
        'title' => 'Gallery welcome', 'remove_gallery_ids' => [$image->id],
        'gallery_images' => [UploadedFile::fake()->image('new.jpg')], 'remove_social_media_image' => 1,
    ])->assertSessionHasNoErrors();
    expect($content->refresh()->galleryImages)->toHaveCount(1)->and($content->social_media_id)->toBeNull();
    $this->assertDatabaseMissing('homepage_gallery_images', ['id' => $image->id]);
    $this->assertDatabaseHas('media_assets', ['id' => $oldAsset->id]);
    $this->assertDatabaseHas('media_assets', ['id' => $socialId]);
    Storage::disk('public')->assertExists($oldAsset->path);

    $newImage = $content->galleryImages->first();
    $this->put(route('admin.homepage.update'), ['title' => 'Gallery welcome', 'remove_gallery_ids' => [$newImage->id]])->assertSessionHasNoErrors();
    $this->get(route('public.home'))->assertOk()->assertDontSee('data-gallery-image=', false);
});

test('homepage validates required content lengths and gallery removals', function (array $input, string $field): void {
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), $input)->assertSessionHasErrors($field);
    $this->assertDatabaseCount('homepage_contents', 0);
})->with([
    'missing title' => [[], 'translations'],
    'long title' => [['title' => str_repeat('a', 256)], 'translations.en.title'],
    'long description' => [['title' => 'Welcome', 'body' => str_repeat('a', 50001)], 'translations.en.body'],
    'long SEO title' => [['title' => 'Welcome', 'meta_title' => str_repeat('a', 256)], 'meta_title'],
    'long SEO keywords' => [['title' => 'Welcome', 'meta_keywords' => str_repeat('a', 501)], 'meta_keywords'],
    'long SEO description' => [['title' => 'Welcome', 'meta_description' => str_repeat('a', 1001)], 'meta_description'],
    'unknown removal' => [['title' => 'Welcome', 'remove_gallery_ids' => [999]], 'remove_gallery_ids.0'],
]);

test('homepage image validation uses the configured MIME size and dimension rules', function (): void {
    config(['settings.images.homepage.gallery.max_size_kb' => 1, 'settings.images.homepage.social.enforce_dimensions' => true]);
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Welcome', 'gallery_images' => [UploadedFile::fake()->image('oversize.jpg')->size(2)],
    ])->assertSessionHasErrors('gallery_images.0');
    $this->put(route('admin.homepage.update'), [
        'title' => 'Welcome', 'gallery_images' => [UploadedFile::fake()->create('document.pdf', 1, 'application/pdf')],
    ])->assertSessionHasErrors('gallery_images.0');
    $this->put(route('admin.homepage.update'), [
        'title' => 'Welcome', 'social_media_image' => UploadedFile::fake()->image('wrong.jpg', 30, 30),
    ])->assertSessionHasErrors('social_media_image');
    $this->put(route('admin.homepage.update'), [
        'title' => 'Welcome', 'gallery_images' => [UploadedFile::fake()->image('valid.jpg')->size(1)],
        'social_media_image' => UploadedFile::fake()->image('valid-social.jpg', 1200, 630),
    ])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('homepage_gallery_images', 1);
});

test('homepage gallery limits roll back removals and content changes', function (): void {
    config(['settings.homepage.gallery_limit' => 2]);
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Original welcome', 'gallery_images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
    ])->assertSessionHasNoErrors();
    $content = HomepageContent::query()->with('galleryImages')->firstOrFail();
    $removeId = $content->galleryImages->first()->id;

    $this->put(route('admin.homepage.update'), [
        'title' => 'Changed welcome', 'remove_gallery_ids' => [$removeId],
        'gallery_images' => [UploadedFile::fake()->image('three.jpg'), UploadedFile::fake()->image('four.jpg')],
    ])->assertSessionHasErrors('gallery_images');
    expect($content->refresh()->title)->toBe('Original welcome')->and($content->galleryImages)->toHaveCount(2);
    $this->assertDatabaseHas('homepage_gallery_images', ['id' => $removeId]);
    $this->assertDatabaseCount('media_assets', 2);
});

test('homepage rejects gallery images belonging to a different content record', function (): void {
    $other = HomepageContent::factory()->create(['key' => 'other']);
    $asset = app(MediaAssetService::class)->store(UploadedFile::fake()->image('other.jpg'), $this->admin);
    $image = $other->galleryImages()->create(['media_asset_id' => $asset->id]);
    $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Welcome', 'remove_gallery_ids' => [$image->id],
    ])->assertSessionHasErrors('remove_gallery_ids.0');
    $this->assertDatabaseHas('homepage_gallery_images', ['id' => $image->id]);
    expect(HomepageContent::query()->where('key', 'home')->exists())->toBeFalse();
});

test('failed homepage upload rolls back text and gallery changes and removes newly stored files', function (): void {
    $content = HomepageContent::factory()->create(['title' => ['en' => 'Original welcome']]);
    $realMedia = app(MediaAssetService::class);
    $attempt = 0;
    $this->partialMock(MediaAssetService::class)->shouldReceive('store')->twice()->andReturnUsing(function (...$arguments) use ($realMedia, &$attempt) {
        if (++$attempt === 2) {
            throw new RuntimeException('Simulated upload failure');
        }

        return $realMedia->store(...$arguments);
    });
    $this->withoutExceptionHandling();
    expect(fn () => $this->actingAs($this->admin)->put(route('admin.homepage.update'), [
        'title' => 'Changed welcome', 'gallery_images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
    ]))->toThrow(RuntimeException::class, 'Simulated upload failure');
    expect($content->refresh()->title)->toBe('Original welcome')
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
    $this->assertDatabaseCount('homepage_gallery_images', 0);
    $this->assertDatabaseCount('media_assets', 0);
    $this->assertDatabaseMissing('activity_log', ['event' => 'homepage.updated']);
});

test('homepage optional translations appear publicly and survive disabling Nepali editing', function (): void {
    config(['settings.nepali' => true]);
    $this->actingAs($this->admin)->get(route('admin.homepage.edit'))->assertOk()->assertSee('Welcome title (Nepali)');
    $this->put(route('admin.homepage.update'), ['translations' => [
        'en' => ['title' => 'English welcome', 'subtitle' => 'About', 'body' => '<p>English content.</p>'],
        'ne' => ['title' => 'हाम्रो परिचय', 'subtitle' => 'स्वागत', 'body' => '<p>नेपाली सामग्री</p>'],
    ]])->assertSessionHasNoErrors();
    $this->get(route('public.home', ['lang' => 'ne']))->assertOk()->assertSee('हाम्रो परिचय')->assertSee('नेपाली सामग्री');
    config(['settings.nepali' => false]);
    $this->put(route('admin.homepage.update'), ['title' => 'Updated English welcome'])->assertSessionHasNoErrors();
    $content = HomepageContent::query()->firstOrFail();
    expect($content->getTranslation('title', 'en', false))->toBe('Updated English welcome')
        ->and($content->getTranslation('title', 'ne', false))->toBe('हाम्रो परिचय');
});

test('homepage migration imports existing About text and displayed gallery photos without deleting originals', function (): void {
    SiteSetting::create(['key' => 'about_title', 'value' => 'Legacy welcome title', 'type' => 'text']);
    SiteSetting::create(['key' => 'about_description', 'value' => '<p>Legacy welcome body.</p>', 'type' => 'text']);
    GalleryAlbum::factory()->create(['status' => 'published', 'published_at' => now()->subMinute(), 'sort_order' => 0]);
    $album = GalleryAlbum::factory()->create(['status' => 'published', 'published_at' => now()->subMinute(), 'sort_order' => 1]);
    $first = app(MediaAssetService::class)->store(UploadedFile::fake()->image('first.jpg'), $this->admin);
    $second = app(MediaAssetService::class)->store(UploadedFile::fake()->image('second.jpg'), $this->admin);
    $album->photos()->create(['media_asset_id' => $first->id, 'sort_order' => 1]);
    $album->photos()->create(['media_asset_id' => $second->id, 'sort_order' => 2]);
    $migration = require database_path('migrations/2026_10_04_100000_create_homepage_content_tables.php');
    $migration->down();
    $migration->up();
    $content = HomepageContent::query()->with('galleryImages')->firstOrFail();
    expect($content->title)->toBe('Legacy welcome title')->and($content->body)->toBe('<p>Legacy welcome body.</p>')
        ->and($content->galleryImages->pluck('media_asset_id')->all())->toBe([$first->id]);
    $this->assertDatabaseCount('gallery_photos', 2);
    $this->assertDatabaseCount('site_settings', 2);

    SiteSetting::create(['key' => 'site_name', 'value' => 'Legacy site name', 'type' => 'text']);
    SiteSetting::whereIn('key', ['about_title', 'about_description'])->update(['value' => '']);
    $migration->down();
    $migration->up();
    $content = HomepageContent::query()->firstOrFail();
    expect($content->title)->toBe('Legacy site name')->and($content->body)->toBe('');
});
