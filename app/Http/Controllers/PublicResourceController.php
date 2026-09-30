<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceDocument\DownloadPublicResourceRequest;
use App\Http\Requests\ResourceDocument\ShowPublicResourceRequest;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Services\ResourceDocumentService;
use App\Services\SiteSettingService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicResourceController extends Controller
{
    public function __construct(private readonly ResourceDocumentService $resources, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings) {}

    public function show(ShowPublicResourceRequest $request, string $slug): View
    {
        $resource = $this->resources->publicDetails($slug);

        return view('public.resources.show', [
            'resource' => $resource, 'title' => $resource->title,
            'settings' => $this->settings->all(),
            'mainMenu' => $this->menus->forLocation('header'),
            'footerMenu' => $this->menus->forLocation('footer'),
        ]);
    }

    public function download(DownloadPublicResourceRequest $request, string $slug): StreamedResponse
    {
        return $this->resources->download($slug);
    }
}
