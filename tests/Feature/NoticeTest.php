<?php

use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

test('admin can publish a notice with an attachment and it is public', function (): void {
    Storage::fake('public');
    $this->actingAs($this->admin)->post(route('admin.notices.store'), [
        'title' => 'Public holiday notice', 'description' => '<p>Office closed Monday.</p>', 'sort_order' => 1,
        'status' => 'published', 'file' => UploadedFile::fake()->create('notice.pdf', 20, 'application/pdf'),
        'meta_title' => 'Holiday notice',
    ])->assertRedirect(route('admin.notices.index'));
    $notice = Notice::firstOrFail();
    expect($notice->slug)->toBe('public-holiday-notice')->and($notice->fileMedia)->not->toBeNull();
    Storage::disk('public')->assertExists($notice->fileMedia->path);
    $this->get(route('public.notices.index'))->assertOk()->assertSee('Public holiday notice');
    $this->get(route('public.notices.show', $notice->slug))->assertOk()->assertSee('Office closed Monday.')->assertSee('Holiday notice');
});

test('notice routes and publishing require permissions', function (): void {
    $this->get(route('admin.notices.index'))->assertRedirect();
    $editor = User::factory()->create();
    $editor->givePermissionTo('notices.create');
    $this->actingAs($editor)->post(route('admin.notices.store'), ['title' => 'Draft notice', 'sort_order' => 0, 'status' => 'draft'])->assertRedirect();
    $this->actingAs($editor)->post(route('admin.notices.store'), ['title' => 'Published notice', 'sort_order' => 0, 'status' => 'published'])->assertSessionHasErrors('status');
    $this->assertDatabaseMissing('notices', ['slug' => 'published-notice']);
});
