<?php

namespace App\Services\Frontend;

use App\Repositories\Contracts\PublicTrainingRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class PublicTrainingService
{
    public function __construct(private readonly PublicTrainingRepositoryInterface $trainings, private readonly SafeHtml $html) {}

    public function homepage(): array
    {
        $baseUrl = rtrim(config('services.tims.base_url'), '/');
        $result = ['enabled' => (bool) config('services.tims.enabled'), 'available' => false, 'items' => [], 'url' => $baseUrl.'/routines?status=all'];
        if (! $result['enabled']) {
            return $result;
        }

        $limit = max(1, min(100, (int) config('services.tims.homepage_limit')));
        $key = 'tims.ongoing.v2.'.sha1($baseUrl.'.'.$limit);
        if (is_array($cached = Cache::get($key))) {
            return $cached;
        }

        try {
            $records = $this->trainings->ongoing($limit);
            $result['items'] = collect($records)->map(fn ($record) => $this->card($record, $baseUrl))
                ->filter()->take($limit)->values()->all();
            if ($records !== [] && $result['items'] === []) {
                throw new UnexpectedValueException('No valid TIMS training records.');
            }
            $result['available'] = true;
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            // Keep upstream error bodies and connection details out of the public page.
        }

        Cache::put($key, $result, config($result['available'] ? 'services.tims.cache_seconds' : 'services.tims.failure_cache_seconds'));

        return $result;
    }

    private function card(mixed $record, string $baseUrl): ?array
    {
        if (! is_array($record) || ($record['status'] ?? null) !== 'ongoing'
            || ! is_string($record['name'] ?? null) || trim($record['name']) === ''
            || filter_var($record['routine_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return null;
        }

        $start = $this->date(data_get($record, 'schedule.start_date'));
        $end = $this->date(data_get($record, 'schedule.end_date'));
        $venue = array_filter([$this->text(data_get($record, 'venue.name')), $this->text(data_get($record, 'venue.building'))]);

        return [
            'name' => trim($record['name']),
            'url' => $baseUrl.'/trainings/'.(int) $record['routine_id'],
            'code' => $this->text($record['code'] ?? null),
            'description' => $this->html->clean($this->text($record['description'] ?? null)),
            'delivery_type' => $this->text($record['delivery_type'] ?? null),
            'department' => $this->text($record['department'] ?? null),
            'duration' => $this->text($record['module_duration'] ?? null),
            'dates' => implode(' – ', array_filter([$start, $end])),
            'dates_bs' => implode(' – ', array_filter([$this->text(data_get($record, 'schedule.start_date_bs')), $this->text(data_get($record, 'schedule.end_date_bs'))])),
            'venue' => implode(', ', array_unique($venue)),
            'capacity' => $this->number($record['capacity'] ?? null),
            'available_seats' => $this->number($record['available_seats'] ?? null),
        ];
    }

    private function text(mixed $value): ?string
    {
        return is_scalar($value) && ! is_bool($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    private function number(mixed $value): ?int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? null : $number;
    }

    private function date(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        if (! checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) {
            return null;
        }
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $date->format('d M, Y') : null;
    }
}
