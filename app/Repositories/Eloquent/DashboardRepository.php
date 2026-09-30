<?php

namespace App\Repositories\Eloquent;

use App\Models\DashboardReport;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function cached(string $key): ?array
    {
        return DashboardReport::where('cache_key', $key)->where('expires_at', '>', now())->first()?->payload;
    }

    public function store(string $key, array $payload, int $seconds): void
    {
        DashboardReport::updateOrCreate(['cache_key' => $key], [
            'payload' => $payload,
            'expires_at' => now()->addSeconds($seconds),
        ]);
    }
}
