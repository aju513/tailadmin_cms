<?php

namespace App\Providers;

use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\ContentRepositoryInterface;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use App\Repositories\Contracts\MediaAssetRepositoryInterface;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Repositories\Contracts\TeamCategoryRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ActivityRepository;
use App\Repositories\Eloquent\ContentAuthorRepository;
use App\Repositories\Eloquent\ContentCategoryRepository;
use App\Repositories\Eloquent\ContentTagRepository;
use App\Repositories\Eloquent\HomepageSlideRepository;
use App\Repositories\Eloquent\MediaAssetRepository;
use App\Repositories\Eloquent\MenuRepository;
use App\Repositories\Eloquent\NewsRepository;
use App\Repositories\Eloquent\NoticeRepository;
use App\Repositories\Eloquent\PageRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SiteSettingRepository;
use App\Repositories\Eloquent\TeamCategoryRepository;
use App\Repositories\Eloquent\TeamMemberRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\Frontend\ImageService::class);
        $this->app->bind(\App\Repositories\Contracts\FrontendRepositoryInterface::class, \App\Repositories\Eloquent\FrontendRepository::class);
        $this->app->bind(\App\Repositories\Contracts\GalleryAlbumRepositoryInterface::class, \App\Repositories\Eloquent\GalleryAlbumRepository::class);
        $this->app->bind(\App\Repositories\Contracts\VideoRepositoryInterface::class, \App\Repositories\Eloquent\VideoRepository::class);
        $this->app->bind(\App\Repositories\Contracts\ResourceCategoryRepositoryInterface::class, \App\Repositories\Eloquent\ResourceCategoryRepository::class);
        $this->app->bind(\App\Repositories\Contracts\ResourceDocumentRepositoryInterface::class, \App\Repositories\Eloquent\ResourceDocumentRepository::class);
        $this->app->bind(\App\Repositories\Contracts\NoticeCategoryRepositoryInterface::class, \App\Repositories\Eloquent\NoticeCategoryRepository::class);
        $this->app->bind(\App\Repositories\Contracts\HallRepositoryInterface::class, \App\Repositories\Eloquent\HallRepository::class);
        $this->app->bind(\App\Repositories\Contracts\DashboardRepositoryInterface::class, \App\Repositories\Eloquent\DashboardRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ActivityRepositoryInterface::class, ActivityRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->bind(NoticeRepositoryInterface::class, NoticeRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, TeamMemberRepository::class);
        $this->app->bind(TeamCategoryRepositoryInterface::class, TeamCategoryRepository::class);
        $this->app->bind(MediaAssetRepositoryInterface::class, MediaAssetRepository::class);
        $this->app->bind(SiteSettingRepositoryInterface::class, SiteSettingRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(HomepageSlideRepositoryInterface::class, HomepageSlideRepository::class);
        $this->app->when(\App\Services\CategoryService::class)->needs(ContentRepositoryInterface::class)->give(ContentCategoryRepository::class);
        $this->app->when(\App\Services\TagService::class)->needs(ContentRepositoryInterface::class)->give(ContentTagRepository::class);
        $this->app->when(\App\Services\AuthorService::class)->needs(ContentRepositoryInterface::class)->give(ContentAuthorRepository::class);
        $this->app->when(\App\Http\Controllers\Admin\CategoryController::class)->needs(ContentRepositoryInterface::class)->give(ContentCategoryRepository::class);
        $this->app->when(\App\Http\Controllers\Admin\TagController::class)->needs(ContentRepositoryInterface::class)->give(ContentTagRepository::class);
        $this->app->when(\App\Http\Controllers\Admin\AuthorController::class)->needs(ContentRepositoryInterface::class)->give(ContentAuthorRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([\App\Models\Page::class, \App\Models\News::class, \App\Models\Notice::class, \App\Models\NoticeCategory::class, \App\Models\ResourceDocument::class, \App\Models\ResourceCategory::class, \App\Models\Hall::class, \App\Models\HallGalleryImage::class, \App\Models\GalleryAlbum::class, \App\Models\GalleryPhoto::class, \App\Models\Video::class, \App\Models\TeamMember::class, \App\Models\TeamCategory::class, \App\Models\HomepageSlide::class, \App\Models\SiteSetting::class, \App\Models\Menu::class, \App\Models\MenuItem::class, \App\Models\MediaAsset::class] as $model) {
            $model::observe(\App\Observers\FrontendContentObserver::class);
        }
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        Password::defaults(fn () => Password::min(12)->letters()->mixedCase()->numbers()->symbols());

        Event::listen(Login::class, function (Login $event): void {
            activity('auth')->causedBy($event->user)->event('auth.login')->log('User signed in');
        });
        Event::listen(Failed::class, function (Failed $event): void {
            activity('auth')->event('auth.failed')->withProperties([
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
            ])->log('Sign-in attempt failed');
        });
        Event::listen(Logout::class, function (Logout $event): void {
            $activity = activity('auth')->event('auth.logout');
            if ($event->user) {
                $activity->causedBy($event->user);
            }
            $activity->log('User signed out');
        });
        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            activity('auth')->causedBy($event->user)->event('auth.password-reset')->log('Password reset completed');
        });
    }
}
