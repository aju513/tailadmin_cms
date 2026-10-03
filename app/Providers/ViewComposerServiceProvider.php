<?php

namespace App\Providers;

use App\Http\ViewComposers\Front\LayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('front.layouts.app', LayoutComposer::class);
    }
}
