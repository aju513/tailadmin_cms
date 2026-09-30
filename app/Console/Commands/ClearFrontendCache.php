<?php

namespace App\Console\Commands;

use App\Services\Frontend\FrontendCache;
use Illuminate\Console\Command;

class ClearFrontendCache extends Command
{
    protected $signature = 'frontend:cache-clear';

    protected $description = 'Invalidate public sitemap and frontend data caches';

    public function handle(FrontendCache $cache): int
    {
        $cache->clear();
        $this->info('Frontend cache cleared.');

        return self::SUCCESS;
    }
}
