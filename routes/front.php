<?php

use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\ContentController;
use App\Http\Controllers\Front\GrievanceController;
use App\Http\Controllers\Front\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap.index');
Route::get('/sitemaps/{type}-{chunk}.xml', [SitemapController::class, 'chunk'])->where('chunk', '[0-9]+')->name('public.sitemap.chunk');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('public.robots');
Route::get('/', [ContentController::class, 'home'])->name('public.home');
Route::get('/search', [ContentController::class, 'search'])->name('public.search');
Route::get('/contact', [ContentController::class, 'contact'])->name('public.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,1')->name('public.contact.send');
Route::post('/grievances/{pageId}', [GrievanceController::class, 'store'])->whereNumber('pageId')->middleware('throttle:3,1')->name('public.grievances.store');
Route::get('/sitemap', [ContentController::class, 'sitemap'])->name('public.sitemap');
Route::get('/news', [ContentController::class, 'news'])->name('public.news.index');
foreach (['category', 'tag', 'author'] as $taxonomy) {
    Route::get('/news/'.$taxonomy.'/{slug}', [ContentController::class, $taxonomy])->name('public.news.'.$taxonomy);
}
Route::get('/resources/{slug}/download', [ContentController::class, 'download'])->name('public.resources.download');
foreach (['news', 'notices', 'resources', 'halls', 'gallery', 'videos', 'team'] as $type) {
    if ($type !== 'news') {
        Route::get('/'.$type, [ContentController::class, 'index'])->defaults('kind', $type)->name('public.'.$type.'.index');
    }
    $detailRoute = Route::get('/'.$type.'/{slug}', [ContentController::class, 'show'])->defaults('kind', $type)->name('public.'.$type.'.show');
    if ($type === 'team') {
        $detailRoute->where('slug', '[0-9]+');
    }
}

// Keep the CMS catch-all after every explicit admin and public route.
Route::get('/{path}', [ContentController::class, 'page'])->where('path', '.*')->name('public.page');
