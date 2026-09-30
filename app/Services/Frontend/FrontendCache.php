<?php

namespace App\Services\Frontend;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FrontendCache
{
    public function remember(string $key, Closure $callback): mixed
    {
        $version = Cache::get('frontend.version', 'initial');

        return Cache::remember('frontend.'.$version.'.'.sha1(config('app.url').'.'.app()->getLocale().'.'.$key), config('frontend.cache_seconds'), $callback);
    }

    public function clear(): void
    {
        Cache::forever('frontend.version', Str::uuid()->toString());
    }
}
