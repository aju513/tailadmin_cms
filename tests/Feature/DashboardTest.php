<?php

use App\Models\DashboardReport;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\GoogleReportingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Http::preventStrayRequests();
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('dashboard access requires authentication and permission', function () {
    $this->get(route('admin.dashboard'))->assertRedirect('/admin/login');
    $operator = User::factory()->create();
    $this->actingAs($operator)->get(route('admin.dashboard'))->assertForbidden();
    $role = Role::create(['name' => 'dashboard-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('dashboard.view');
    $operator->assignRole($role);
    $this->actingAs($operator)->get(route('admin.dashboard'))->assertOk()->assertSee('Active Users');
    Http::assertNothingSent();
});

test('dashboard validates reporting periods before fetching reports', function ($days) {
    $this->actingAs($this->admin)->getJson(route('admin.dashboard', ['days' => $days]))->assertUnprocessable()->assertJsonValidationErrors('days');
    expect(DashboardReport::count())->toBe(0);
})->with([0, 8, 365, 'invalid']);

test('unconfigured reports render unavailable states without network calls', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Google Analytics unavailable')->assertDontSee('Search Console unavailable')
        ->assertDontSee('Top 15 Search Queries');
    Http::assertNothingSent();
});

test('dashboard renders real reports and caches each reporting period', function () {
    $this->app->instance('env', 'production');
    $this->travelTo(now()->setDate(2026, 9, 30));
    $analytics = ['active' => 120, 'new' => 80, 'returning' => 40,
        'countries' => [['label' => 'Nepal', 'value' => 200]],
        'devices' => [['label' => 'desktop', 'value' => 120]],
        'pages' => [['title' => '<script>unsafe</script>', 'url' => 'https://example.com/about', 'views' => 200]]];
    $this->mock(GoogleReportingService::class, function ($mock) use ($analytics) {
        $mock->shouldReceive('analytics')->once()->with('2026-09-23', '2026-09-29')->andReturn($analytics);
        $mock->shouldReceive('search')->once()->with('2026-09-23', '2026-09-29')->andReturn([
            ['query' => 'example query', 'clicks' => 12, 'impressions' => 100, 'ctr' => 12, 'position' => 2.5],
        ]);
    });
    for ($visit = 0; $visit < 2; $visit++) {
        $this->actingAs($this->admin)->get(route('admin.dashboard', ['days' => 7]))->assertOk()
            ->assertSee('Nepal')->assertSee('example query')->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)
            ->assertDontSee('<script>unsafe</script>', false)->assertSee('data-dashboard-chart', false);
    }
    expect(DashboardReport::count())->toBe(2);
});

test('one provider failure does not hide the other and is retried after one minute', function () {
    $this->app->instance('env', 'production');
    $this->mock(GoogleReportingService::class, function ($mock) {
        $mock->shouldReceive('analytics')->twice()->andThrow(new RuntimeException('secret-token'));
        $mock->shouldReceive('search')->once()->andReturn([]);
    });
    $service = app(DashboardService::class);
    $first = $service->overview(30);
    expect($first['analytics']['available'])->toBeFalse()->and($first['search']['available'])->toBeTrue();
    $service->overview(30);
    $this->travel(61)->seconds();
    $service->overview(30);
    expect(json_encode(DashboardReport::all()->toArray()))->not->toContain('secret-token');
});

test('empty Google reports are successful empty states', function () {
    $this->app->instance('env', 'production');
    $this->mock(GoogleReportingService::class, function ($mock) {
        $mock->shouldReceive('analytics')->andReturn(['active' => 0, 'new' => 0, 'returning' => 0, 'countries' => [], 'devices' => [], 'pages' => []]);
        $mock->shouldReceive('search')->andReturn([]);
    });
    $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
        ->assertSee('No page views for this period.')->assertSee('No search queries for this period.')
        ->assertDontSee('Google Analytics unavailable');
});

test('Google adapter maps reports by dimension name and normalizes search metrics', function () {
    $path = tempnam(sys_get_temp_dir(), 'dashboard-test-');
    file_put_contents($path, json_encode(['client_email' => 'test@example.com', 'private_key' => 'test-key']));
    config(['dashboard.property_id' => '123', 'dashboard.site_url' => 'sc-domain:example.com', 'dashboard.analytics_credentials' => $path, 'dashboard.search_credentials' => $path]);
    foreach (['analytics.readonly', 'webmasters.readonly'] as $scope) {
        Cache::put('dashboard-google-token:'.hash('sha256', 'test-key'.$scope), 'test-token', 3000);
    }
    $row = fn ($dimensions, $metric) => ['dimensionValues' => array_map(fn ($value) => ['value' => $value], $dimensions), 'metricValues' => [['value' => (string) $metric]]];
    Http::fake([
        '*:batchRunReports' => Http::response(['reports' => [
            ['rows' => [$row([], 10)]], ['rows' => [$row(['returning'], 3), $row(['new'], 7)]],
            ['rows' => [$row(['Nepal'], 20)]], ['rows' => [$row(['desktop'], 10)]],
            ['rows' => [$row(['About', 'example.com/about'], 20)]],
        ]]),
        '*searchAnalytics/query' => Http::response(['rows' => [['keys' => ['example'], 'clicks' => 3, 'impressions' => 10, 'ctr' => 0.3, 'position' => 1.234]]]),
    ]);
    try {
        $service = app(GoogleReportingService::class);
        $analytics = $service->analytics('2026-09-01', '2026-09-29');
        expect($analytics['new'])->toBe(7)->and($analytics['returning'])->toBe(3)
            ->and($analytics['pages'][0]['url'])->toBe('https://example.com/about');
        $search = $service->search('2026-09-01', '2026-09-29');
        expect($search[0]['ctr'])->toBe(30.0)->and($search[0]['position'])->toBe(1.23);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'batchRunReports') && count($request['requests']) === 5 && $request['requests'][0]['dateRanges'][0]['endDate'] === '2026-09-29');
        Http::assertSent(fn ($request) => str_contains($request->url(), 'sc-domain%3Aexample.com') && $request['rowLimit'] === 15 && $request['dataState'] === 'final');
    } finally {
        unlink($path);
    }
});

test('Search Console runs and appears only in production', function (string $environment) {
    $this->app->instance('env', $environment);
    $this->mock(GoogleReportingService::class, function ($mock) use ($environment) {
        $mock->shouldReceive('analytics')->once()->andReturn(['active' => 0, 'new' => 0, 'returning' => 0, 'countries' => [], 'devices' => [], 'pages' => []]);
        if ($environment === 'production') {
            $mock->shouldReceive('search')->once()->andThrow(new RuntimeException('secret-token'));
        } else {
            $mock->shouldNotReceive('search');
        }
    });
    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertDontSee('secret-token');
    if ($environment === 'production') {
        $response->assertSee('Top 15 Search Queries')->assertSee('Search Console unavailable');
        expect(DashboardReport::count())->toBe(2);
    } else {
        $response->assertDontSee('Top 15 Search Queries')->assertDontSee('Search Console unavailable');
        expect(DashboardReport::count())->toBe(1);
    }
})->with(['local', 'testing', 'production']);
