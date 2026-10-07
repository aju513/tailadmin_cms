<?php

use App\Models\MediaAsset;
use App\Models\Popup;
use App\Models\User;
use App\Repositories\Contracts\PopupRepositoryInterface;
use App\Services\MediaAssetService;
use App\Services\PopupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});
function popupPayload(array $overrides = []): array
{
    return array_replace(['title' => 'Announcement', 'status' => 'draft', 'message' => 'First line'."\n".'Second line', 'image' => UploadedFile::fake()->image('poster.png', 500, 800)], $overrides);
}
function popupActor(array $permissions): User
{
    $role = Role::create(['name' => uniqid('popup-role-'), 'guard_name' => 'web']);
    $role->givePermissionTo($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
function publishedPopup(string $title, int $order): Popup
{
    $media = app(MediaAssetService::class)->store(UploadedFile::fake()->image('poster.png'), null);

    return Popup::factory()->create(['title' => $title, 'status' => 'published', 'sort_order' => $order, 'media_id' => $media->id]);
}

test('popup screens create update search and delete with shared media preservation', function () {
    $this->actingAs($this->admin)->get(route('admin.popups.index'))->assertOk()->assertSee('Popup Manager');
    $this->get(route('admin.popups.create'))->assertOk()->assertSee('Save popup')->assertSee('Message (optional)');
    $this->post(route('admin.popups.store'), popupPayload())->assertRedirect(route('admin.popups.index'));
    $popup = Popup::firstOrFail();
    $old = $popup->media;
    $this->get(route('admin.popups.edit', $popup))->assertOk()->assertSee('Announcement');
    $this->put(route('admin.popups.update', $popup), popupPayload(['title' => 'Updated', 'status' => 'published', 'button_label' => 'Details', 'button_url' => 'https://example.com']))->assertRedirect();
    expect($popup->fresh()->sort_order)->toBe($popup->sort_order);
    $this->get(route('admin.popups.index', ['search' => 'Updated']))->assertSee('Updated');
    $this->get(route('admin.popups.index', ['search' => 'Not found']))->assertDontSee('Updated');
    $this->delete(route('admin.popups.destroy', $popup))->assertRedirect();
    expect(Popup::count())->toBe(0);
    expect(MediaAsset::whereKey($old->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists($old->path);
});

test('popup requests validate content image status and paired safe links', function (array $overrides, array $errors) {
    $this->actingAs($this->admin)->post(route('admin.popups.store'), popupPayload($overrides))->assertSessionHasErrors($errors);
    expect(Popup::count())->toBe(0);
})->with([
    'missing title' => [['title' => ''], ['title']], 'missing image' => [['image' => null], ['image']],
    'unsupported image' => [['image' => UploadedFile::fake()->create('poster.svg', 20, 'image/svg+xml')], ['image']],
    'oversized image' => [['image' => UploadedFile::fake()->image('poster.png')->size(6000)], ['image']],
    'long message' => [['message' => str_repeat('x', 5001)], ['message']], 'invalid status' => [['status' => 'scheduled'], ['status']],
    'unsafe url' => [['button_label' => 'Go', 'button_url' => 'javascript:alert(1)'], ['button_url']],
    'missing label' => [['button_url' => 'https://example.com'], ['button_label']], 'missing url' => [['button_label' => 'Go'], ['button_url']],
]);

test('permissions protect routes publication and service bypasses', function () {
    $this->get(route('admin.popups.index'))->assertRedirect(route('login'));
    $actor = popupActor(['popups.manage', 'popups.create', 'popups.edit', 'popups.delete']);
    $this->actingAs($actor);
    $this->post(route('admin.popups.store'), popupPayload(['status' => 'published']))->assertForbidden();
    $this->post(route('admin.popups.store'), popupPayload())->assertRedirect();
    $live = publishedPopup('Live', 1);
    $this->put(route('admin.popups.update', $live), ['title' => 'Changed', 'status' => 'draft'])->assertForbidden();
    $this->delete(route('admin.popups.destroy', $live))->assertForbidden();
    $this->postJson(route('admin.popups.order'), ['records' => [$live->id], 'original_order' => [$live->id]])->assertForbidden();
    expect(fn () => app(PopupService::class)->save(['title' => 'Bypass', 'status' => 'published'], $actor))->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    expect(fn () => app(PopupService::class)->delete($live, $actor))->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    $ordinary = User::factory()->create();
    $this->actingAs($ordinary)->get(route('admin.popups.index'))->assertForbidden();
    $this->get(route('admin.popups.create'))->assertForbidden();
    $this->get(route('admin.popups.edit', $live))->assertForbidden();
});

test('only highest priority eligible popup renders on homepage with escaped plain text', function () {
    $missing = Popup::factory()->create(['title' => 'Missing file', 'status' => 'published', 'sort_order' => 0]);
    $first = publishedPopup('First announcement', 1);
    publishedPopup('Second announcement', 2);
    Popup::factory()->create(['title' => 'Draft announcement', 'sort_order' => 0]);
    $first->update(['message' => '<script>alert(1)</script>']);
    $this->get('/')->assertOk()->assertSee('id="homepage-popup"', false)->assertSee('First announcement')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Second announcement')->assertDontSee('Missing file')->assertDontSee('Draft announcement');
    $this->get('/resources')->assertOk()->assertDontSee('id="homepage-popup"', false);
    $this->actingAs($this->admin)->put(route('admin.popups.update', $first), ['title' => 'First announcement', 'status' => 'draft'])->assertRedirect();
    $this->get('/')->assertSee('Second announcement')->assertDontSee('First announcement');
});

test('no popup renders without eligible media and deletion skips missing media', function () {
    $popup = Popup::factory()->create(['status' => 'published']);
    $popup->media->delete();
    $this->get('/')->assertOk()->assertDontSee('id="homepage-popup"', false);
});

test('priority changes are atomic and stale partial or duplicate submissions fail', function () {
    $first = publishedPopup('First', 1);
    $second = publishedPopup('Second', 2);
    $ids = [$first->id, $second->id];
    $this->actingAs($this->admin)->postJson(route('admin.popups.order'), ['records' => array_reverse($ids), 'original_order' => $ids])->assertOk();
    expect(app(PopupService::class)->active()->id)->toBe($second->id);
    $this->postJson(route('admin.popups.order'), ['records' => $ids, 'original_order' => $ids])->assertUnprocessable();
    $this->postJson(route('admin.popups.order'), ['records' => [$first->id], 'original_order' => [$first->id]])->assertUnprocessable();
    $this->postJson(route('admin.popups.order'), ['records' => [$first->id, $first->id], 'original_order' => array_reverse($ids)])->assertUnprocessable();
    expect(app(PopupService::class)->active()->id)->toBe($second->id);
});

test('failed save rolls back media database records and uploaded files without activity', function () {
    $repository = Mockery::mock(PopupRepositoryInterface::class);
    $repository->shouldReceive('nextSortOrder')->once()->andReturn(1);
    $repository->shouldReceive('create')->once()->andThrow(new RuntimeException('Failure'));
    app()->instance(PopupRepositoryInterface::class, $repository);
    expect(fn () => app(PopupService::class)->save(popupPayload(), $this->admin))->toThrow(RuntimeException::class);
    expect(Popup::count())->toBe(0);
    expect(MediaAsset::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
    expect(\Spatie\Activitylog\Models\Activity::where('event', 'popup.created')->exists())->toBeFalse();
});

test('successful popup workflows refresh frontend cache and log activity', function () {
    $version = Cache::get('frontend.version');
    $popup = app(PopupService::class)->save(popupPayload(), $this->admin);
    expect(Cache::get('frontend.version'))->not->toBe($version);
    expect(\Spatie\Activitylog\Models\Activity::where('event', 'popup.created')->exists())->toBeTrue();
    $version = Cache::get('frontend.version');
    app(PopupService::class)->delete($popup, $this->admin);
    expect(Cache::get('frontend.version'))->not->toBe($version);
    expect(\Spatie\Activitylog\Models\Activity::where('event', 'popup.deleted')->exists())->toBeTrue();
});
