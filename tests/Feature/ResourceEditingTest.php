<?php

use App\Models\ResourceCategory;
use App\Models\ResourceDocument;
use App\Models\User;
use App\Repositories\Contracts\ResourceDocumentRepositoryInterface;
use App\Repositories\Eloquent\ResourceDocumentRepository;
use App\Services\ResourceDocumentService;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->category = ResourceCategory::factory()->create(['is_active' => true]);
});

test('resource forms group URL publication date and status with a full width rich description', function (): void {
    $this->actingAs($this->admin)->get(route('admin.resources.create'))->assertOk()
        ->assertSeeInOrder(['name="title"', 'name="slug"', 'name="resource_category_id"', 'name="published_at"', 'name="status"', 'name="description"'], false)
        ->assertSee('class="js-rich-text-editor"', false)->assertDontSee('name="sort_order"', false)->assertDontSee('title="Publication"', false);
});

test('resources append automatically preserve order on edit and safely retain rich descriptions', function (): void {
    ResourceDocument::factory()->create(['sort_order' => 10, 'resource_category_id' => $this->category->id]);
    $this->actingAs($this->admin)->post(route('admin.resources.store'), [
        'title' => 'Rich document', 'resource_category_id' => $this->category->id, 'status' => 'published',
        'description' => '<p>Helpful <strong>guidance</strong>.</p><ul><li>First item</li></ul><script>alert(1)</script><a href="javascript:alert(2)">Unsafe link</a>',
        'attachment' => UploadedFile::fake()->create('guide.pdf', 1, 'application/pdf'), 'sort_order' => 999,
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.resources.index'));
    $record = ResourceDocument::where('slug', 'rich-document')->firstOrFail();
    expect($record->sort_order)->toBe(11)->and($record->description)->toContain('<strong>guidance</strong>')->not->toContain('<script>', 'javascript:');
    $this->get(route('public.resources.show', $record->slug))->assertOk()->assertSee('<strong>guidance</strong>', false)->assertSee('<li>First item</li>', false)->assertDontSee('<script>alert(1)</script>', false);
    $this->put(route('admin.resources.update', $record), ['title' => 'Edited rich document', 'resource_category_id' => $this->category->id, 'status' => 'published', 'description' => '<p><em>Updated copy</em></p>', 'sort_order' => 0])->assertSessionHasNoErrors();
    expect($record->refresh()->sort_order)->toBe(11)->and($record->description)->toBe('<p><em>Updated copy</em></p>');
    $this->get(route('admin.resources.edit', $record))->assertOk()->assertSee('js-rich-text-editor')->assertDontSee('name="sort_order"', false);
});

test('resource rich descriptions preserve the existing description limit and publication validation', function (): void {
    $this->actingAs($this->admin)->post(route('admin.resources.store'), ['title' => 'Document', 'resource_category_id' => $this->category->id, 'status' => 'draft', 'description' => str_repeat('a', 10001), 'published_at' => 'invalid', 'attachment' => UploadedFile::fake()->create('guide.pdf', 1, 'application/pdf')])->assertSessionHasErrors(['description', 'published_at']);
    $this->assertDatabaseCount('resource_documents', 0);
});

test('resource index hides move arrows and keeps ordering permission controlled', function (): void {
    $record = ResourceDocument::factory()->create(['title' => 'Moveable guide', 'resource_category_id' => $this->category->id]);
    $this->actingAs($this->admin)->get(route('admin.resources.index'))->assertOk()->assertDontSee('Move Moveable guide up')->assertDontSee('Move Moveable guide down');
    $this->get(route('admin.resources.index', ['search' => 'Moveable']))->assertOk()->assertSee('Clear the search to reorder resources.')->assertDontSee('Move Moveable guide up');
    $reader = User::factory()->create();
    $role = Role::create(['name' => 'resource-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('resources.manage');
    $reader->assignRole($role);
    $this->actingAs($reader)->get(route('admin.resources.index'))->assertOk()->assertDontSee('Move Moveable guide up')->assertDontSee('Move Moveable guide down');
    $this->postJson(route('admin.resources.order'), ['resources' => [$record->id], 'original_order' => [$record->id]])->assertForbidden();
    expect(fn () => app(ResourceDocumentService::class)->reorder([$record->id], [$record->id], $reader))->toThrow(Illuminate\Auth\Access\AuthorizationException::class);
});

test('moving resources on a paginated page preserves all other catalogue positions and refreshes public ordering', function (): void {
    $records = ResourceDocument::factory()->count(18)->sequence(fn (Sequence $sequence) => ['sort_order' => $sequence->index])->create(['resource_category_id' => $this->category->id]);
    $original = $records->modelKeys();
    $this->get(route('public.home'))->assertOk();
    $version = Cache::get('frontend.version');
    $firstPage = array_slice($original, 0, 15);
    $moved = $firstPage;
    [$moved[0], $moved[1]] = [$moved[1], $moved[0]];
    $this->actingAs($this->admin)->postJson(route('admin.resources.order'), ['resources' => $moved, 'original_order' => $firstPage])->assertOk()->assertJson(['message' => 'Resource order updated.']);
    expect(ResourceDocument::orderBy('sort_order')->pluck('id')->all())->toBe([...$moved, ...array_slice($original, 15)])
        ->and(Cache::get('frontend.version'))->not->toBe($version);
    $secondPage = array_slice($original, 15);
    $this->postJson(route('admin.resources.order'), ['resources' => array_reverse($secondPage), 'original_order' => $secondPage])->assertOk();
    expect(ResourceDocument::orderBy('sort_order')->pluck('id')->all())->toBe([...$moved, ...array_reverse($secondPage)]);
    $this->assertDatabaseHas('activity_log', ['event' => 'resources.reordered', 'causer_id' => $this->admin->id]);
});

test('ordering rejects stale mismatched and non contiguous rows without modifying positions', function (): void {
    $records = ResourceDocument::factory()->count(3)->sequence(fn (Sequence $sequence) => ['sort_order' => $sequence->index])->create(['resource_category_id' => $this->category->id]);
    [$first, $second, $third] = $records->modelKeys();
    $this->actingAs($this->admin)->postJson(route('admin.resources.order'), ['resources' => [$second, $first], 'original_order' => [$first, $third]])->assertUnprocessable()->assertJsonValidationErrors('resources');
    $this->postJson(route('admin.resources.order'), ['resources' => [$third, $first], 'original_order' => [$first, $third]])->assertUnprocessable()->assertJsonValidationErrors('resources');
    $this->postJson(route('admin.resources.order'), ['resources' => [$first, $second, $third], 'original_order' => [$second, $first, $third]])->assertUnprocessable()->assertJsonValidationErrors('resources');
    expect(ResourceDocument::orderBy('sort_order')->pluck('id')->all())->toBe([$first, $second, $third]);
    $this->assertDatabaseMissing('activity_log', ['event' => 'resources.reordered']);
});

test('resource reorder input validates missing original order unknown IDs and duplicate IDs', function (): void {
    $record = ResourceDocument::factory()->create(['resource_category_id' => $this->category->id]);
    $this->actingAs($this->admin)->postJson(route('admin.resources.order'), ['resources' => [$record->id]])->assertUnprocessable()->assertJsonValidationErrors('original_order');
    $this->postJson(route('admin.resources.order'), ['resources' => [$record->id, $record->id], 'original_order' => [$record->id]])->assertUnprocessable()->assertJsonValidationErrors('resources.0');
    $this->postJson(route('admin.resources.order'), ['resources' => [99999], 'original_order' => [$record->id]])->assertUnprocessable()->assertJsonValidationErrors('resources.0');
    $this->assertDatabaseMissing('activity_log', ['event' => 'resources.reordered']);
});

test('failed resource ordering restores every position and leaves no success audit or cache refresh', function (): void {
    $records = ResourceDocument::factory()->count(3)->sequence(fn (Sequence $sequence) => ['sort_order' => $sequence->index])->create(['resource_category_id' => $this->category->id]);
    $ids = $records->modelKeys();
    $version = Cache::get('frontend.version');
    $repository = Mockery::mock(ResourceDocumentRepository::class)->makePartial();
    $repository->shouldReceive('reorder')->once()->andReturnUsing(function ($ordered) {
        ResourceDocument::whereKey($ordered[0])->update(['sort_order' => 99]);
        throw new RuntimeException('Order write failed');
    });
    $this->app->instance(ResourceDocumentRepositoryInterface::class, $repository);
    expect(fn () => app(ResourceDocumentService::class)->reorder(array_reverse($ids), $ids, $this->admin))->toThrow(RuntimeException::class, 'Order write failed');
    expect(ResourceDocument::orderBy('sort_order')->pluck('id')->all())->toBe($ids)->and(Cache::get('frontend.version'))->toBe($version);
    $this->assertDatabaseMissing('activity_log', ['event' => 'resources.reordered']);
});
