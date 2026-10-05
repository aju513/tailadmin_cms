<?php

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\Grievance;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Repositories\Contracts\GrievanceRepositoryInterface;
use App\Services\GrievanceService;
use App\Services\SiteSettingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['app.url' => 'https://lumbini.example', 'cache.default' => 'array']);
    Cache::flush();
    Http::preventStrayRequests();
    Storage::fake('local');
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->page = Page::factory()->create(['title' => ['en' => 'Public Grievance'], 'slug' => 'grievance', 'path' => 'grievance', 'body' => null, 'page_type' => PageType::Grievance, 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);
    app(SiteSettingService::class)->update(['recaptcha_site_key' => 'test-site-key', 'recaptcha_secret_key' => 'test-secret-key'], $this->admin);
    $this->payload = ['message' => 'Please investigate this issue.', 'recaptcha_token' => 'test-token'];
    $this->verification = ['success' => true, 'action' => 'grievance_submit', 'score' => 0.9, 'hostname' => 'lumbini.example', 'challenge_ts' => now()->toIso8601String()];
    Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response($this->verification)]);
});

test('published grievance pages render the themed form and use their normal CMS paths', function (): void {
    $this->get('/grievance')->assertOk()->assertSee('Public Grievance')->assertSee('data-grievance-form', false)
        ->assertSee(route('public.grievances.store', $this->page->id), false)->assertSee('test-site-key')->assertDontSee('test-secret-key');
    $this->page->update(['path' => 'help/grievance']);
    $this->get('/help/grievance')->assertOk()->assertSee('data-grievance-form', false);
    Http::assertNothingSent();
});

test('administrators can create a grievance form page through the existing page editor', function (): void {
    $this->actingAs($this->admin)->get(route('admin.pages.create'))->assertOk()->assertSee('Grievance Form');
    $this->actingAs($this->admin)->post(route('admin.pages.store'), ['title' => 'New grievance', 'page_type' => 'grievance', 'slug' => 'new-grievance', 'status' => 'draft'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pages', ['path' => 'new-grievance', 'page_type' => 'grievance']);
});

test('anonymous submissions save a receipt without storing security tokens', function (): void {
    $response = $this->post(route('public.grievances.store', $this->page->id), $this->payload);
    $response->assertRedirect(route('public.page', ['path' => 'grievance', 'lang' => 'en']))->assertSessionHas('grievance_success');
    $grievance = Grievance::sole();
    expect($grievance->reference)->toStartWith('GRV-')->and($grievance->page_id)->toBe($this->page->id)
        ->and($grievance->full_name)->toBeNull()->and($grievance->message)->toBe($this->payload['message'])
        ->and(json_encode($grievance->getAttributes()))->not->toContain('test-token');
    Http::assertSent(fn ($request) => $request->url() === 'https://www.google.com/recaptcha/api/siteverify' && $request['response'] === 'test-token' && $request['secret'] === 'test-secret-key');
    expect(Activity::where('event', 'grievance.submitted')->sole()->properties->toJson())->not->toContain('test-token', 'test-secret-key', $this->payload['message']);
});

test('attachments and provided contact details are stored privately', function (): void {
    $this->post(route('public.grievances.store', $this->page->id), [...$this->payload, 'full_name' => 'Visitor', 'email' => 'visitor@example.test', 'phone' => '9800000000', 'subject' => 'Request', 'attachment' => UploadedFile::fake()->create('evidence.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();
    $grievance = Grievance::sole();
    Storage::disk('local')->assertExists($grievance->attachment_path);
    Storage::disk('public')->assertMissing($grievance->attachment_path);
    expect($grievance->attachment_name)->toBe('evidence.pdf')->and($grievance->attachment_mime)->toBe('application/pdf')->and($grievance->email)->toBe('visitor@example.test');
    $this->get('/storage/'.$grievance->attachment_path)->assertNotFound();
});

test('public input is validated before verification or persistence', function (array $invalid, string $field): void {
    $this->from('/grievance')->post(route('public.grievances.store', $this->page->id), [...$this->payload, ...$invalid])->assertSessionHasErrors($field)->assertSessionMissing('_old_input.recaptcha_token');
    expect(Grievance::count())->toBe(0);
    Http::assertNothingSent();
})->with([
    'missing message' => [['message' => ''], 'message'],
    'long message' => [['message' => str_repeat('a', 10001)], 'message'],
    'bad email' => [['email' => 'invalid'], 'email'],
    'long phone' => [['phone' => str_repeat('1', 41)], 'phone'],
    'missing token' => [['recaptcha_token' => null], 'recaptcha_token'],
    'untrusted language' => [['lang' => 'xx'], 'lang'],
]);

test('unsafe or oversized attachment uploads are rejected', function (string $name, int $size, string $mime): void {
    $this->post(route('public.grievances.store', $this->page->id), [...$this->payload, 'attachment' => UploadedFile::fake()->create($name, $size, $mime)])->assertSessionHasErrors('attachment');
    expect(Grievance::count())->toBe(0)->and(Storage::disk('local')->allFiles())->toBe([]);
    Http::assertNothingSent();
})->with([
    'executable' => ['script.php', 1, 'application/x-php'],
    'wrong extension' => ['script.php', 1, 'application/pdf'],
    'too large' => ['large.pdf', 5121, 'application/pdf'],
]);

test('unpublished scheduled and other page types cannot receive grievances', function (array $attributes): void {
    $this->page->update($attributes);
    $this->post(route('public.grievances.store', $this->page->id), $this->payload)->assertNotFound();
    expect(Grievance::count())->toBe(0);
    Http::assertNothingSent();
})->with([
    'draft' => [['status' => ContentStatus::Draft]],
    'scheduled' => [fn () => ['published_at' => now()->addDay()]],
    'article' => [['page_type' => PageType::Article]],
]);

test('missing or unreadable keys disable the form and block direct posts', function (string $key, ?string $value): void {
    SiteSetting::where('key', $key)->update(['value' => $value]);
    app(\App\Services\Frontend\FrontendCache::class)->clear();
    $this->get('/grievance')->assertOk()->assertSee('temporarily unavailable')->assertSee('name="message"', false)->assertSee('disabled', false)->assertDontSee('data-grievance-form', false);
    $this->from('/grievance')->post(route('public.grievances.store', $this->page->id), $this->payload)->assertSessionHasErrors('recaptcha_token');
    expect(Grievance::count())->toBe(0);
    Http::assertNothingSent();
})->with([
    'site key absent' => ['recaptcha_site_key', null],
    'secret absent' => ['recaptcha_secret_key', null],
    'unreadable secret' => ['recaptcha_secret_key', 'not-encrypted'],
]);

test('rejected suspicious or malformed verification responses cannot create submissions', function (array $overrides): void {
    Http::swap(new \Illuminate\Http\Client\Factory);
    Http::preventStrayRequests();
    Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response([...$this->verification, ...$overrides])]);
    $this->from('/grievance')->post(route('public.grievances.store', $this->page->id), $this->payload)->assertSessionHasErrors('recaptcha_token')->assertSessionMissing('_old_input.recaptcha_token');
    expect(Grievance::count())->toBe(0)->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'rejected' => [['success' => false]],
    'wrong action' => [['action' => 'contact']],
    'low score' => [['score' => 0.1]],
    'missing score' => [['score' => null]],
    'impossible score' => [['score' => 2]],
    'wrong hostname' => [['hostname' => 'evil.example']],
    'malformed hostname' => [['hostname' => []]],
    'stale token' => [fn () => ['challenge_ts' => now()->subMinutes(3)->toIso8601String()]],
    'future token' => [fn () => ['challenge_ts' => now()->addMinutes(3)->toIso8601String()]],
    'invalid timestamp' => [['challenge_ts' => 'not-a-date']],
]);

test('verification transport failures do not create a submission', function (string $failure): void {
    Http::swap(new \Illuminate\Http\Client\Factory);
    Http::preventStrayRequests();
    Http::fake(['www.google.com/recaptcha/api/siteverify' => match ($failure) {
        'connection' => Http::failedConnection(),
        'http' => Http::response('unavailable', 503),
        default => Http::response('not-json', 200),
    }]);
    $this->post(route('public.grievances.store', $this->page->id), $this->payload)->assertSessionHasErrors('recaptcha_token')->assertSessionMissing('grievance_success');
    expect(Grievance::count())->toBe(0);
})->with(['connection', 'http', 'invalid-json']);

test('failed database writes remove an uploaded attachment', function (): void {
    $repository = Mockery::mock(GrievanceRepositoryInterface::class);
    $repository->shouldReceive('publishedPage')->with($this->page->id, true)->once()->andReturn($this->page);
    $repository->shouldReceive('create')->once()->andThrow(new RuntimeException('Simulated persistence failure'));
    app()->instance(GrievanceRepositoryInterface::class, $repository);
    expect(fn () => app(GrievanceService::class)->submit($this->page->id, [...$this->payload, 'attachment' => UploadedFile::fake()->create('evidence.pdf', 50, 'application/pdf')]))->toThrow(RuntimeException::class);
    expect(Storage::disk('local')->allFiles())->toBe([])->and(Grievance::count())->toBe(0);
});

test('secret settings are encrypted retained and never shown or flashed', function (): void {
    $encrypted = SiteSetting::where('key', 'recaptcha_secret_key')->value('value');
    expect($encrypted)->not->toBe('test-secret-key')->and(Crypt::decryptString($encrypted))->toBe('test-secret-key');
    expect(app(SiteSettingService::class)->all())->not->toHaveKey('recaptcha_secret_key');
    $this->actingAs($this->admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('reCAPTCHA v3')->assertDontSee('test-secret-key')->assertDontSee($encrypted);
    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['site_name' => 'Institute', 'recaptcha_site_key' => 'test-site-key', 'recaptcha_secret_key' => ''])->assertSessionHasNoErrors();
    expect(SiteSetting::where('key', 'recaptcha_secret_key')->value('value'))->toBe($encrypted);
    $this->from(route('admin.settings.edit'))->put(route('admin.settings.update'), ['site_name' => '', 'recaptcha_secret_key' => 'replacement-secret'])->assertSessionHasErrors('site_name')->assertSessionMissing('_old_input.recaptcha_secret_key');
    expect(Activity::where('event', 'settings.updated')->get()->toJson())->not->toContain('test-secret-key', 'replacement-secret');
});

test('recaptcha settings require a matching key pair and settings permission', function (): void {
    SiteSetting::whereIn('key', ['recaptcha_site_key', 'recaptcha_secret_key'])->delete();
    $this->actingAs($this->admin)->put(route('admin.settings.update'), ['site_name' => 'Institute', 'recaptcha_site_key' => 'site-only'])->assertSessionHasErrors('recaptcha_secret_key');
    $this->put(route('admin.settings.update'), ['site_name' => 'Institute', 'recaptcha_secret_key' => 'secret-only'])->assertSessionHasErrors('recaptcha_site_key');
    $ordinary = User::factory()->create(['status' => 'active']);
    $this->actingAs($ordinary)->put(route('admin.settings.update'), ['site_name' => 'Institute', 'recaptcha_site_key' => 'new-key', 'recaptcha_secret_key' => 'new-secret'])->assertForbidden();
});

test('admin review is searchable paginated escaped and permission protected', function (): void {
    Grievance::factory()->count(16)->create();
    $item = Grievance::factory()->create(['subject' => 'Unique grievance', 'message' => '<script>alert(1)</script>']);
    $this->actingAs($this->admin)->get(route('admin.grievances.index'))->assertOk()->assertSee('page=2');
    $this->get(route('admin.grievances.index', ['search' => 'Unique grievance']))->assertOk()->assertSee('Unique grievance')->assertViewHas('items', fn ($items) => $items->total() === 1);
    $this->get(route('admin.grievances.show', $item->id))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->get(route('admin.grievances.index', ['search' => str_repeat('x', 101)]))->assertSessionHasErrors('search');
    $ordinary = User::factory()->create(['status' => 'active']);
    $this->actingAs($ordinary)->get(route('admin.grievances.index'))->assertForbidden();
    $this->get(route('admin.grievances.show', $item->id))->assertForbidden();
    $role = Role::create(['name' => 'grievance-list-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('grievances.manage');
    $ordinary->assignRole($role);
    $this->get(route('admin.grievances.index'))->assertOk()->assertDontSee('View details');
    $this->get(route('admin.grievances.show', $item->id))->assertForbidden();
});

test('private attachment downloads require authentication and detail permission', function (): void {
    Storage::disk('local')->put('grievances/evidence.pdf', 'private evidence');
    $item = Grievance::factory()->create(['attachment_path' => 'grievances/evidence.pdf', 'attachment_name' => 'evidence.pdf']);
    $route = route('admin.grievances.download', $item->id);
    $this->get($route)->assertRedirect(route('login'));
    $ordinary = User::factory()->create(['status' => 'active']);
    $this->actingAs($ordinary)->get($route)->assertForbidden();
    $this->actingAs($this->admin)->get($route)->assertOk()->assertDownload('evidence.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
    Storage::disk('local')->delete('grievances/evidence.pdf');
    $this->get($route)->assertNotFound();
});

test('page deletion preserves grievance source snapshots', function (): void {
    $item = Grievance::factory()->create(['page_id' => $this->page->id, 'page_title' => $this->page->title, 'page_path' => $this->page->path]);
    $this->page->delete();
    expect($item->fresh()->page_id)->toBeNull()->and($item->fresh()->page_title)->toBe('Public Grievance')->and($item->fresh()->page_path)->toBe('grievance');
});

test('public submission attempts are rate limited', function (): void {
    $route = route('public.grievances.store', $this->page->id);
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $this->post($route, [])->assertSessionHasErrors('message');
    }
    $this->post($route, $this->payload)->assertStatus(429);
    expect(Grievance::count())->toBe(0);
    Http::assertNothingSent();
});
