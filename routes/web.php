<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomepageSlideController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\UiKitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('public.home');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active'])->group(function (): void {
    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password/change', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
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
