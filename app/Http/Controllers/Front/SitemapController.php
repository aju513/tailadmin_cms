<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\SitemapRequest;
use App\Services\Frontend\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapService $sitemaps) {}

    public function index(SitemapRequest $request): Response
    {
        return response($this->sitemaps->index(), 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function chunk(SitemapRequest $request, string $type, string $chunk): Response
    {
        return response($this->sitemaps->chunk($type, (int) $chunk), 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function robots(SitemapRequest $request): Response
    {
        return response($this->sitemaps->robots(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
