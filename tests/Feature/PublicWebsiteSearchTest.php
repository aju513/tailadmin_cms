<?php

use App\Enums\ContentStatus;
use App\Models\News;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
});

test('public search matches every word across fields and excludes unpublished content', function (): void {
    Page::factory()->create(['title' => ['en' => 'Training courses'], 'body' => ['en' => '<p>Local government development.</p>'], 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    News::factory()->create(['title' => 'Training update', 'summary' => 'Local government development', 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    News::factory()->create(['title' => 'Training draft', 'summary' => 'Local government development', 'status' => ContentStatus::Draft]);
    News::factory()->create(['title' => 'Training scheduled', 'summary' => 'Local government development', 'status' => ContentStatus::Published, 'published_at' => now()->addDay()]);
    TeamMember::factory()->create(['name' => 'Training coordinator', 'designation' => 'Government development officer', 'is_active' => false]);

    $response = $this->get(route('public.search', ['q' => '  government   training  ']))->assertOk()
        ->assertSee('Training courses')->assertSee('Training update')
        ->assertDontSee('Training draft')->assertDontSee('Training scheduled')->assertDontSee('Training coordinator');
    expect($response->viewData('results')['pages']->total())->toBe(1)
        ->and($response->viewData('results')['news']->total())->toBe(1);
});

test('search treats SQL wildcard characters as literal text and handles empty unicode whitespace', function (): void {
    News::factory()->create(['title' => '100% attendance_target', 'status' => 'published', 'published_at' => now()->subDay()]);
    News::factory()->create(['title' => '1000 attendance target', 'status' => 'published', 'published_at' => now()->subDay()]);

    $this->get(route('public.search', ['q' => '100% attendance_target']))->assertOk()->assertSee('100% attendance_target')->assertDontSee('1000 attendance target');
    $this->get(route('public.search', ['q' => "\u{00A0}\u{2003}"]))->assertOk()->assertSee('Enter a term to search published website content.')->assertDontSee('100% attendance_target');
    $this->get(route('public.search', ['search' => 'attendance_target']))->assertOk()->assertSee('100% attendance_target')->assertDontSee('1000 attendance target');
});

test('header and search results expose labelled native search controls', function (): void {
    $this->get(route('public.search', ['q' => 'No matching record']))->assertOk()
        ->assertSee('aria-controls="site-search-dialog"', false)
        ->assertSee('<dialog id="site-search-dialog"', false)
        ->assertSee('aria-labelledby="site-search-title"', false)
        ->assertSee('id="site-search-input" type="search" name="q"', false)
        ->assertSee('aria-label="Submit search"', false)
        ->assertSee('class="site-search-form" role="search"', false)
        ->assertSee('No results found.');
});
