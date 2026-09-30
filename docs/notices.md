# Notice types and page connections

The Notices sidebar now contains Add Notice and Manage Notices. Existing notice content, optional attachments, SEO, publication controls, and permissions are retained.

## Types and deadlines

Four code-owned NoticeType values are available:

- general: General Notices
- tender: Tender Notices
- press_release: Press Releases
- application: Application Announcements

Existing records default to General Notices during migration. Types are fixed choices; no separate taxonomy CRUD or additional permissions are needed.

Each notice can have an optional deadline timestamp, useful for tenders and applications. No deadline is required for any type. Historical deadlines are allowed. Passing a deadline adds a Deadline passed label; it does not automatically hide, unpublish, or delete the notice. Publication and deadline are independent.

## Page connection

On Add/Edit Page, select Page Type: Notices. The Notice Type dropdown appears, allowing a single type or All types. Published notices of that type are listed below the page's existing introduction/body. A null selection means All types. Changing to another page type clears notice_type on the server.

Examples: Tender Notices page selects tender; Press Releases selects press_release; Notice Board selects All types. The existing Page/menu workflow controls page URLs and navigation placement.

The public /notices listing also has a type filter, and detail pages show the notice's type and optional deadline. Lists contain only published notices with publication timestamps reached, ordered by display order, publication date, then ID. Page listings paginate using notices_page and preserve query parameters. Draft and scheduled detail URLs return 404.

## Architecture

New requests cover create/edit and public index/detail routes. Controllers delegate notice reads and writes to NoticeService. Its existing bound repository contract now includes row locking and filtered public pagination. Services own workflows, authorization, transactions, audit logging, and upload rollback cleanup.

Creating/editing/deleting requires the existing action permission. Publishing, unpublishing, editing or deleting published notices additionally requires notices.publish. Page editors can select notice types without notice management permissions. Attachments remain optional public Media Library assets; replacing or deleting a notice preserves uploaded files.

Notice descriptions retain the existing rich-text rendering. The project's existing rich-text sanitization work remains separate.

## Setup and verification

Run:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

No new dependencies are required. Tests, builds, migrations, permission/menu synchronization, and Git actions remain deferred at the user's request. PHP and compiled Blade syntax checks are performed. Functional checks remain for type/deadline validation, CRUD, publish authorization, draft/scheduled visibility, pagination, page type switching, upload rollback, and legacy record defaults.
