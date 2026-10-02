<?php

use App\Models\HomepageSlide;
use App\Models\TeamCategory;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

dataset('ajax index managers', [
    ['news', \App\Models\News::class, 'news', 'status', 'published', 'draft'],
    ['notices', \App\Models\Notice::class, 'notices', 'status', 'published', 'draft'],
    ['resources', \App\Models\ResourceDocument::class, 'resources', 'status', 'published', 'draft'],
    ['resource-categories', \App\Models\ResourceCategory::class, 'categories', 'is_active', '1', '0'],
    ['notice-categories', \App\Models\NoticeCategory::class, 'categories', 'is_active', '1', '0'],
    ['team-members', \App\Models\TeamMember::class, 'members', 'is_active', '1', '0'],
    ['team-categories', \App\Models\TeamCategory::class, 'categories', 'status', '1', '0'],
    ['halls', \App\Models\Hall::class, 'halls', 'status', 'published', 'draft'],
    ['homepage-slides', \App\Models\HomepageSlide::class, 'records', 'status', 'published', 'draft'],
    ['videos', \App\Models\Video::class, 'records', 'status', 'published', 'draft'],
    ['gallery', \App\Models\GalleryAlbum::class, 'records', 'status', 'published', 'draft'],
    ['users', \App\Models\User::class, 'users', 'status', 'active', 'inactive'],
]);

test('index managers render shared controls and update only selected records via ajax', function (string $module, string $model, string $key, string $field, string $active, string $inactive): void {
    $make = function () use ($model, $field, $inactive) {
        if ($model === HomepageSlide::class) {
            return HomepageSlide::create(['title' => 'Test slide', 'status' => 'draft', 'sort_order' => 0]);
        }
        if ($model === TeamCategory::class) {
            return TeamCategory::create(['name' => fake()->unique()->word(), 'slug' => fake()->unique()->slug(), 'status' => false, 'sort_order' => 0]);
        }

        return $model::factory()->create([$field => $inactive]);
    };
    $record = $make();
    $other = $make();
    $this->actingAs($this->admin)->get(route("admin.{$module}.index"))
        ->assertOk()->assertDontSee('@js(', false)->assertSee('table-checkbox-tick', false)
        ->assertSee('page-status-control', false)->assertSee('selected = $event.target.checked', false)
        ->assertSee('x-effect="$el.indeterminate', false);
    $this->patchJson(route("admin.{$module}.bulk-status"), [$key => [$record->id], 'status' => $active])
        ->assertOk()->assertJsonPath('records.0.id', $record->id)->assertJsonPath('records.0.status', $active);
    $value = $record->fresh()->getAttribute($field);
    expect($value instanceof \BackedEnum ? $value->value : (string) (int) $value)->toBe($active);
    $value = $other->fresh()->getAttribute($field);
    expect($value instanceof \BackedEnum ? $value->value : (string) (int) $value)->toBe($inactive);
    $this->patchJson(route("admin.{$module}.bulk-status"), [$key => [$record->id], 'status' => $inactive])
        ->assertOk()->assertJsonPath('records.0.status', $inactive);
    $this->patchJson(route("admin.{$module}.bulk-status"), [$key => [], 'status' => $active])
        ->assertUnprocessable()->assertJsonValidationErrors([$key]);
    $this->actingAs(User::factory()->create())->patchJson(route("admin.{$module}.bulk-status"), [$key => [$record->id], 'status' => $active])->assertForbidden();
})->with('ajax index managers');

test('ajax user status retains the last super administrator safeguard', function (): void {
    $this->actingAs($this->admin)->patchJson(route('admin.users.bulk-status'), ['users' => [$this->admin->id], 'status' => 'inactive'])
        ->assertUnprocessable()->assertJsonValidationErrors(['status']);
    expect($this->admin->fresh()->isActive())->toBeTrue();
});
