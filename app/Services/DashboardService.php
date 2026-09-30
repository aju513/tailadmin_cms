<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Throwable;

class DashboardService
{
    public function __construct(private DashboardRepositoryInterface $reports, private GoogleReportingService $google) {}

    public function overview(int $days): array
    {
        $end = today()->subDay();
        $start = $end->copy()->subDays($days - 1);
        $result = ['days' => $days, 'start' => $start->toDateString(), 'end' => $end->toDateString()];
        foreach (['analytics', 'search'] as $provider) {
            if ($provider === 'search' && ! app()->environment('production')) {
                $result[$provider] = ['available' => false, 'data' => [], 'updated_at' => null];

                continue;
            }
            $identity = $provider === 'analytics' ? config('dashboard.property_id') : config('dashboard.site_url');
            $path = config('dashboard.'.$provider.'_credentials');
            $fingerprint = is_readable($path) ? hash_file('sha256', $path) : 'missing';
            $key = hash('sha256', implode('|', [$provider, $identity, $fingerprint, $result['start'], $result['end']]));
            $report = $this->reports->cached($key);
            if ($report === null) {
                try {
                    $report = ['available' => true, 'data' => $this->google->{$provider}($result['start'], $result['end']), 'updated_at' => now()->toIso8601String()];
                } catch (Throwable) {
                    // Google exceptions may contain credentials or tokens. Never log or expose them.
                    $report = ['available' => false, 'data' => [], 'updated_at' => null];
                }
                $this->reports->store($key, $report, $report['available'] ? 900 : 60);
            }
            $result[$provider] = $report;
        }

        return $result;
    }
}
