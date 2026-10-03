<?php

use App\Enums\ContentStatus;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    config([
        'cache.default' => 'array',
        'frontend.assets.hot_file' => 'framework/testing-vite-front.none',
        'admin.assets.hot_file' => 'framework/testing-vite-admin.none',
    ]);
    Cache::flush();
});

test('public and admin screens render their own views and production bundles', function (): void {
    $this->get('/')->assertOk()->assertViewIs('front.pages.home')
        ->assertSee('/build/front/assets/', false)->assertDontSee('/build/admin/', false);
    $this->get('/admin/login')->assertOk()->assertViewIs('admin.auth.signin')
        ->assertSee('/build/admin/assets/', false)->assertDontSee('/build/front/', false);

    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->actingAs(User::findOrFail(1))->get('/admin')->assertOk()->assertViewIs('admin.pages.dashboard.ecommerce');
});

test('frontend navigation and branding use configured defaults', function (): void {
    config([
        'frontend.menu_locations.header' => 'unassigned-header',
        'frontend.menu_locations.footer' => 'unassigned-footer',
        'frontend.navigation.header' => [
            ['label' => 'Configured header', 'route' => 'public.contact', 'children' => [
                ['label' => 'Configured child', 'url' => 'https://example.test/child', 'external' => true],
            ]],
            ['label' => 'Unsafe link', 'url' => 'javascript:alert(1)'],
        ],
        'frontend.navigation.footer' => [['label' => 'Configured footer', 'route' => 'public.resources.index']],
        'frontend.defaults.province_name' => 'Configured Province',
        'frontend.defaults.office_hours' => 'Configured hours',
        'frontend.defaults.footer_text' => 'Configured copyright',
        'frontend.branding.logo' => 'front/images/configured-logo.svg',
    ]);

    $this->get('/search')->assertOk()->assertSee('Configured header')->assertSee('Configured child')
        ->assertSee('Configured footer')->assertSee('Configured Province')->assertSee('Configured hours')
        ->assertSee('Configured copyright')->assertSee('front/images/configured-logo.svg', false)->assertDontSee('Unsafe link');
});

test('CMS settings and menus take precedence over frontend defaults', function (): void {
    foreach (['site_name' => 'Managed Institute', 'office_hours' => 'Managed office hours', 'province_name' => 'Managed Province', 'footer_text' => 'Managed copyright'] as $key => $value) {
        SiteSetting::create(['key' => $key, 'value' => $value, 'type' => 'text']);
    }
    foreach (['header' => 'Managed header', 'footer' => 'Managed footer'] as $location => $label) {
        $menu = Menu::updateOrCreate(['location' => $location], ['name' => $label]);
        MenuItem::create(['menu_id' => $menu->id, 'label' => $label, 'external_url' => '/contact', 'sort_order' => 0]);
    }
    config([
        'frontend.navigation.header' => [['label' => 'Fallback header', 'route' => 'public.home']],
        'frontend.navigation.footer' => [['label' => 'Fallback footer', 'route' => 'public.home']],
    ]);

    $this->get('/')->assertOk()->assertSee('Managed Institute')->assertSee('Managed header')->assertSee('Managed footer')
        ->assertSee('Managed office hours')->assertSee('Managed Province')->assertSee('Managed copyright')
        ->assertDontSee('Fallback header')->assertDontSee('Fallback footer');
});

test('new frontend views receive shared header footer and metadata from the layout composer', function (): void {
    SiteSetting::create(['key' => 'site_name', 'value' => 'Standalone Institute', 'type' => 'text']);

    $html = view('front.layouts.app')->render();

    expect($html)->toContain('Standalone Institute', '<header', '<footer', '<main id="content">', '<title>', '/build/front/assets/')
        ->not->toContain('/build/admin/');
});

test('an intentionally empty CMS menu keeps configured fallback links hidden', function (): void {
    config([
        'frontend.navigation.header' => [['label' => 'Fallback header', 'route' => 'public.home']],
        'frontend.navigation.footer' => [['label' => 'Fallback footer', 'route' => 'public.home']],
    ]);

    $this->get('/')->assertOk()->assertDontSee('Fallback header')->assertDontSee('Fallback footer');
});

test('public CMS paths cannot take over protected admin routes', function (): void {
    Page::factory()->create(['path' => 'admin/custom-page', 'slug' => 'custom-page', 'status' => ContentStatus::Published, 'published_at' => now()->subHour()]);

    $this->get('/admin/custom-page')->assertNotFound();
    $this->get('/admin/settings')->assertRedirect('/admin/login');
    $this->actingAs(User::factory()->create(['status' => 'active']))->get('/admin/settings')->assertForbidden();
});

test('admin and frontend use independent Vite development servers', function (): void {
    $suffix = uniqid('application-structure-', true);
    $frontHot = 'framework/'.$suffix.'-front.hot';
    $adminHot = 'framework/'.$suffix.'-admin.hot';
    config(['frontend.assets.hot_file' => $frontHot, 'admin.assets.hot_file' => $adminHot]);
    file_put_contents(storage_path($frontHot), 'http://localhost:5193');
    file_put_contents(storage_path($adminHot), 'http://localhost:5194');

    try {
        $this->get('/')->assertOk()->assertSee('http://localhost:5193/resources/front/js/app.js', false)->assertDontSee('localhost:5194');
        $this->get('/admin/login')->assertOk()->assertSee('http://localhost:5194/resources/admin/js/app.js', false)->assertDontSee('localhost:5193');
    } finally {
        unlink(storage_path($frontHot));
        unlink(storage_path($adminHot));
    }
});
