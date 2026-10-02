<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('page status endpoints return updated state for ajax requests', function (): void {
    $page = Page::factory()->create(['status' => ContentStatus::Draft]);
    $this->actingAs($this->admin)->postJson(route('admin.pages.publish', $page))
        ->assertOk()->assertJsonPath('pages.0.id', $page->id)->assertJsonPath('pages.0.status', 'published');
    expect($page->fresh()->status)->toBe(ContentStatus::Published);
    $this->postJson(route('admin.pages.unpublish', $page))
        ->assertOk()->assertJsonPath('pages.0.status', 'draft');
    expect($page->fresh()->published_at)->toBeNull();
});

test('bulk ajax status updates only selected pages and validates input', function (): void {
    $page = Page::factory()->create(['status' => ContentStatus::Draft]);
    $other = Page::factory()->create(['status' => ContentStatus::Draft]);
    $this->actingAs($this->admin)->patchJson(route('admin.pages.bulk-status'), ['pages' => [$page->id], 'status' => 'published'])
        ->assertOk()->assertJsonPath('pages.0.id', $page->id)->assertJsonPath('pages.0.status', 'published');
    expect($page->fresh()->status)->toBe(ContentStatus::Published)
        ->and($other->fresh()->status)->toBe(ContentStatus::Draft);
    $this->patchJson(route('admin.pages.bulk-status'), ['pages' => [], 'status' => 'invalid'])
        ->assertUnprocessable()->assertJsonValidationErrors(['pages', 'status']);
});

test('ajax status changes require publish permission and do not mutate on denial', function (): void {
    $page = Page::factory()->create(['status' => ContentStatus::Draft]);
    $this->actingAs(User::factory()->create())->postJson(route('admin.pages.publish', $page))->assertForbidden();
    $this->postJson(route('admin.pages.unpublish', $page))->assertForbidden();
    $this->patchJson(route('admin.pages.bulk-status'), ['pages' => [$page->id], 'status' => 'published'])->assertForbidden();
    expect($page->fresh()->status)->toBe(ContentStatus::Draft);
});
