<?php

namespace App\Http\ViewComposers\Front;

use App\Services\Frontend\FrontendLayoutService;
use App\Services\Frontend\SeoService;
use Illuminate\View\View;

class LayoutComposer
{
    public function __construct(private readonly FrontendLayoutService $layout, private readonly SeoService $seo) {}

    public function compose(View $view): void
    {
        if (! $view->offsetExists('settings')) {
            $view->with($this->layout->data());
        }

        if (! $view->offsetExists('seo')) {
            $view->with('seo', $this->seo->make($view->getData()));
        }
    }
}
