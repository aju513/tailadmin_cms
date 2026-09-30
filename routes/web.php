<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\HomepageSlideController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamCategoryController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\UiKitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicNoticeController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/resources/{slug}/download', [\App\Http\Controllers\PublicResourceController::class, 'download'])->name('public.resources.download');
Route::get('/resources/{slug}', [\App\Http\Controllers\PublicResourceController::class, 'show'])->name('public.resources.show');

Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('/news', [PublicNewsController::class, 'index'])->name('public.news.index');
Route::get('/news/category/{slug}', [PublicNewsController::class, 'category'])->name('public.news.category');
Route::get('/news/tag/{slug}', [PublicNewsController::class, 'tag'])->name('public.news.tag');
Route::get('/news/author/{slug}', [PublicNewsController::class, 'author'])->name('public.news.author');
Route::get('/news/{slug}', [PublicNewsController::class, 'show'])->name('public.news.show');
Route::get('/notices', [PublicNoticeController::class, 'index'])->name('public.notices.index');
Route::get('/notices/{slug}', [PublicNoticeController::class, 'show'])->name('public.notices.show');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active'])->group(function (): void {
    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password/change', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');

    Route::get('/halls', [HallController::class, 'index'])->middleware('can:halls.manage')->name('halls.index');
    Route::get('/halls/create', [HallController::class, 'create'])->middleware('can:halls.create')->name('halls.create');
    Route::post('/halls', [HallController::class, 'store'])->middleware('can:halls.create')->name('halls.store');
    Route::get('/halls/{hall}/edit', [HallController::class, 'edit'])->middleware('can:halls.edit')->name('halls.edit');
    Route::put('/halls/{hall}', [HallController::class, 'update'])->middleware('can:halls.edit')->name('halls.update');
    Route::delete('/halls/{hall}', [HallController::class, 'destroy'])->middleware('can:halls.delete')->name('halls.destroy');
    Route::get('/halls/{hall}', [HallController::class, 'show'])->middleware('can:halls.show')->name('halls.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/pages', [PageController::class, 'index'])->middleware('can:pages.manage')->name('pages.index');
    Route::get('/pages/create', [PageController::class, 'create'])->middleware('can:pages.create')->name('pages.create');
    Route::post('/pages', [PageController::class, 'store'])->middleware('can:pages.create')->name('pages.store');
    Route::post('/pages/order', [PageController::class, 'order'])->middleware('can:pages.edit')->name('pages.order');
    Route::patch('/pages/bulk-status', [PageController::class, 'bulkStatus'])->middleware('can:pages.publish')->name('pages.bulk-status');
    Route::delete('/pages/bulk', [PageController::class, 'bulkDestroy'])->middleware('can:pages.delete')->name('pages.bulk-destroy');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->middleware('can:pages.edit')->name('pages.edit');
    Route::put('/pages/{page}', [PageController::class, 'update'])->middleware('can:pages.edit')->name('pages.update');
    Route::post('/pages/{page}/publish', [PageController::class, 'publish'])->middleware('can:pages.publish')->name('pages.publish');
    Route::post('/pages/{page}/unpublish', [PageController::class, 'unpublish'])->middleware('can:pages.publish')->name('pages.unpublish');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->middleware('can:pages.delete')->name('pages.destroy');
    Route::get('/pages/{page}', [PageController::class, 'show'])->middleware('can:pages.show')->name('pages.show');

    Route::get('/news', [NewsController::class, 'index'])->middleware('can:news.manage')->name('news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->middleware('can:news.create')->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->middleware('can:news.create')->name('news.store');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->middleware('can:news.edit')->name('news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->middleware('can:news.edit')->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->middleware('can:news.delete')->name('news.destroy');
    Route::get('/news/{news}', [NewsController::class, 'show'])->middleware('can:news.show')->name('news.show');
    Route::get('/notices', [NoticeController::class, 'index'])->middleware('can:notices.manage')->name('notices.index');
    Route::get('/notices/create', [NoticeController::class, 'create'])->middleware('can:notices.create')->name('notices.create');
    Route::post('/notices', [NoticeController::class, 'store'])->middleware('can:notices.create')->name('notices.store');
    Route::get('/notices/{notice}/edit', [NoticeController::class, 'edit'])->middleware('can:notices.edit')->name('notices.edit');
    Route::put('/notices/{notice}', [NoticeController::class, 'update'])->middleware('can:notices.edit')->name('notices.update');
    Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->middleware('can:notices.delete')->name('notices.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->middleware('can:categories.manage')->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->middleware('can:categories.create')->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('can:categories.create')->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('can:categories.edit')->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('can:categories.edit')->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:categories.delete')->name('categories.destroy');
    Route::get('/tags', [TagController::class, 'index'])->middleware('can:tags.manage')->name('tags.index');
    Route::get('/tags/create', [TagController::class, 'create'])->middleware('can:tags.create')->name('tags.create');
    Route::post('/tags', [TagController::class, 'store'])->middleware('can:tags.create')->name('tags.store');
    Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])->middleware('can:tags.edit')->name('tags.edit');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->middleware('can:tags.edit')->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->middleware('can:tags.delete')->name('tags.destroy');
    Route::get('/authors', [AuthorController::class, 'index'])->middleware('can:authors.manage')->name('authors.index');
    Route::get('/authors/create', [AuthorController::class, 'create'])->middleware('can:authors.create')->name('authors.create');
    Route::post('/authors', [AuthorController::class, 'store'])->middleware('can:authors.create')->name('authors.store');
    Route::get('/authors/{author}/edit', [AuthorController::class, 'edit'])->middleware('can:authors.edit')->name('authors.edit');
    Route::put('/authors/{author}', [AuthorController::class, 'update'])->middleware('can:authors.edit')->name('authors.update');
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy'])->middleware('can:authors.delete')->name('authors.destroy');

    Route::get('/team-members', [TeamMemberController::class, 'index'])->middleware('can:team-members.manage')->name('team-members.index');
    Route::get('/team-members/create', [TeamMemberController::class, 'create'])->middleware('can:team-members.create')->name('team-members.create');
    Route::post('/team-members', [TeamMemberController::class, 'store'])->middleware('can:team-members.create')->name('team-members.store');
    Route::get('/team-members/{teamMember}/edit', [TeamMemberController::class, 'edit'])->middleware('can:team-members.edit')->name('team-members.edit');
    Route::put('/team-members/{teamMember}', [TeamMemberController::class, 'update'])->middleware('can:team-members.edit')->name('team-members.update');
    Route::delete('/team-members/{teamMember}', [TeamMemberController::class, 'destroy'])->middleware('can:team-members.delete')->name('team-members.destroy');
    Route::get('/team-categories', [TeamCategoryController::class, 'index'])->middleware('can:team-categories.manage')->name('team-categories.index');
    Route::get('/team-categories/create', [TeamCategoryController::class, 'create'])->middleware('can:team-categories.create')->name('team-categories.create');
    Route::post('/team-categories', [TeamCategoryController::class, 'store'])->middleware('can:team-categories.create')->name('team-categories.store');
    Route::get('/team-categories/{teamCategory}/edit', [TeamCategoryController::class, 'edit'])->middleware('can:team-categories.edit')->name('team-categories.edit');
    Route::put('/team-categories/{teamCategory}', [TeamCategoryController::class, 'update'])->middleware('can:team-categories.edit')->name('team-categories.update');
    Route::delete('/team-categories/{teamCategory}', [TeamCategoryController::class, 'destroy'])->middleware('can:team-categories.delete')->name('team-categories.destroy');

    Route::get('/gallery', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'index'])->middleware('can:gallery.manage')->name('gallery.index');
    Route::get('/gallery/create', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'create'])->middleware('can:gallery.create')->name('gallery.create');
    Route::post('/gallery', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'store'])->middleware('can:gallery.create')->name('gallery.store');
    Route::get('/gallery/{galleryAlbum}/edit', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'edit'])->middleware('can:gallery.edit')->name('gallery.edit');
    Route::put('/gallery/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'update'])->middleware('can:gallery.edit')->name('gallery.update');
    Route::delete('/gallery/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryAlbumController::class, 'destroy'])->middleware('can:gallery.delete')->name('gallery.destroy');
    Route::get('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'index'])->middleware('can:videos.manage')->name('videos.index');
    Route::get('/videos/create', [\App\Http\Controllers\Admin\VideoController::class, 'create'])->middleware('can:videos.create')->name('videos.create');
    Route::post('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'store'])->middleware('can:videos.create')->name('videos.store');
    Route::get('/videos/{video}/edit', [\App\Http\Controllers\Admin\VideoController::class, 'edit'])->middleware('can:videos.edit')->name('videos.edit');
    Route::put('/videos/{video}', [\App\Http\Controllers\Admin\VideoController::class, 'update'])->middleware('can:videos.edit')->name('videos.update');
    Route::delete('/videos/{video}', [\App\Http\Controllers\Admin\VideoController::class, 'destroy'])->middleware('can:videos.delete')->name('videos.destroy');

    Route::get('/resources', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'index'])->middleware('can:resources.manage')->name('resources.index');
    Route::get('/resources/create', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'create'])->middleware('can:resources.create')->name('resources.create');
    Route::post('/resources', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'store'])->middleware('can:resources.create')->name('resources.store');
    Route::get('/resources/{resourceDocument}/edit', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'edit'])->middleware('can:resources.edit')->name('resources.edit');
    Route::put('/resources/{resourceDocument}', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'update'])->middleware('can:resources.edit')->name('resources.update');
    Route::delete('/resources/{resourceDocument}', [\App\Http\Controllers\Admin\ResourceDocumentController::class, 'destroy'])->middleware('can:resources.delete')->name('resources.destroy');
    Route::get('/resource-categories', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'index'])->middleware('can:resource-categories.manage')->name('resource-categories.index');
    Route::get('/resource-categories/create', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'create'])->middleware('can:resource-categories.create')->name('resource-categories.create');
    Route::post('/resource-categories', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'store'])->middleware('can:resource-categories.create')->name('resource-categories.store');
    Route::get('/resource-categories/{resourceCategory}/edit', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'edit'])->middleware('can:resource-categories.edit')->name('resource-categories.edit');
    Route::put('/resource-categories/{resourceCategory}', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'update'])->middleware('can:resource-categories.edit')->name('resource-categories.update');
    Route::delete('/resource-categories/{resourceCategory}', [\App\Http\Controllers\Admin\ResourceCategoryController::class, 'destroy'])->middleware('can:resource-categories.delete')->name('resource-categories.destroy');

    Route::get('/media', [MediaController::class, 'index'])->middleware('can:media.manage')->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->middleware('can:media.create')->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->middleware('can:media.delete')->name('media.destroy');

    Route::get('/menus', [MenuController::class, 'index'])->middleware('can:menus.manage')->name('menus.index');
    Route::get('/menus/header', [MenuController::class, 'header'])->middleware('can:menus.manage')->name('menus.header');
    Route::get('/menus/footer', [MenuController::class, 'footer'])->middleware('can:menus.manage')->name('menus.footer');
    Route::post('/menus/assign', [MenuController::class, 'assign'])->middleware('can:menus.manage')->name('menus.assign');
    Route::patch('/menus/order', [MenuController::class, 'order'])->middleware('can:menus.manage')->name('menus.order');
    Route::delete('/menus/bulk', [MenuController::class, 'bulkDestroy'])->middleware('can:menus.manage')->name('menus.bulk-destroy');
    Route::delete('/menus/{menuItem}', [MenuController::class, 'destroy'])->middleware('can:menus.manage')->name('menus.destroy');

    Route::get('/homepage-slides', [HomepageSlideController::class, 'index'])->middleware('can:homepage-slides.manage')->name('homepage-slides.index');
    Route::get('/homepage-slides/create', [HomepageSlideController::class, 'create'])->middleware('can:homepage-slides.create')->name('homepage-slides.create');
    Route::post('/homepage-slides', [HomepageSlideController::class, 'store'])->middleware('can:homepage-slides.create')->name('homepage-slides.store');
    Route::get('/homepage-slides/{homepageSlide}/edit', [HomepageSlideController::class, 'edit'])->middleware('can:homepage-slides.edit')->name('homepage-slides.edit');
    Route::put('/homepage-slides/{homepageSlide}', [HomepageSlideController::class, 'update'])->middleware('can:homepage-slides.edit')->name('homepage-slides.update');
    Route::delete('/homepage-slides/{homepageSlide}', [HomepageSlideController::class, 'destroy'])->middleware('can:homepage-slides.delete')->name('homepage-slides.destroy');

    Route::get('/settings', [SiteSettingController::class, 'edit'])->middleware('can:settings.manage')->name('settings.edit');
    Route::put('/settings', [SiteSettingController::class, 'update'])->middleware('can:settings.manage')->name('settings.update');

    Route::get('/users', [UserController::class, 'index'])->middleware('can:users.manage')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('can:users.create')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
    Route::patch('/users/bulk-status', [UserController::class, 'bulkStatus'])->middleware('can:users.change-status')->name('users.bulk-status');
    Route::delete('/users/bulk', [UserController::class, 'bulkDestroy'])->middleware('can:users.delete')->name('users.bulk-destroy');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.edit')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('users.update');
    Route::patch('/users/{user}/status', [UserController::class, 'status'])->middleware('can:users.change-status')->name('users.status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete')->name('users.destroy');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:users.show')->name('users.show');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('can:roles.manage')->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->middleware('can:roles.create')->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('can:roles.create')->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('can:roles.edit')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.edit')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:roles.delete')->name('roles.destroy');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('can:roles.show')->name('roles.show');
    Route::get('/permissions', PermissionController::class)->name('permissions.index');
    Route::get('/activity-log', ActivityLogController::class)->name('activity.index');
    Route::get('/ui-kit', UiKitController::class)->name('ui-kit');
});

Route::any('/admin/{path?}', fn () => abort(404))->where('path', '.*');
Route::get('/{path}', [PublicPageController::class, 'show'])->where('path', '.*')->name('public.page');
