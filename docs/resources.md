# Resources and page connections

Resources are structured downloadable documents, managed separately from Pages. The nested Resources sidebar contains Add/Manage Resources and Add/Manage Resource Categories. Both use the existing TailAdmin controls, breadcrumb Save/Close actions, and simple search. Resource documents remain paginated; category managers show the full ordered set like Pages.

## Admin workflow

1. Create a resource category, for example Legal Documents or Training Materials.
2. Add a resource with title, category, optional stable slug, plain-text description, PDF/Word/Excel attachment (up to 10 MB), display order, status, and publication timestamp.
3. Create or edit a Page. Select Page Type: Resource.
4. The Resource Category dropdown appears. Select a category, or All categories.
5. Publish the page and the documents. Assign the page to the existing public menu.

The page retains its own title, introduction/body, banner, URL, SEO, and navigation placement. Its matching resources appear below that content automatically. One document can appear on multiple pages that select its category or All categories, without duplicated uploads.

## Data and behavior

resource_categories stores name, unique slug, full-width CKEditor description, Published status, internal order, and audit actors. Category forms have no display-order field. Reorder categories in the index by dragging rows or using arrow buttons. resource_documents stores title, unique slug, category, description, MediaAsset attachment, order, draft/published state, publication timestamp, and audit actors. Slugs remain stable on edit unless explicitly changed.

pages.resource_category_id is nullable. Null means All categories only for Resource pages. Changing a page to another type clears its resource category on the server. Existing Resource pages default to All categories after migration.

Public listings are ordered by display order, then latest publication date and ID; they contain 12 documents per page and preserve the page's language query. Only published resources in active categories whose publication timestamp has arrived and whose media record exists are listed. Detail and download endpoints apply the same publication checks and return 404 for drafts/future publication. Missing files return 404 on download.

Public URLs /resources/{slug} and /resources/{slug}/download are reserved for resource documents; avoid using those paths for generic child Pages. A generic /resources page itself is supported.

Attachments follow the existing public Media Library storage model. The download endpoint checks publication status; underlying public storage URLs are not private draft access controls. This catalogue is intended for public documents. Replacing or deleting a resource preserves old uploaded files in Media Library.

Inactive categories hide their documents on all public listings/detail/download routes. Existing category assignments stay editable in admin. Categories cannot be deleted while referenced by documents or Pages. Foreign keys reinforce that rule, and transactions lock category selections. Move references first.

## Architecture and permissions

New workflows follow Route -> FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model. Bindings, permissions, and sidebar entries are code-owned. Services manage transactions, audit events, publication rules, upload rollback cleanup, and category deletion safeguards. Document descriptions are escaped plain text; category descriptions use CKEditor and are not rendered as raw HTML on public pages.

resources.manage/create/edit/delete/publish and resource-categories.manage/create/edit/delete are separate abilities. Publishing, unpublishing, editing, or deleting published resources also requires resources.publish. Page category selection is available to authorized page editors without requiring resource management permission.

## Category ordering

Reordering requires resource-categories.edit. A dedicated FormRequest validates the ID list, and the service locks the complete category set, rejects stale/partial submissions, saves positions transactionally, and records an activity event. Search must be cleared before reordering. Move controls are disabled while saving, and failed requests restore the prior row order.

## Setup and verification

Run:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

Run php artisan storage:link if the public media link is absent. Grant new permissions to appropriate existing roles. PHP upload_max_filesize and post_max_size must allow the desired document sizes. No new dependencies are needed.

At the user's request, tests, builds, migrations, menu/permission synchronization, and Git actions are deferred. PHP and compiled Blade syntax checks are performed separately. Functional verification remains: resource/category CRUD, invalid attachments, publication authorization, references, page category selection/reset, All categories, pagination, scheduled/draft visibility, missing files, and upload rollback.

Category Add/Edit forms place Save and Close beside the breadcrumb heading. Category name takes the full width, slug and Published toggle share a row, and the Description editor occupies its own full-width row outside the field grid. The Published toggle still uses the existing is_active boolean and category edit permissions.
