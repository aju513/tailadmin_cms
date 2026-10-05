<?php

use App\Enums\PageType;
use App\Models\GalleryAlbum;
use App\Models\Hall;
use App\Models\Notice;
use App\Models\Page;
use App\Models\ResourceCategory;
use App\Models\ResourceDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config(['settings.nepali' => false, 'cache.default' => 'array']);
    Storage::fake('public');
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
});

dataset('live slug modules', ['pages', 'notices', 'resources', 'resource-categories', 'gallery', 'halls']);

function liveSlugRecord(string $module): Model
{
    $attributes = ['title' => 'Existing title', 'slug' => 'existing-title'];

    return match ($module) {
        'pages' => Page::factory()->create([...$attributes, 'title' => ['en' => 'Existing title'], 'path' => 'existing-title']),
        'notices' => Notice::factory()->inSection(Page::factory()->create(['page_type' => PageType::Notices]))->create($attributes),
        'resources' => ResourceDocument::factory()->create($attributes),
        'resource-categories' => ResourceCategory::factory()->create(['name' => 'Existing title', 'slug' => 'existing-title']),
        'gallery' => GalleryAlbum::factory()->create($attributes),
        'halls' => Hall::factory()->create([...$attributes, 'title' => ['en' => 'Existing title']]),
    };
}

test('create and edit forms expose the appropriate URL controls with existing values', function (string $module): void {
    $record = liveSlugRecord($module);
    foreach ([route("admin.{$module}.create"), route("admin.{$module}.edit", $record)] as $url) {
        $response = $this->actingAs($this->admin)->get($url)->assertOk()->assertDontSee('@js(', false);
        if ($module === 'resource-categories') {
            $response->assertSee('name="name"', false)->assertDontSee('name="slug"', false)->assertDontSee('URL slug')->assertDontSee('slugEditor(', false);
        } else {
            $response->assertSee('x-bind:value="title"', false)->assertSee('x-bind:value="slug"', false)
                ->assertSee('@input="updateTitle($event.target.value)"', false)->assertSee('@input="updateSlug($event.target.value)"', false);
        }
        if ($url === route("admin.{$module}.edit", $record)) {
            $response->assertSee('value="Existing title"', false);
            if ($module !== 'resource-categories') {
                $response->assertSee('value="existing-title"', false);
            }
        }
    }
})->with('live slug modules');

test('invalid manual URL requests return validation feedback and restore form inputs', function (string $module): void {
    $payload = ['title' => 'Retry title', 'slug' => 'custom address!', 'status' => 'draft'];
    $payload += match ($module) {
        'notices' => ['notice_page_id' => Page::factory()->create(['page_type' => PageType::Notices])->id],
        'resources' => ['resource_category_id' => ResourceCategory::factory()->create()->id, 'attachment' => UploadedFile::fake()->create('guide.pdf', 1, 'application/pdf')],
        'resource-categories' => ['name' => 'Retry title', 'is_active' => true],
        'halls' => ['capacity' => 50, 'availability_status' => 'available', 'sort_order' => 0],
        default => [],
    };
    $this->actingAs($this->admin)->from(route("admin.{$module}.create"))->post(route("admin.{$module}.store"), $payload)
        ->assertRedirect(route("admin.{$module}.create"))->assertSessionHasErrors('slug');
    $response = $this->get(route("admin.{$module}.create"))->assertOk()->assertSee('value="Retry title"', false);
    if ($module === 'resource-categories') {
        $response->assertDontSee('name="slug"', false)->assertDontSee('value="custom address!"', false);
    } else {
        $response->assertSee('value="custom address!"', false)->assertSee('x-bind:value="slug"', false);
    }
})->with('live slug modules');

test('live URL fields remain protected by each module create and edit permissions', function (string $module): void {
    $record = liveSlugRecord($module);
    $this->actingAs(User::factory()->create())->get(route("admin.{$module}.create"))->assertForbidden();
    $this->get(route("admin.{$module}.edit", $record))->assertForbidden();
})->with('live slug modules');

test('bilingual forms bind URL generation only to the English title', function (string $module): void {
    config(['settings.nepali' => true]);
    $record = liveSlugRecord($module);
    $record->setTranslation('title', 'ne', 'नेपाली शीर्षक')->save();
    $response = $this->actingAs($this->admin)->get(route("admin.{$module}.edit", $record))->assertOk();
    preg_match('/<input\b[^>]*name="translations\[en\]\[title\]"[^>]*>/s', $response->getContent(), $english);
    preg_match('/<input\b[^>]*name="translations\[ne\]\[title\]"[^>]*>/s', $response->getContent(), $nepali);
    expect($english[0])->toContain('@input="updateTitle($event.target.value)"', 'x-bind:value="title"')
        ->and($nepali[0])->not->toContain('@input=', 'x-bind:value=');
})->with(['pages', 'halls']);

test('resource categories generate stored slugs without a form field and retain them after a rename', function (): void {
    $this->actingAs($this->admin)->post(route('admin.resource-categories.store'), ['name' => 'Training materials', 'is_active' => true])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.resource-categories.index'));
    $category = ResourceCategory::where('slug', 'training-materials')->firstOrFail();
    $this->put(route('admin.resource-categories.update', $category), ['name' => 'Renamed category', 'is_active' => true])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.resource-categories.edit', $category));
    expect($category->refresh()->slug)->toBe('training-materials');
    $response = $this->get(route('admin.resource-categories.index'))->assertOk()->assertSee('Renamed category');
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $cell = $xpath->query('//td[div[contains(@class, "font-medium")]]')->item(0);
    expect(trim($cell->textContent))->toBe('Renamed category');
});
