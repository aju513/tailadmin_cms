<?php

use App\Enums\ContentStatus;
use App\Models\GalleryAlbum;
use App\Models\HomepageSlide;
use App\Models\User;
use App\Models\Video;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

dataset('media managers', [
    ['homepage-slides', HomepageSlide::class, 'homepage_slides', 'Add Slide'],
    ['videos', Video::class, 'videos', 'Add Video'],
    ['gallery', GalleryAlbum::class, 'gallery_albums', 'Add Gallery'],
]);

test('media managers render and bulk actions affect only selected records', function (string $module, string $model, string $table, string $label): void {
    $make = fn () => $model === HomepageSlide::class
        ? HomepageSlide::create(['title' => 'Test slide', 'status' => ContentStatus::Draft, 'sort_order' => 0])
        : $model::factory()->create();
    $selected = $make();
    $other = $make();
    $this->actingAs($this->admin)->get(route("admin.{$module}.index"))
        ->assertOk()->assertSee($label)->assertSee('Bulk delete');
    if ($module === 'homepage-slides') {
        $this->get(route("admin.{$module}.index"))
            ->assertSee('Select all slides on this page')
            ->assertSee('table-checkbox-tick', false)
            ->assertSee('x-model="selected"', false);
    }
    $this->patch(route("admin.{$module}.bulk-status"), ['records' => [$selected->id], 'status' => 'published'])->assertRedirect();
    expect($selected->fresh()->status)->toBe(ContentStatus::Published)
        ->and($other->fresh()->status)->toBe(ContentStatus::Draft);
    $this->patch(route("admin.{$module}.bulk-status"), ['records' => [$selected->id], 'status' => 'draft'])->assertRedirect();
    expect($selected->fresh()->status)->toBe(ContentStatus::Draft);
    $this->delete(route("admin.{$module}.bulk-destroy"), ['records' => [$selected->id]])->assertRedirect();
    $this->assertDatabaseMissing($table, ['id' => $selected->id]);
    $this->assertDatabaseHas($table, ['id' => $other->id]);
})->with('media managers');

test('media bulk actions validate selections and require permissions', function (string $module, string $model, string $table, string $label): void {
    $this->actingAs($this->admin)->patchJson(route("admin.{$module}.bulk-status"), ['records' => [], 'status' => 'invalid'])
        ->assertUnprocessable()->assertJsonValidationErrors(['records', 'status']);
    $this->deleteJson(route("admin.{$module}.bulk-destroy"), ['records' => [999999]])->assertUnprocessable();
    $this->actingAs(User::factory()->create())->patchJson(route("admin.{$module}.bulk-status"), ['records' => [1], 'status' => 'published'])->assertForbidden();
    $this->deleteJson(route("admin.{$module}.bulk-destroy"), ['records' => [1]])->assertForbidden();
})->with('media managers');
