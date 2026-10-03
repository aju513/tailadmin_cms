# Architecture

## Stack and boundary

The admin application is a server-rendered Laravel monolith under `/admin`. Blade renders TailAdmin views, Alpine.js provides local UI state, and Tailwind CSS 4 provides design tokens. The public boundary serves the homepage and published nested pages.

## Request flow

Non-trivial features follow `Route -> FormRequest -> Controller -> Service -> Repository -> Model`.

- Routes use stable `admin.*` names and authentication/authorization middleware.
- FormRequests validate and authorize input.
- Controllers contain transport concerns only.
- Services coordinate workflows, database transactions, state changes, and activity records.
- Repository contracts isolate persistence and query behavior; Eloquent implementations are container-bound in `AppServiceProvider`.
- Models define relationships, casts, and small state helpers.

Fortify owns login, logout, and password-reset controllers. The application provides its Blade views and active-user authentication callback. Profile and password changes use the project layers instead of Fortify's optional update endpoints.

## Authorization

The sidebar and Blade `@can` directives improve usability, but route middleware, FormRequest authorization, and Gate checks enforce security. The `super-admin` role receives a `Gate::before` allow result. Other application code checks permissions rather than role names.

## Data ownership

- `config/permissions.php` owns permission names.
- `config/admin-menu.php` owns navigation metadata.
- Services own role synchronization and audit event properties.
- Repositories own filtering and pagination.
- CMS content uses local public-disk media assets and database-managed public menus; cloud storage is not required.

## Public frontend

`routes/web.php` loads `routes/admin.php` before `routes/front.php`; the public CMS catch-all is last. Controllers and FormRequests use `Admin` and `Front` namespaces. Admin templates live in `resources/views/admin` and assets in `resources/admin`; public templates and assets use the matching `front` folders. Shared models, repositories and content services remain domain-based.

Front/ContentController delegates to Frontend/FrontendService and the bound FrontendRepositoryInterface. FrontendLayoutService prepares common settings and menus, and the frontend layout composer provides these to new public views. SEO, sitemap, image processing and cache invalidation use separate services. See [folder-structure.md](folder-structure.md) for configuration and independent Vite builds, and [frontend.md](frontend.md) for public features.
