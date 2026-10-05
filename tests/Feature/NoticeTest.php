<?php

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\MediaAsset;
use App\Models\Notice;
use App\Models\NoticeCategory;
use App\Models\Page;
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
        'notice_page_id' => Page::factory()->create(['page_type' => PageType::Notices])->id,
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

test('public notices show a deadline when one is set', function (): void {
    $deadline = now()->addDays(5)->setTime(17, 30);
    $notice = Notice::factory()->create([
        'title' => 'Tender deadline notice',
        'deadline_at' => $deadline,
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $formatted = $deadline->format('d M, Y H:i');

    $this->get(route('public.notices.index'))->assertOk()->assertSee('Deadline')->assertSee($formatted);
    $this->get(route('public.notices.show', $notice->slug))->assertOk()->assertSee('Deadline:')->assertSee($formatted);
});

test('notice routes and publishing require permissions', function (): void {
    $this->get(route('admin.notices.index'))->assertRedirect();
    $section = Page::factory()->create(['page_type' => PageType::Notices]);
    $editor = User::factory()->create();
    $editor->givePermissionTo('notices.create');
    $this->actingAs($editor)->post(route('admin.notices.store'), ['notice_page_id' => $section->id, 'title' => 'Draft notice', 'sort_order' => 0, 'status' => 'draft'])->assertRedirect();
    $this->actingAs($editor)->post(route('admin.notices.store'), ['notice_page_id' => $section->id, 'title' => 'Published notice', 'sort_order' => 0, 'status' => 'published'])->assertSessionHasErrors('status');
    $this->assertDatabaseMissing('notices', ['slug' => 'published-notice']);
});

test('public notice search paginates and retains filters on standalone and CMS lists', function (string $path, bool $cms, int $perPage, string $pageName): void {
    $category = NoticeCategory::factory()->create();
    if ($cms) {
        Page::factory()->create(['path' => $path, 'slug' => $path, 'page_type' => PageType::Notices, 'notice_category_id' => $category->id, 'status' => ContentStatus::Published]);
    }
    $matching = Notice::factory()->count($perPage + 1)->sequence(fn ($sequence) => ['title' => 'Training notice '.$sequence->index])->create([
        'notice_category_id' => $category->id, 'status' => ContentStatus::Published, 'published_at' => now()->subDay(),
    ]);
    Notice::factory()->create(['title' => 'Holiday announcement', 'notice_category_id' => $category->id, 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    Notice::factory()->create(['title' => 'Training in another category', 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    Notice::factory()->create(['title' => 'Training draft', 'notice_category_id' => $category->id, 'status' => ContentStatus::Draft]);
    Notice::factory()->create(['title' => 'Training scheduled', 'notice_category_id' => $category->id, 'status' => ContentStatus::Published, 'published_at' => now()->addDay()]);

    $query = ['q' => 'Training', 'notice_category_id' => $category->id];
    $response = $this->get('/'.$path.'?'.http_build_query($query))->assertOk()
        ->assertSee('Search notices')->assertSee('Clear search')
        ->assertDontSee('Holiday announcement')->assertDontSee('Training in another category')
        ->assertDontSee('Training draft')->assertDontSee('Training scheduled');
    $items = $response->viewData('items');
    expect($items->total())->toBe($perPage + 1)->and($items->count())->toBe($perPage);
    expect($items->url(2))->toContain('q=Training', 'notice_category_id='.$category->id, $pageName.'=2');

    $next = $this->get($items->url(2))->assertOk()->assertSee('notices-page__number">'.($perPage + 1), false);
    expect($next->viewData('items')->getCollection()->pluck('id')->all())->toBe([$matching->first()->id]);
    $next->assertSee('href="'.url('/'.$path).'?notice_category_id='.$category->id.'"', false);

    $this->get('/'.$path.'?'.http_build_query([...$query, 'q' => 'No matching title']))->assertOk()->assertSee('No notices match your search.');
    $legacy = $this->get('/'.$path.'?'.http_build_query(['search' => 'Training', 'notice_category_id' => $category->id]))->assertOk();
    expect($legacy->viewData('items')->total())->toBe($perPage + 1);
})->with([
    'standalone catalogue' => ['notices', false, 12, 'page'],
    'CMS catalogue' => ['notices', true, 15, 'notices_page'],
    'custom CMS path' => ['notice-board', true, 15, 'notices_page'],
]);

test('notice search rejects invalid public filters', function (array $filters): void {
    $this->get('/notices?'.http_build_query($filters))->assertNotFound();
})->with([
    'long search' => [['q' => str_repeat('a', 101)]],
    'array search' => [['q' => ['training']]],
    'invalid page' => [['page' => 0]],
    'invalid CMS page' => [['notices_page' => -1]],
]);

test('notice details render inline readers and a download button for supported attachments', function (string $extension, string $mime, bool $office): void {
    Storage::fake('public');
    $asset = MediaAsset::create(['disk' => 'public', 'path' => 'notices/attachment.'.$extension, 'original_name' => 'attachment.'.$extension, 'mime_type' => $mime, 'size' => 10]);
    $notice = Notice::factory()->create(['title' => 'Notice with attachment', 'description' => '<p><strong>Notice information</strong></p>', 'file_media_id' => $asset->id, 'status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    $response = $this->get(route('public.notices.show', $notice->slug))->assertOk()
        ->assertSee('lg:col-span-8')->assertSee('lg:col-span-4')
        ->assertSee('<strong>Notice information</strong>', false)
        ->assertSee('Open file in new tab')->assertSee('Download file')->assertDontSee('Download this notice')
        ->assertSee('href="'.url($asset->url()).'" download="'.$asset->original_name.'"', false);
    if (str_starts_with($mime, 'image/')) {
        $response->assertSee('<img src="'.url($asset->url()).'"', false)->assertDontSee('<iframe', false);
    } else {
        $source = $office ? 'https://view.officeapps.live.com/op/embed.aspx?src='.rawurlencode(url($asset->url())) : url($asset->url());
        $response->assertSee('<iframe', false)->assertSee('src="'.$source.'"', false)->assertSee('Notice with attachment file preview');
    }
})->with([
    'PDF' => ['pdf', 'application/pdf', false],
    'image' => ['png', 'image/png', false],
    'Word' => ['doc', 'application/msword', true],
    'modern Word' => ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', true],
    'Excel' => ['xls', 'application/vnd.ms-excel', true],
    'modern Excel' => ['xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', true],
]);

test('notice sidebar shows the five most recently added public notices excluding the current notice', function (): void {
    $category = NoticeCategory::factory()->create();
    $attributes = ['notice_category_id' => $category->id, 'status' => ContentStatus::Published, 'published_at' => now()->subDay()];
    $current = Notice::factory()->create([...$attributes, 'title' => 'Current notice']);
    $recent = Notice::factory()->count(6)->sequence(fn ($sequence) => ['title' => 'Recent notice '.$sequence->index, 'created_at' => now()->subHours(6 - $sequence->index)])->create($attributes);
    Notice::factory()->create([...$attributes, 'title' => 'Unpublished sidebar notice', 'status' => ContentStatus::Draft]);
    Notice::factory()->create([...$attributes, 'title' => 'Scheduled sidebar notice', 'published_at' => now()->addDay()]);
    $inactive = NoticeCategory::factory()->create(['is_active' => false]);
    Notice::factory()->create([...$attributes, 'title' => 'Inactive category notice', 'notice_category_id' => $inactive->id]);

    $response = $this->get(route('public.notices.show', $current->slug))->assertOk()
        ->assertSee('Recently added notices')->assertSee('File not available yet')
        ->assertDontSee('Recent notice 0')->assertDontSee('Unpublished sidebar notice')
        ->assertDontSee('Scheduled sidebar notice')->assertDontSee('Inactive category notice')
        ->assertDontSee('<iframe', false);
    expect($response->viewData('recentNotices')->pluck('id')->all())->toBe($recent->reverse()->take(5)->pluck('id')->values()->all());
    foreach ($response->viewData('recentNotices') as $notice) {
        $response->assertSee('href="'.route('public.notices.show', $notice->slug).'"', false);
    }
    $this->get(route('public.notices.show', 'missing-notice'))->assertNotFound();
    $this->get(route('public.notices.show', Notice::where('title', 'Scheduled sidebar notice')->firstOrFail()->slug))->assertNotFound();
});

test('notice details show an empty recent list when no other notices are public', function (): void {
    $notice = Notice::factory()->create(['status' => ContentStatus::Published, 'published_at' => now()->subDay()]);
    $this->get(route('public.notices.show', $notice->slug))->assertOk()->assertSee('No other notices are available.')->assertSee('File not available yet')->assertDontSee('Download file');
});
