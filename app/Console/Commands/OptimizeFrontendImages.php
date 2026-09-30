<?php

namespace App\Console\Commands;

use App\Services\Frontend\FrontendMaintenanceService;
use Illuminate\Console\Command;

class OptimizeFrontendImages extends Command
{
    protected $signature = 'frontend:images-optimize';

    protected $description = 'Generate responsive WebP derivatives for existing CMS images';

    public function handle(FrontendMaintenanceService $maintenance): int
    {
        $this->info('Images processed: '.$maintenance->optimizeImages());

        return self::SUCCESS;
    }
}
