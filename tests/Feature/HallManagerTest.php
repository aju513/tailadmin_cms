<?php

use App\Enums\ContentStatus;
use App\Models\Hall;
use App\Models\User;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('hall manager renders and bulk changes affect only selected halls', function (): void {
    $selected = Hall::factory()->create();
    $other = Hall::factory()->create();
    $this->actingAs($this->admin)->get(route('admin.halls.index'))
        ->assertOk()->assertSee('Add Hall')->assertSee('Bulk delete');
    $this->patch(route('admin.halls.bulk-status'), ['halls' => [$selected->id], 'status' => 'published'])->assertRedirect();
    expect($selected->fresh()->status)->toBe(ContentStatus::Published)
        ->and($selected->fresh()->published_by)->toBe($this->admin->id)
        ->and($other->fresh()->status)->toBe(ContentStatus::Draft);
    $this->patch(route('admin.halls.bulk-status'), ['halls' => [$selected->id], 'status' => 'draft'])->assertRedirect();
    expect($selected->fresh()->published_at)->toBeNull();
    $this->delete(route('admin.halls.bulk-destroy'), ['halls' => [$selected->id]])->assertRedirect();
    $this->assertDatabaseMissing('halls', ['id' => $selected->id]);
    $this->assertDatabaseHas('halls', ['id' => $other->id]);
});

test('hall bulk actions validate selections and require permissions', function (): void {
    $hall = Hall::factory()->create();
    $this->actingAs($this->admin)->patchJson(route('admin.halls.bulk-status'), ['halls' => [], 'status' => 'invalid'])
        ->assertUnprocessable()->assertJsonValidationErrors(['halls', 'status']);
    $this->deleteJson(route('admin.halls.bulk-destroy'), ['halls' => [999999]])->assertUnprocessable();
    $this->actingAs(User::factory()->create())->patchJson(route('admin.halls.bulk-status'), ['halls' => [$hall->id], 'status' => 'published'])->assertForbidden();
    $this->deleteJson(route('admin.halls.bulk-destroy'), ['halls' => [$hall->id]])->assertForbidden();
    expect($hall->fresh()->status)->toBe(ContentStatus::Draft);
});
