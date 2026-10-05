<?php

use App\Models\CapacityReport;
use App\Models\SiteSetting;
use App\Models\User;
use App\Repositories\Contracts\CapacityReportRepositoryInterface;
use App\Repositories\Eloquent\CapacityReportRepository;
use App\Services\CapacityReportService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
    $this->artisan('admin:permissions-sync')->assertSuccessful();
    $this->admin = User::findOrFail(1);
    $this->input = ['fiscal_year' => '2082/83', 'development' => [['key' => 'Total training programs', 'value' => 1200], ['key' => 'Total participants', 'value' => 0]], 'collaboration' => [['key' => 'Research studies', 'value' => null]]];
});

test('capacity reports provide a fiscal-year editor and generated admin navigation', function (): void {
    $this->artisan('admin:menu-regenerate')->assertSuccessful();
    $this->actingAs($this->admin)->get(route('admin.capacity-reports.index'))->assertOk()->assertSee('No capacity reports yet.')->assertSee('Add Report');
    $this->get(route('admin.capacity-reports.create'))->assertOk()->assertSee('Add Capacity Report')
        ->assertSee('Save report')->assertSee('Close')->assertSee('data-sticky-form-actions="capacity-report-form"', false)
        ->assertSee('2082/83')->assertSee('Capacity Development Contribution')->assertSee('Contribution Through Collaboration')
        ->assertSee('Add key/value')->assertSee('Total training programs');
    $this->get(route('admin.settings.edit'))->assertOk()->assertSee(route('admin.capacity-reports.index'), false)->assertDontSee('name="capacity_reports"', false);
    $this->assertDatabaseCount('capacity_reports', 0);
});

test('saving a fiscal-year report publishes key value pairs and refreshes cached homepage figures', function (): void {
    $this->get(route('public.home'))->assertOk()->assertDontSee('data-report-year=', false);
    $this->actingAs($this->admin)->post(route('admin.capacity-reports.store'), [...$this->input, 'created_by' => 999, 'updated_by' => 999])->assertRedirect(route('admin.capacity-reports.index'))->assertSessionHasNoErrors();
    $record = CapacityReport::firstOrFail();
    expect($record->development)->toBe($this->input['development'])->and($record->collaboration)->toBe($this->input['collaboration'])
        ->and($record->created_by)->toBe($this->admin->id)->and($record->updated_by)->toBe($this->admin->id);
    $this->get(route('public.home'))->assertOk()->assertSee('2082/83')->assertSee('<strong>1,200</strong>', false)->assertSee('<strong>0</strong>', false)->assertSee('<strong>-</strong>', false);
    $this->assertDatabaseHas('activity_log', ['event' => 'capacity-report.created', 'causer_id' => $this->admin->id]);
});

test('editing supports renamed reordered added and removed keys and preserves the creator', function (): void {
    $report = CapacityReport::factory()->create(['fiscal_year' => '2082/83', 'created_by' => $this->admin->id]);
    $editor = User::factory()->create();
    $role = Role::create(['name' => 'report-editor', 'guard_name' => 'web']);
    $role->givePermissionTo(['capacity-reports.manage', 'capacity-reports.edit']);
    $editor->assignRole($role);
    $this->get(route('public.home'))->assertOk();
    $input = ['fiscal_year' => '2081/82', 'development' => [['key' => ' New custom metric ', 'value' => '32'], ['key' => 'Renamed metric', 'value' => null]], 'collaboration' => [['key' => 'Cooperation events', 'value' => 14]]];
    $this->actingAs($editor)->get(route('admin.capacity-reports.edit', $report))->assertOk()->assertSee('2082/83');
    $this->put(route('admin.capacity-reports.update', $report), $input)->assertRedirect(route('admin.capacity-reports.index'))->assertSessionHasNoErrors();
    expect($report->refresh()->fiscal_year)->toBe('2081/82')->and($report->development)->toBe([['key' => 'New custom metric', 'value' => 32], ['key' => 'Renamed metric', 'value' => null]])
        ->and($report->created_by)->toBe($this->admin->id)->and($report->updated_by)->toBe($editor->id);
    $this->get(route('public.home'))->assertOk()->assertSee('New custom metric')->assertSee('Cooperation events')->assertDontSee('2082/83');
    $this->assertDatabaseCount('capacity_reports', 1);
    $this->assertDatabaseHas('activity_log', ['event' => 'capacity-report.updated', 'causer_id' => $editor->id]);
});

test('report authorization protects reads writes and direct service workflows', function (): void {
    $report = CapacityReport::factory()->create();
    $this->get(route('admin.capacity-reports.index'))->assertRedirect(route('login'));
    $this->post(route('admin.capacity-reports.store'), $this->input)->assertRedirect(route('login'));
    $ordinary = User::factory()->create();
    $this->actingAs($ordinary)->get(route('admin.capacity-reports.index'))->assertForbidden();
    $this->get(route('admin.capacity-reports.create'))->assertForbidden();
    $this->get(route('admin.capacity-reports.edit', $report))->assertForbidden();
    $this->post(route('admin.capacity-reports.store'), $this->input)->assertForbidden();
    $this->put(route('admin.capacity-reports.update', $report), $this->input)->assertForbidden();
    $this->delete(route('admin.capacity-reports.destroy', $report))->assertForbidden();
    expect(fn () => app(CapacityReportService::class)->save($this->input, $ordinary))->toThrow(Illuminate\Auth\Access\AuthorizationException::class);
    expect(fn () => app(CapacityReportService::class)->delete($report, $ordinary))->toThrow(Illuminate\Auth\Access\AuthorizationException::class);
    $role = Role::create(['name' => 'report-reader', 'guard_name' => 'web']);
    $role->givePermissionTo('capacity-reports.manage');
    $ordinary->assignRole($role);
    $this->get(route('admin.capacity-reports.index'))->assertOk()->assertDontSee('Add Report')->assertDontSee('Delete report for');
    $this->get(route('admin.capacity-reports.edit', $report))->assertForbidden();
    $this->post(route('admin.capacity-reports.store'), $this->input)->assertForbidden();
    $this->assertDatabaseCount('capacity_reports', 1);
});

test('fiscal years are unique on create update and at the database boundary', function (): void {
    $existing = CapacityReport::factory()->create(['fiscal_year' => '2082/83']);
    $other = CapacityReport::factory()->create(['fiscal_year' => '2081/82']);
    $this->actingAs($this->admin)->post(route('admin.capacity-reports.store'), $this->input)->assertSessionHasErrors('fiscal_year');
    $this->put(route('admin.capacity-reports.update', $other), $this->input)->assertSessionHasErrors('fiscal_year');
    $this->put(route('admin.capacity-reports.update', $existing), $this->input)->assertSessionHasNoErrors();
    expect(fn () => CapacityReport::factory()->create(['fiscal_year' => '2082/83']))->toThrow(UniqueConstraintViolationException::class);
    $this->assertDatabaseCount('capacity_reports', 2);
});

test('capacity reports validate years keys and numerical values before writing', function (string $path, mixed $value, string $field): void {
    $input = $this->input;
    data_set($input, $path, $value);
    $this->actingAs($this->admin)->from(route('admin.capacity-reports.create'))->post(route('admin.capacity-reports.store'), $input)->assertRedirect(route('admin.capacity-reports.create'))->assertSessionHasErrors($field);
    $this->assertDatabaseCount('capacity_reports', 0);
    $this->assertDatabaseMissing('activity_log', ['event' => 'capacity-report.created']);
})->with([
    'missing year' => ['fiscal_year', '', 'fiscal_year'],
    'unconfigured year' => ['fiscal_year', '2099/00', 'fiscal_year'],
    'empty key' => ['development.0.key', '   ', 'development.0.key'],
    'overlong key' => ['development.0.key', str_repeat('a', 161), 'development.0.key'],
    'duplicate key' => ['development.1.key', ' total TRAINING programs ', 'development.1.key'],
    'negative number' => ['development.0.value', -1, 'development.0.value'],
    'fractional number' => ['collaboration.0.value', 2.5, 'collaboration.0.value'],
    'non numerical' => ['collaboration.0.value', 'many', 'collaboration.0.value'],
    'over maximum' => ['development.0.value', 1000000001, 'development.0.value'],
    'unknown row field' => ['development.0.extra', 'untrusted', 'development.0'],
    'empty group' => ['collaboration', [], 'collaboration'],
    'malformed group' => ['development', 'invalid', 'development'],
    'malformed row' => ['development.0', 'invalid', 'development.0'],
    'non list group' => ['development', ['invalid' => ['key' => 'Label', 'value' => 1]], 'development'],
]);

test('row limits and fiscal-year choices follow settings configuration', function (): void {
    config(['settings.fiscal_years' => ['2090/91'], 'settings.capacity_reports.max_rows' => 1, 'settings.capacity_reports.max_value' => 10]);
    $this->actingAs($this->admin)->get(route('admin.capacity-reports.create'))->assertOk()->assertSee('2090/91')->assertDontSee('2082/83');
    $input = [...$this->input, 'fiscal_year' => '2090/91'];
    $this->post(route('admin.capacity-reports.store'), $input)->assertSessionHasErrors('development');
    $input['development'] = [['key' => 'Allowed', 'value' => 11]];
    $this->post(route('admin.capacity-reports.store'), $input)->assertSessionHasErrors('development.0.value');
    $input['development'][0]['value'] = 10;
    $this->post(route('admin.capacity-reports.store'), $input)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('capacity_reports', ['fiscal_year' => '2090/91']);
});

test('the homepage defaults to the latest saved fiscal year and escapes editable metric labels', function (): void {
    CapacityReport::factory()->create(['fiscal_year' => '2080/81']);
    CapacityReport::factory()->create(['fiscal_year' => '2082/83', 'development' => [['key' => '<script>alert(1)</script>', 'value' => 0]]]);
    $response = $this->get(route('public.home'))->assertOk()->assertSeeInOrder(['data-report-year="0">2082/83', 'data-report-year="1">2080/81'], false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('aria-pressed="false"', false);
    expect($response->getContent())->toContain('data-report-panel="1"  hidden');
});

test('deleting a report logs the action and removes its cached public year', function (): void {
    $report = CapacityReport::factory()->create(['fiscal_year' => '2082/83']);
    $this->get(route('public.home'))->assertOk()->assertSee('2082/83');
    $this->actingAs($this->admin)->delete(route('admin.capacity-reports.destroy', $report))->assertRedirect(route('admin.capacity-reports.index'));
    $this->assertDatabaseMissing('capacity_reports', ['id' => $report->id]);
    $this->assertDatabaseHas('activity_log', ['event' => 'capacity-report.deleted']);
    $this->get(route('public.home'))->assertOk()->assertDontSee('data-report-year=', false)->assertSee('Total training programs')->assertSee('<strong>-</strong>', false);
    $this->delete(route('admin.capacity-reports.destroy', $report))->assertNotFound();
});

test('capacity reports filter by fiscal year and retain unconfigured saved years for editing', function (): void {
    $legacy = CapacityReport::factory()->create(['fiscal_year' => '2070/71']);
    $other = CapacityReport::factory()->create(['fiscal_year' => '2082/83']);
    $this->actingAs($this->admin)->get(route('admin.capacity-reports.index', ['fiscal_year' => '2070/71']))->assertOk()->assertSee(route('admin.capacity-reports.edit', $legacy), false)->assertDontSee(route('admin.capacity-reports.edit', $other), false);
    $this->get(route('admin.capacity-reports.edit', $legacy))->assertOk()->assertSee('value="2070/71" selected', false);
    $this->put(route('admin.capacity-reports.update', $legacy), [...$this->input, 'fiscal_year' => '2070/71'])->assertSessionHasNoErrors();
    $this->post(route('admin.capacity-reports.store'), [...$this->input, 'fiscal_year' => '2071/72'])->assertSessionHasErrors('fiscal_year');
    $this->get(route('admin.capacity-reports.edit', 999))->assertNotFound();
});

test('failed report updates roll back all figures and do not log success or invalidate the cache', function (): void {
    $report = CapacityReport::factory()->create(['fiscal_year' => '2082/83']);
    $previous = $report->development;
    $this->get(route('public.home'))->assertOk();
    $version = Cache::get('frontend.version');
    $repository = Mockery::mock(CapacityReportRepository::class)->makePartial();
    $repository->shouldReceive('update')->once()->andReturnUsing(function ($record, $data) {
        $record->update($data);
        throw new RuntimeException('Persistence failed');
    });
    $this->app->instance(CapacityReportRepositoryInterface::class, $repository);
    expect(fn () => app(CapacityReportService::class)->save($this->input, $this->admin, $report))->toThrow(RuntimeException::class, 'Persistence failed');
    expect($report->refresh()->development)->toBe($previous)->and(Cache::get('frontend.version'))->toBe($version);
    $this->assertDatabaseMissing('activity_log', ['event' => 'capacity-report.updated']);
});

test('concurrent fiscal-year duplicates return a validation error without a success audit', function (): void {
    $repository = Mockery::mock(CapacityReportRepository::class)->makePartial();
    $repository->shouldReceive('create')->once()->andThrow(new UniqueConstraintViolationException('sqlite', 'insert into capacity_reports', [], new PDOException('UNIQUE constraint failed: capacity_reports.fiscal_year')));
    $this->app->instance(CapacityReportRepositoryInterface::class, $repository);
    $this->actingAs($this->admin)->post(route('admin.capacity-reports.store'), $this->input)->assertSessionHasErrors('fiscal_year');
    $this->assertDatabaseCount('capacity_reports', 0);
    $this->assertDatabaseMissing('activity_log', ['event' => 'capacity-report.created']);
});

test('the migration imports legacy reporting years without changing archived settings', function (): void {
    $saved = json_encode([['year' => '2070/71', 'development' => ['training_programs' => 12, 'participants' => 0], 'collaboration' => ['research' => 4]], ['year' => '2081/82', 'development' => ['materials' => null], 'collaboration' => []]]);
    SiteSetting::create(['key' => 'capacity_reports', 'value' => $saved, 'type' => 'json']);
    $migration = require database_path('migrations/2026_10_04_140000_create_capacity_reports_table.php');
    $migration->down();
    $migration->up();
    $first = CapacityReport::where('fiscal_year', '2070/71')->firstOrFail();
    expect($first->development[0])->toBe(['key' => 'Total training programs', 'value' => 12])->and($first->development[1]['value'])->toBe(0)
        ->and($first->collaboration[6])->toBe(['key' => 'Research studies', 'value' => 4])->and($first->development[7]['value'])->toBeNull();
    expect(SiteSetting::where('key', 'capacity_reports')->value('value'))->toBe($saved);
    $this->assertDatabaseCount('capacity_reports', 2);
});
