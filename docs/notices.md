# Notice types and page connections

The Notices sidebar now contains Add Notice and Manage Notices. Existing notice content, optional attachments, SEO, publication controls, and permissions are retained.

## Managed categories and deadlines

The Notices sidebar contains Add/Manage Notices and Add/Manage Notice Categories. Categories have a name, stable unique slug, full-width CKEditor description, and Published toggle. The numeric display-order field is removed. Categories are ordered directly in the manager by dragging rows or using arrow buttons, with the same row-based approach as Pages.

The migration creates General Notices, Tender Notices, Press Releases, and Application Announcements as editable category records, and maps existing notice/page type assignments to their category IDs. Existing All categories pages become All categories. The legacy notice_type columns/enum remain stored for historical compatibility but no longer drive admin choices or public filtering.

An inactive category and all its notices are hidden on the public website, including detail URLs. Admin selectors retain inactive categories, labelled Unpublished, to avoid losing assignments. Public filter options show active categories only.

Each notice can have an optional deadline timestamp, useful for tenders and applications. Historical deadlines are allowed. Passing a deadline adds a Deadline passed label; it does not automatically unpublish or delete the notice.

## Page connection

On Add/Edit Page, select Page Type: Notices. Category selection is no longer part of the Page form; existing category assignments remain supported by the public compatibility layer, while saving a page clears the legacy notice category assignment.

Examples: Tender Notices page selects the Tender Notices category; Press Releases selects Press Releases; Notice Board selects All categories. The existing Page/menu workflow controls page URLs and navigation placement.

The public /notices listing also has a category filter, and detail pages show the notice's category and optional deadline. Lists contain only published notices with publication timestamps reached, ordered by display order, publication date, then ID. Page listings paginate using notices_page and preserve query parameters. Draft and scheduled detail URLs return 404.

## Architecture

New requests cover create/edit and public index/detail routes. Controllers delegate notice reads and writes to NoticeService. Its existing bound repository contract now includes row locking and filtered public pagination. Notice categories use their own repository contract, service, FormRequests, and controller. The contract is bound in AppServiceProvider. Services own workflows, authorization, transactions, audit logging, and upload rollback cleanup.

Creating/editing/deleting requires the existing action permission. Publishing, unpublishing, editing or deleting published notices additionally requires notices.publish. Page editors can select notice categories without notice management permissions. Attachments remain optional public Media Library assets; replacing or deleting a notice preserves uploaded files.

Notice descriptions retain the existing rich-text rendering. The project's existing rich-text sanitization work remains separate.

## Category management and ordering

notice-categories.manage/create/edit/delete are code-owned permissions. Reordering uses notice-categories.edit and a FormRequest validating distinct existing category IDs. The service locks all categories and requires the submitted IDs to match the complete current set, then persists the order transactionally and logs the change. Reordering is disabled while searching or saving. Failed requests restore the original row order and show an error.

The category manager lists the complete ordered set, as Pages does, rather than paginating a partial order. Categories cannot be deleted while referenced by notices or Pages; restrictive foreign keys enforce the same rule.

## Setup and verification

Run:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

No new dependencies are required. Tests, builds, migrations, permission/menu synchronization, and Git actions remain deferred at the user's request. PHP and compiled Blade syntax checks are performed. Functional checks remain for category/deadline validation, CRUD, publish authorization, draft/scheduled visibility, pagination, page type switching, upload rollback, and legacy category mapping, inactive-category hiding, reorder authorization, and stale reorder rejection.

Category Add/Edit forms place Save and Close beside the breadcrumb heading. Category name takes the full width, slug and Published toggle share a row, and the Description editor occupies its own full-width row outside the field grid. The Published toggle still uses the existing is_active boolean and category edit permissions.

Add/Edit Notice places the Published toggle where the numeric Display order field previously appeared; the duplicate toggle below the description is removed. Notice ordering remains internal: new notices append, and edits preserve their order.
