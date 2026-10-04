<?php

use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\ResourceCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    Storage::fake('public');
});

dataset('configured admin image fields', [
    'page banner' => ['pages', 'banner_image', 'images.page.banner', false],
    'page social' => ['pages', 'social_media_image', 'images.page.social', false],
    'news thumbnail' => ['news', 'thumbnail', 'images.news.thumbnail', false],
    'news banner' => ['news', 'banner_image', 'images.news.banner', false],
    'news social' => ['news', 'social_media_image', 'images.news.social', false],
    'homepage slide' => ['homepage-slides', 'image', 'images.homepage_slide', false],
    'team member' => ['team-members', 'photo', 'images.team_member', false],
    'hall thumbnail' => ['halls', 'thumbnail', 'images.hall.thumbnail', false],
    'hall banner' => ['halls', 'banner_image', 'images.hall.banner', false],
    'hall social' => ['halls', 'social_media_image', 'images.hall.social', false],
    'hall gallery' => ['halls', 'gallery_images', 'images.hall.gallery', true],
    'gallery photo' => ['gallery', 'new_photos', 'images.gallery_photo', true],
    'video thumbnail' => ['videos', 'cover', 'images.video_cover', false],
]);

test('admin image fields enforce their configured upload limit and accept a valid upload', function (string $module, string $input, string $profile, bool $multiple): void {
    config(['settings.'.$profile.'.max_size_kb' => 1]);
    $data = ['title' => 'Configured upload', 'status' => 'draft', 'sort_order' => 0];
    $data += match ($module) {
        'team-members' => ['name' => 'Team member', 'designation' => 'Officer'],
        'halls' => ['capacity' => 20, 'availability_status' => 'available'],
        'videos' => ['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        default => [],
    };
    $oversized = UploadedFile::fake()->image('upload.jpg', 40, 30)->size(2);
    $data[$input] = $multiple ? [$oversized] : $oversized;
    $error = $input.($multiple ? '.0' : '');

    $this->actingAs($this->admin)->post(route('admin.'.$module.'.store'), $data)->assertSessionHasErrors($error);
    expect(session('errors')->getBag('default')->keys())->toBe([$error]);
    $this->assertDatabaseCount('media_assets', 0);

    $valid = UploadedFile::fake()->image('upload.jpg', 40, 30)->size(1);
    $data[$input] = $multiple ? [$valid] : $valid;
    $this->post(route('admin.'.$module.'.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('media_assets', 1);
    Storage::disk('public')->assertExists(MediaAsset::firstOrFail()->path);
})->with('configured admin image fields');

test('configured pixel dimensions can be enforced on create and update', function (): void {
    config([
        'settings.images.page.banner.width' => 80,
        'settings.images.page.banner.height' => 40,
        'settings.images.page.banner.enforce_dimensions' => true,
    ]);
    $data = ['title' => 'Dimension controlled', 'status' => 'draft', 'banner_image' => UploadedFile::fake()->image('banner.jpg', 40, 40)];
    $this->actingAs($this->admin)->post(route('admin.pages.store'), $data)->assertSessionHasErrors('banner_image');
    $this->assertDatabaseCount('media_assets', 0);

    $data['banner_image'] = UploadedFile::fake()->image('banner.jpg', 80, 40);
    $this->post(route('admin.pages.store'), $data)->assertSessionHasNoErrors();
    $page = Page::firstOrFail();
    $originalMediaId = $page->banner_media_id;
    $data['banner_image'] = UploadedFile::fake()->image('replacement.jpg', 80, 80);
    $this->put(route('admin.pages.update', $page), $data)->assertSessionHasErrors('banner_image');
    expect($page->refresh()->banner_media_id)->toBe($originalMediaId);
    $this->assertDatabaseCount('media_assets', 1);
});

test('image validation rejects disallowed types and retains authorization', function (): void {
    config(['settings.images.news.thumbnail.mimes' => ['png']]);
    $data = ['title' => 'Image formats', 'status' => 'draft', 'thumbnail' => UploadedFile::fake()->image('photo.jpg')];
    $this->actingAs($this->admin)->post(route('admin.news.store'), $data)->assertSessionHasErrors('thumbnail');
    $data['thumbnail'] = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');
    $this->post(route('admin.news.store'), $data)->assertSessionHasErrors('thumbnail');
    $this->actingAs(User::factory()->create(['status' => 'active']))->post(route('admin.news.store'), $data)->assertForbidden();
    $this->assertDatabaseCount('media_assets', 0);
});

test('document uploads use configured file limits', function (): void {
    config(['settings.uploads.document.max_size_kb' => 1]);
    $category = ResourceCategory::factory()->create();
    $this->actingAs($this->admin)->post(route('admin.resources.store'), [
        'title' => 'Document', 'status' => 'draft', 'sort_order' => 0, 'resource_category_id' => $category->id,
        'attachment' => UploadedFile::fake()->create('large.pdf', 2, 'application/pdf'),
    ])->assertSessionHasErrors('attachment');
    $this->assertDatabaseCount('resource_documents', 0);
    $this->assertDatabaseCount('media_assets', 0);

    $this->post(route('admin.resources.store'), [
        'title' => 'Document', 'status' => 'draft', 'sort_order' => 0, 'resource_category_id' => $category->id,
        'attachment' => UploadedFile::fake()->create('valid.pdf', 1, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('resource_documents', 1);
    $this->assertDatabaseCount('media_assets', 1);
    Storage::disk('public')->assertExists(MediaAsset::firstOrFail()->path);
});

test('admin upload guidance reflects configured pixels formats and file size', function (): void {
    config([
        'settings.images.news.thumbnail.width' => 820,
        'settings.images.news.thumbnail.height' => 460,
        'settings.images.news.thumbnail.max_size_kb' => 2048,
        'settings.images.news.thumbnail.mimes' => ['png'],
    ]);
    $this->actingAs($this->admin)->get(route('admin.news.create'))->assertOk()
        ->assertSee('Recommended dimensions: 820 × 460 px.')->assertSee('Maximum size: 2.0 MB.')
        ->assertSee('accept=".png"', false)->assertSee('maxSize: 2097152', false);

    config(['settings.images.news.thumbnail.enforce_dimensions' => true]);
    $this->get(route('admin.news.create'))->assertOk()->assertSee('Required dimensions: 820 × 460 px.');
    $this->get(route('admin.homepage-slides.create'))->assertOk()->assertSee('Recommended dimensions: 1600 × 900 px.')
        ->assertSee('Maximum size: 5.0 MB.');
    $this->get(route('admin.resources.create'))->assertOk()->assertSee('Maximum size: 10.0 MB.');
});
