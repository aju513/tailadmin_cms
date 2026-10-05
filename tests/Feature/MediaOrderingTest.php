<?php

use App\Enums\ContentStatus;
use App\Models\GalleryAlbum;
use App\Models\HomepageSlide;
use App\Models\User;
use App\Models\Video;
use App\Repositories\Contracts\GalleryAlbumRepositoryInterface;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use App\Repositories\Contracts\VideoRepositoryInterface;
use App\Repositories\Eloquent\GalleryAlbumRepository;
use App\Repositories\Eloquent\HomepageSlideRepository;
use App\Repositories\Eloquent\VideoRepository;
use App\Services\GalleryAlbumService;
use App\Services\HomepageSlideService;
use App\Services\VideoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->makeMediaRecords = function (string $model, int $count = 1, ContentStatus $status = ContentStatus::Draft): array {
        return collect(range(0, $count - 1))->map(function (int $position) use ($model, $status) {
            $attributes = ['title' => 'Ordered item '.$position, 'sort_order' => $position, 'status' => $status];

            return $model === HomepageSlide::class ? $model::create($attributes) : $model::factory()->create($attributes);
        })->all();
    };
});

dataset('ordered media modules', [
    ['videos', Video::class, VideoService::class, VideoRepositoryInterface::class, VideoRepository::class, 'Video'],
    ['gallery', GalleryAlbum::class, GalleryAlbumService::class, GalleryAlbumRepositoryInterface::class, GalleryAlbumRepository::class, 'Gallery'],
    ['homepage-slides', HomepageSlide::class, HomepageSlideService::class, HomepageSlideRepositoryInterface::class, HomepageSlideRepository::class, 'Home slide'],
]);

test('media forms append new entries without numeric order and preserve the position when edited', function (string $module, string $model): void {
    $previous = ($this->makeMediaRecords)($model)[0];
    $previous->update(['sort_order' => 10]);
    $this->actingAs($this->admin)->get(route("admin.{$module}.create"))->assertOk()
        ->assertDontSee('name="sort_order"', false)->assertSee('data-sticky-form-actions=', false)
        ->assertSee('Save')->assertSee('Close');
    $data = ['title' => 'New media entry', 'status' => 'draft', 'sort_order' => 999];
    if ($module === 'videos') {
        $data['video_url'] = 'https://www.youtube.com/watch?v=example';
        $data['description'] = '<p>A <strong>useful</strong> video.</p>';
    } elseif ($module === 'homepage-slides') {
        $data['image'] = UploadedFile::fake()->image('slide.png', 160, 90);
        $data['subtitle'] = 'A slide subtitle';
        $data['link_url'] = 'https://example.com/programs';
    }
    $this->post(route("admin.{$module}.store"), $data)->assertSessionHasNoErrors()->assertRedirect(route("admin.{$module}.index"));
    $record = $model::where('title', 'New media entry')->firstOrFail();
    expect($record->sort_order)->toBe(11);
    $slug = $record->slug;
    $mediaId = $record->media_id;
    unset($data['image']);
    $data['title'] = 'Renamed media entry';
    $data['sort_order'] = 0;
    $this->put(route("admin.{$module}.update", $record), $data)->assertSessionHasNoErrors()
        ->assertRedirect(route("admin.{$module}.".($module === 'homepage-slides' ? 'index' : 'edit'), $module === 'homepage-slides' ? [] : $record));
    expect($record->refresh()->sort_order)->toBe(11)->and($record->slug)->toBe($slug)->and($record->media_id)->toBe($mediaId);
    $response = $this->get(route("admin.{$module}.edit", $record))->assertOk()->assertDontSee('name="sort_order"', false);
    if ($module === 'videos') {
        $response->assertSeeInOrder(['name="title"', 'name="video_url"', 'name="status"', 'name="description"', 'Thumbnail'], false)
            ->assertSee('js-rich-text-editor');
    } elseif ($module === 'gallery') {
        $response->assertSeeInOrder(['Gallery details', 'name="title"', 'name="slug"', 'name="status"', 'Gallery images'], false);
    } else {
        $response->assertSeeInOrder(['Slide details', 'name="title"', 'name="subtitle"', 'name="link_url"', 'name="status"', 'Slide image'], false)
            ->assertSee($record->media->url(), false);
    }
})->with('ordered media modules');

test('media form validation still rejects missing required inputs and invalid publication status', function (string $module, string $model): void {
    $required = $module === 'videos' ? ['title', 'video_url'] : ($module === 'homepage-slides' ? ['title', 'image'] : ['title']);
    $this->actingAs($this->admin)->post(route("admin.{$module}.store"), ['status' => 'invalid'])->assertSessionHasErrors([...$required, 'status']);
    expect($model::count())->toBe(0);
})->with('ordered media modules');

test('media rows support drag ordering without move arrows', function (string $module, string $model, string $service): void {
    $record = ($this->makeMediaRecords)($model)[0];
    $this->actingAs($this->admin)->get(route("admin.{$module}.index"))->assertOk()
        ->assertDontSee('Move Ordered item 0 up')->assertDontSee('Move Ordered item 0 down');
    if ($module !== 'homepage-slides') {
        $this->get(route("admin.{$module}.index", ['search' => 'Ordered']))->assertOk()
            ->assertSee('Clear the search to reorder ')->assertDontSee('Move Ordered item 0 up')->assertDontSee('Move Ordered item 0 down');
    }
    $reader = User::factory()->create();
    $role = Role::create(['name' => $module.'-reader', 'guard_name' => 'web']);
    $role->givePermissionTo($module.'.manage');
    $reader->assignRole($role);
    $this->actingAs($reader)->get(route("admin.{$module}.index"))->assertOk()->assertDontSee('Move Ordered item 0 up')->assertDontSee('Move Ordered item 0 down');
    $this->postJson(route("admin.{$module}.order"), ['records' => [$record->id], 'original_order' => [$record->id]])->assertForbidden();
    expect(fn () => app($service)->reorder([$record->id], [$record->id], $reader))->toThrow(AuthorizationException::class);
})->with('ordered media modules');

test('media page moves retain every other page and invalidate the public cache', function (string $module, string $model, string $service, string $contract, string $repository, string $label): void {
    $records = ($this->makeMediaRecords)($model, 18, ContentStatus::Published);
    $original = array_map(fn ($record) => $record->id, $records);
    $editor = User::factory()->create();
    $role = Role::create(['name' => $module.'-order-editor', 'guard_name' => 'web']);
    $role->givePermissionTo([$module.'.manage', $module.'.edit']);
    $editor->assignRole($role);
    $firstPage = array_slice($original, 0, 15);
    $this->actingAs($editor)->get(route("admin.{$module}.index"))->assertOk()
        ->assertViewHas($module === 'homepage-slides' ? 'slides' : 'records', fn ($page) => $page->modelKeys() === $firstPage);
    Cache::forever('frontend.version', 'before-order');
    $moved = $firstPage;
    [$moved[0], $moved[1]] = [$moved[1], $moved[0]];
    $this->postJson(route("admin.{$module}.order"), ['records' => $moved, 'original_order' => $firstPage])
        ->assertOk()->assertJson(['message' => $label.' order updated.']);
    expect($model::orderBy('sort_order')->pluck('id')->all())->toBe([...$moved, ...array_slice($original, 15)])
        ->and(Cache::get('frontend.version'))->not->toBe('before-order');
    $secondPage = array_slice($original, 15);
    $this->get(route("admin.{$module}.index", ['page' => 2]))->assertOk()
        ->assertViewHas($module === 'homepage-slides' ? 'slides' : 'records', fn ($page) => $page->modelKeys() === $secondPage);
    $this->postJson(route("admin.{$module}.order"), ['records' => array_reverse($secondPage), 'original_order' => $secondPage])->assertOk();
    expect($model::orderBy('sort_order')->pluck('id')->all())->toBe([...$moved, ...array_reverse($secondPage)])
        ->and($model::where('status', ContentStatus::Published)->count())->toBe(18);
    $this->assertDatabaseHas('activity_log', ['event' => $module.'.reordered', 'causer_id' => $editor->id]);
})->with('ordered media modules');

test('media reorder rejects stale mismatched and non contiguous lists without writes', function (string $module, string $model): void {
    [$first, $second, $third] = array_map(fn ($record) => $record->id, ($this->makeMediaRecords)($model, 3));
    Cache::forever('frontend.version', 'unchanged');
    $this->actingAs($this->admin)->postJson(route("admin.{$module}.order"), ['records' => [$second, $first], 'original_order' => [$first, $third]])
        ->assertUnprocessable()->assertJsonValidationErrors('records');
    $this->postJson(route("admin.{$module}.order"), ['records' => [$third, $first], 'original_order' => [$first, $third]])
        ->assertUnprocessable()->assertJsonValidationErrors('records');
    $this->postJson(route("admin.{$module}.order"), ['records' => [$first, $second, $third], 'original_order' => [$second, $first, $third]])
        ->assertUnprocessable()->assertJsonValidationErrors('records');
    expect($model::orderBy('sort_order')->pluck('id')->all())->toBe([$first, $second, $third])->and(Cache::get('frontend.version'))->toBe('unchanged');
    $this->assertDatabaseMissing('activity_log', ['event' => $module.'.reordered']);
})->with('ordered media modules');

test('media ordering validates missing original order duplicate unknown and non list IDs', function (string $module, string $model): void {
    $record = ($this->makeMediaRecords)($model)[0];
    $this->actingAs($this->admin)->postJson(route("admin.{$module}.order"), ['records' => [$record->id]])
        ->assertUnprocessable()->assertJsonValidationErrors('original_order');
    $this->postJson(route("admin.{$module}.order"), ['records' => [$record->id, $record->id], 'original_order' => [$record->id]])
        ->assertUnprocessable()->assertJsonValidationErrors('records.0');
    $this->postJson(route("admin.{$module}.order"), ['records' => [999999], 'original_order' => [$record->id]])
        ->assertUnprocessable()->assertJsonValidationErrors('records.0');
    $this->postJson(route("admin.{$module}.order"), ['records' => ['row' => $record->id], 'original_order' => [$record->id]])
        ->assertUnprocessable()->assertJsonValidationErrors('records');
    $this->assertDatabaseMissing('activity_log', ['event' => $module.'.reordered']);
})->with('ordered media modules');

test('failed media ordering rolls back positions without success audits or cache invalidation', function (string $module, string $model, string $service, string $contract, string $repository): void {
    $ids = array_map(fn ($record) => $record->id, ($this->makeMediaRecords)($model, 3));
    Cache::forever('frontend.version', 'unchanged');
    $fake = Mockery::mock($repository)->makePartial();
    $fake->shouldReceive('reorder')->once()->andReturnUsing(function ($ordered) use ($model): void {
        $model::whereKey($ordered[0])->update(['sort_order' => 99]);
        throw new RuntimeException('Order write failed');
    });
    $this->app->instance($contract, $fake);
    expect(fn () => app($service)->reorder(array_reverse($ids), $ids, $this->admin))->toThrow(RuntimeException::class, 'Order write failed');
    expect($model::orderBy('sort_order')->pluck('id')->all())->toBe($ids)->and(Cache::get('frontend.version'))->toBe('unchanged');
    $this->assertDatabaseMissing('activity_log', ['event' => $module.'.reordered']);
})->with('ordered media modules');

test('failed home slide creation rolls back its uploaded image and database asset', function (): void {
    $repository = Mockery::mock(HomepageSlideRepository::class)->makePartial();
    $repository->shouldReceive('create')->once()->andThrow(new RuntimeException('Slide write failed'));
    $this->app->instance(HomepageSlideRepositoryInterface::class, $repository);
    expect(fn () => app(HomepageSlideService::class)->save([
        'title' => 'Failed slide', 'status' => 'draft', 'image' => UploadedFile::fake()->image('slide.png', 160, 90),
    ], $this->admin))->toThrow(RuntimeException::class, 'Slide write failed');
    $this->assertDatabaseCount('homepage_slides', 0);
    $this->assertDatabaseCount('media_assets', 0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
    $this->assertDatabaseMissing('activity_log', ['event' => 'homepage-slide.created']);
});
