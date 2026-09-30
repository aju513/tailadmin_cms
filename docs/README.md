# Admin Foundation Documentation

This repository is a Laravel 12, Blade, Alpine.js, Tailwind CSS 4, and TailAdmin admin foundation.

## Start here

1. Install dependencies with `composer install` and `npm install`.
2. Configure `.env`, including the database and mail transport.
3. Run `php artisan migrate --seed`.
4. Sign in at `/admin/login` with `admin@admin.com` / `admin`.
The bootstrap credential is intentionally predictable and must never remain unchanged. The permission command creates it only when the users table is empty. Password changes remain available from the admin password screen, but are not forced by middleware.

## Documentation map

- [Notice types and page connections](notices.md): notice classifications, deadlines, and automatic Notices page listings.

- [Resources and page connections](resources.md): document catalogue, categories, automatic Resource page listings, and downloads.

- [Photo gallery and videos](media-catalogues.md): dedicated Media catalogue pages, album photos, publication permissions, and setup.


- [Halls and bookings](halls-and-bookings.md): implemented hall fields and admin screens, booking phases, availability rules, and setup commands.

- [Analytics dashboard](dashboard.md): Google Analytics, Search Console, credentials, reporting periods, and unavailable states.

- [Architecture](architecture.md): layers, data flow, routes, and boundaries.
- [Authentication and authorization](authentication-authorization.md): Fortify, users, roles, safeguards, and auditing.
- [Permissions and menus](menus-permissions.md): configuration schemas and regeneration commands.
- [UI components](ui-components.md): layouts and TailAdmin component conventions.
- [Feature development](feature-development.md): required implementation workflow.
- [Testing and operations](testing-operations.md): verification, deployment, and maintenance commands.
- [Content management](content-management.md): pages, media, menus, settings, homepage slides, categories, tags, and authors.
