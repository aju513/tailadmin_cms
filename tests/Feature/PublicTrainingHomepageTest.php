<?php

use App\Services\Frontend\FrontendCache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config(['services.tims.enabled' => true, 'services.tims.base_url' => 'https://tmis.pcgg.lumbini.gov.np']);
    Http::preventStrayRequests();
});

function homepageTrainingRecord(array $overrides = []): array
{
    return array_replace_recursive([
        'id' => 9, 'routine_id' => 42, 'name' => 'Public administration training', 'code' => 'PCGG-009',
        'description' => '<p>Practical leadership skills.</p>', 'module_duration' => '30 hours', 'status' => 'ongoing',
        'schedule' => ['start_date' => '2026-10-01', 'end_date' => '2026-10-10', 'start_date_bs' => '2083-06-15', 'end_date_bs' => '2083-06-24'],
        'venue' => ['name' => 'Main Hall', 'building' => 'Training Centre'],
        'department' => 'Administration', 'delivery_type' => 'In person', 'capacity' => 50,
        'approved_enrollments' => 12, 'available_seats' => 38,
    ], $overrides);
}

test('homepage renders ongoing TIMS training cards and routine detail links without authentication', function (): void {
    Http::fake(['https://tmis.pcgg.lumbini.gov.np/api/v1/trainings*' => Http::response(['status' => true, 'data' => [homepageTrainingRecord()]])]);

    $this->get(route('public.home'))->assertOk()
        ->assertSee('Ongoing Trainings')->assertSee('Public administration training')
        ->assertDontSee('PCGG-009')->assertSee('Practical leadership skills.')->assertSee('In person')
        ->assertSee('01 Oct, 2026')->assertSee('10 Oct, 2026')->assertSee('Main Hall, Training Centre')
        ->assertSee('Administration')->assertDontSee('Duration:')->assertDontSee('seats available')
        ->assertSee('href="https://tmis.pcgg.lumbini.gov.np/trainings/42"', false)
        ->assertSee('href="https://tmis.pcgg.lumbini.gov.np/routines?status=all"', false)
        ->assertDontSee('Upcoming')->assertDontSee('18 Nov, 2026');

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && $request->url() === 'https://tmis.pcgg.lumbini.gov.np/api/v1/trainings?status=ongoing&limit=6'
        && $request->hasHeader('Accept', 'application/json') && ! $request->hasHeader('Authorization'));
    Http::assertSentCount(1);
});

test('homepage limits cards and skips invalid and non ongoing API records', function (): void {
    $records = [null, ['routine_id' => 99], homepageTrainingRecord(['routine_id' => '../admin']), homepageTrainingRecord(['name' => []]), homepageTrainingRecord(['name' => 'Upcoming course', 'status' => 'upcoming'])];
    foreach (range(1, 7) as $id) {
        $records[] = homepageTrainingRecord(['routine_id' => $id, 'name' => 'Valid course '.$id]);
    }
    Http::fake(['*' => Http::response(['status' => true, 'data' => $records])]);

    $response = $this->get(route('public.home'))->assertOk()->assertSee('Valid course 6')->assertDontSee('Valid course 7')->assertDontSee('Upcoming course');
    expect($response->viewData('trainingCatalogue')['items'])->toHaveCount(6);
});

test('homepage escapes upstream training text and omits absent or invalid optional values', function (): void {
    $record = homepageTrainingRecord([
        'name' => '<script>alert("training")</script>', 'description' => '<img src=x onerror=alert(1)>Safe description',
        'venue' => null, 'department' => null, 'delivery_type' => null, 'module_duration' => null,
        'schedule' => ['start_date' => '2026-99-99', 'end_date' => ['invalid'], 'start_date_bs' => null, 'end_date_bs' => null],
        'code' => 'DS21MVL4E', 'capacity' => 30, 'available_seats' => 0,
    ]);
    Http::fake(['*' => Http::response(['status' => true, 'data' => [$record]])]);

    $this->get(route('public.home'))->assertOk()->assertSee($record['name'])
        ->assertSee('Safe description')->assertDontSee('DS21MVL4E')->assertDontSee('0 seats available out of 30')
        ->assertDontSee('<script>alert("training")</script>', false)->assertDontSee('onerror=alert(1)', false)
        ->assertDontSee('2026-99-99')->assertDontSee('Duration:');
});

test('training descriptions render sanitized rich text without breaking or truncating markup', function (): void {
    $paragraph = str_repeat('Detailed training information. ', 8).'तालिम विवरण &amp; guidance.';
    $description = '<p onclick="alert(1)">Learn <strong>leadership</strong> and <em>governance</em>.</p>'
        .'<ul><li>Practical exercises</li><li>Group discussions</li></ul>'
        .'<p>'.$paragraph.'</p><a href="https://example.com/course" style="color:red">Course information</a>'
        .'<script>alert("unsafe-description")</script><a href="javascript:alert(2)">Unsafe link</a>';
    Http::fake(['*' => Http::response(['status' => true, 'data' => [homepageTrainingRecord(['description' => $description])]])]);

    $this->get(route('public.home'))->assertOk()
        ->assertSee('<strong>leadership</strong>', false)->assertSee('<em>governance</em>', false)
        ->assertSee('<ul><li>Practical exercises</li><li>Group discussions</li></ul>', false)
        ->assertSee($paragraph, false)->assertSee('href="https://example.com/course"', false)
        ->assertDontSee('&lt;strong&gt;', false)->assertDontSee('onclick="alert(1)"', false)
        ->assertDontSee('unsafe-description')->assertDontSee('javascript:alert(2)', false)->assertDontSee('style="color:red"', false);
});

test('Nepali homepage uses the BS training dates supplied by TIMS', function (): void {
    config(['settings.nepali' => true, 'frontend.translation.mode' => 'manual']);
    Http::fake(['*' => Http::response(['status' => true, 'data' => [homepageTrainingRecord()]])]);

    $this->get(route('public.home', ['lang' => 'ne']))->assertOk()->assertSee('2083-06-15')->assertSee('2083-06-24 BS')->assertDontSee('01 Oct, 2026');
});

test('valid empty TIMS responses show an empty state and are cached', function (): void {
    Http::fake(['*' => Http::response(['status' => true, 'data' => []])]);

    $this->get(route('public.home'))->assertOk()->assertSee('No ongoing trainings are available right now.')->assertDontSee('training-list__item', false);
    $this->get(route('public.home'))->assertOk()->assertSee('No ongoing trainings are available right now.');
    Http::assertSentCount(1);
});

test('upstream errors leave the homepage usable without exposing remote error bodies', function (mixed $body, int $status): void {
    Http::fake(['*' => Http::response($body, $status)]);

    $this->get(route('public.home'))->assertOk()->assertSee('Training information is temporarily unavailable.')
        ->assertDontSee('PRIVATE-UPSTREAM-ERROR')->assertDontSee('training-list__item', false);
})->with([
    'not deployed' => [['message' => 'PRIVATE-UPSTREAM-ERROR'], 404],
    'server error' => [['message' => 'PRIVATE-UPSTREAM-ERROR'], 500],
    'rate limited' => [['message' => 'PRIVATE-UPSTREAM-ERROR'], 429],
    'invalid JSON' => ['PRIVATE-UPSTREAM-ERROR', 200],
    'API rejected' => [['status' => false, 'data' => []], 200],
    'missing data' => [['status' => true], 200],
    'invalid data' => [['status' => true, 'data' => 'PRIVATE-UPSTREAM-ERROR'], 200],
    'all invalid records' => [['status' => true, 'data' => [null, ['name' => 'Broken']]], 200],
]);

test('connection failures show the unavailable state', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    $this->get(route('public.home'))->assertOk()->assertSee('Training information is temporarily unavailable.');
});

test('training failures retry after one minute and successful data caches for five minutes independently of the CMS cache', function (): void {
    Http::fake(['*' => Http::sequence()->push(['message' => 'Unavailable'], 503)
        ->push(['status' => true, 'data' => [homepageTrainingRecord()]])
        ->push(['status' => true, 'data' => [homepageTrainingRecord(['name' => 'Updated training'])]])]);

    $this->get(route('public.home'))->assertOk()->assertSee('Training information is temporarily unavailable.');
    $this->get(route('public.home'))->assertOk();
    Http::assertSentCount(1);
    $this->travel(61)->seconds();
    $this->get(route('public.home'))->assertOk()->assertSee('Public administration training');
    app(FrontendCache::class)->clear();
    $this->get(route('public.home'))->assertOk()->assertSee('Public administration training');
    Http::assertSentCount(2);
    $this->travel(301)->seconds();
    $this->get(route('public.home'))->assertOk()->assertSee('Updated training');
    Http::assertSentCount(3);
});

test('configured TIMS origins apply to requests and all training links', function (): void {
    config(['services.tims.base_url' => 'https://training.example.test/']);
    Http::fake(['https://training.example.test/api/v1/trainings*' => Http::response(['status' => true, 'data' => [homepageTrainingRecord()]])]);

    $this->get(route('public.home'))->assertOk()->assertSee('href="https://training.example.test/trainings/42"', false)
        ->assertSee('href="https://training.example.test/routines?status=all"', false);
    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://training.example.test/api/v1/trainings?'));
});

test('disabled training integration makes no external request and hides the section', function (): void {
    config(['services.tims.enabled' => false]);
    $this->get(route('public.home'))->assertOk()->assertDontSee('Ongoing Trainings');
    Http::assertNothingSent();
});
