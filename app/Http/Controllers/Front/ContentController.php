<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\PublicContentRequest;
use App\Http\Requests\ResourceDocument\DownloadPublicResourceRequest;
use App\Services\Frontend\FrontendService;
use App\Services\ResourceDocumentService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentController extends Controller
{
    public function __construct(private readonly FrontendService $front, private readonly ResourceDocumentService $resources) {}

    public function home(PublicContentRequest $request): View
    {
        return view('front.pages.home', $this->front->home($request->validated()));
    }

    public function page(PublicContentRequest $request, string $path): View
    {
        return view('front.pages.page', $this->front->page($path, $request->validated()));
    }

    public function index(PublicContentRequest $request): View
    {
        return view('front.pages.page', $this->front->listing($request->route()->defaults['kind'], $request->validated()));
    }

    public function news(PublicContentRequest $request): View
    {
        return view('front.pages.page', $this->front->newsListing($request->validated()));
    }

    public function category(PublicContentRequest $request, string $slug): View
    {
        return view('front.pages.page', $this->front->newsListing($request->validated(), 'category', $slug));
    }

    public function tag(PublicContentRequest $request, string $slug): View
    {
        return view('front.pages.page', $this->front->newsListing($request->validated(), 'tag', $slug));
    }

    public function author(PublicContentRequest $request, string $slug): View
    {
        return view('front.pages.page', $this->front->newsListing($request->validated(), 'author', $slug));
    }

    public function show(PublicContentRequest $request, string $slug): View
    {
        $data = $this->front->detail($request->route()->defaults['kind'], $slug, $request->validated());

        return view($data['view'] ?? 'front.pages.detail', $data);
    }

    public function search(PublicContentRequest $request): View
    {
        return view('front.pages.search', $this->front->search($request->validated()));
    }

    public function contact(PublicContentRequest $request): View
    {
        return view('front.pages.page', $this->front->contact($request->validated()));
    }

    public function sitemap(PublicContentRequest $request): View
    {
        return view('front.pages.page', $this->front->sitemap($request->validated()));
    }

    public function download(DownloadPublicResourceRequest $request, string $slug): StreamedResponse
    {
        return $this->resources->download($slug);
    }
}
