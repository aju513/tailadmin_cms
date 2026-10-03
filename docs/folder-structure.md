# Admin and frontend structure

The application follows the separate admin/front boundaries used in `D:/travel-dashboard-v6-tailwind-latest`, retaining Laravel 12, Tailwind 4, Fortify and this project's repository/service architecture.

```text
app/
  Http/
    Controllers/
      Admin/                 Admin screens, including the dashboard
      Front/                 Public content, contact and sitemaps
      Controller.php         Shared controller base
    Requests/
      Admin/<Feature>/       Admin validation and permission checks
      Front/<Feature>/       Public validation and public-access authorization
    ViewComposers/Front/      Shared frontend layout data
  Providers/
    AppServiceProvider.php   Repository bindings and shared application setup
    FortifyServiceProvider.php
    ViewComposerServiceProvider.php
  Services/
    Frontend/                Public orchestration, layout, SEO, cache and images
    <Feature>Service.php     Shared content workflows
  Repositories/
    Contracts/               Persistence contracts
    Eloquent/                Eloquent implementations
  Models/                    Shared CMS data
config/
  admin.php                  Admin assets, build directory and hot file
  admin-menu.php             Generated, permission-aware admin navigation
  frontend.php               Public layout, assets, branding and menu defaults
resources/
  admin/{css,js}/             Admin assets and JavaScript components
  front/{css,js,fonts,images,vendor}/
  views/
    admin/
      auth/                  Sign-in and password-reset pages
      layouts/               app.blade.php and auth.blade.php
      partials/              Assets, header, sidebar and backdrop
      pages/<feature>/       Admin pages and form partials
    front/
      layouts/               Public document shell
      partials/              Assets, metadata, header, footer and menu branches
      components/            Include-based cards and pagination
      sections/              Homepage and reusable page sections
      catalogues/            Module listings
      details/               Module detail views
      pages/                 Home, CMS page, detail and search entry views
      emails/                Public contact email templates
    components/
      admin/header/          Admin dropdown Blade components
      front/                 Public Blade components such as x-front.image
      common/,form/,ui/,...   Reusable TailAdmin component library
routes/
  web.php                    Loads admin.php before front.php
  admin.php                  Protected admin routes and reserved admin fallback
  front.php                  Public routes, with the CMS catch-all last
public/build/
  admin/                     Admin manifest and compiled assets
  front/                     Frontend manifest and compiled assets
vite.config.js               Frontend build configuration
vite.admin.config.js         Admin build configuration
```

Controllers and requests belong to the boundary that receives the request. Models, repositories and content workflows remain shared so the admin edits the same records the public website publishes. New features follow `Route -> FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model`.

## Frontend header and footer

Public pages extend `front.layouts.app`. It includes the header and footer outside the semantic `main` element and provides `styles` and `scripts` stacks. The registered layout composer supplies `FrontendLayoutService` data and metadata to new views using this shell. Existing public workflows use the same layout service when preparing page data and SEO.

`config/frontend.php` owns:

- `layout.header` and `layout.footer`: Blade partial names.
- `branding`: fallback logo and footer illustration paths under `public/`.
- `defaults`: fallback province, office hours, copyright and training/TMIS URLs.
- `menu_locations`: CMS menu locations used for the header and footer.
- `navigation.header` and `navigation.footer`: fallback links when that CMS menu does not exist. Entries accept `label` and a named `route`, a `setting` key or a `url`, plus optional `children` and `external`.
- `assets`: entrypoints, build directory and development hot-file path.

Saved Site Settings override defaults. Existing CMS menus override configured navigation, including intentionally empty menus. The repository enforces published-page visibility and menu hierarchy. The footer displays configured office hours; Blade escapes settings and labels, and unsafe navigation protocols are excluded.

Use **Site Settings**, **Header Menu**, and **Footer Menu** to manage content. Clear the frontend cache after changing configuration defaults: `php artisan frontend:cache-clear`. Rebuild Laravel's configuration cache if deployment uses `config:cache`.

## Assets and development

```powershell
npm run dev                  # Both Vite servers through concurrently
npm run dev:front            # Frontend only, port 5173
npm run dev:admin            # Admin only, port 5174
npm run build                # Both production builds
npm run build:front          # Frontend only
npm run build:admin          # Admin only
```

The servers use separate `storage/framework/vite-front.hot` and `vite-admin.hot` files. Layout asset partials select the matching hot file and manifest. Each build replaces only its own output directory. Admin Tailwind scans admin views and shared components; frontend Tailwind scans public views and assets.

Build both manifests before feature tests or deployment. After the first checkout of this structure, run `composer dump-autoload`, `php artisan view:clear`, `php artisan frontend:cache-clear` and `php artisan admin:menu-regenerate` to refresh generated files. Route URLs, route names and permissions are retained; no database migration is added.

`ApplicationStructureTest` covers production/development asset separation, configuration fallbacks, CMS overrides, reusable layout composition and the protected admin boundary. The existing suite covers validation, authorization, publication rules and failure paths.
