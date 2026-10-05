# Resources and page connections

Resources are structured downloadable documents, managed separately from Pages. The nested Resources sidebar contains Add/Manage Resources and Add/Manage Resource Categories. Both use the existing TailAdmin controls, breadcrumb Save/Close actions, and simple search. Resource documents remain paginated; category managers show the full ordered set like Pages.

## Admin workflow

1. Create a resource category, for example Legal Documents or Training Materials.
2. Add a resource with title, category, optional stable slug, CKEditor description, PDF/Word/Excel attachment (up to 10 MB), a Published toggle, and publication date. Title and URL each occupy half the desktop row. The date picker and Published toggle sit below URL, and Description uses the full card width. Fields stack on mobile.
3. Create or edit a Page with Page Type: Resource.
4. Publish the page and the documents. Assign the page to the existing public menu.

The page retains its own title, introduction/body, banner, URL, SEO, and navigation placement. Its matching resources appear below that content automatically. One document can appear on multiple pages that select its category or All categories, without duplicated uploads.

## Data and behavior

resource_categories stores name, unique slug, full-width CKEditor description, Published status, internal order, and audit actors. Category forms omit slug and display-order fields, and the category index omits slug text. The service generates category slugs from the name on create and preserves existing slugs when editing. Reorder categories in the index by dragging rows or using arrow buttons. resource_documents stores title, unique slug, sanitized rich-text description, category, MediaAsset attachment, internal order, draft/published state, publication timestamp, and audit actors. Resource forms also omit display order: new documents append to the ordered set, and edits preserve their position. Resource forms generate editable URL suggestions while typing the title. Manual URLs and existing saved URLs remain stable through title changes and validation reloads. Clearing the URL resumes generation on the next title change; blank URLs on create still generate when saved.

Existing `pages.resource_category_id` values are retained for compatibility with previously configured public pages, but new Page forms no longer expose category selection. Saving a page clears the legacy resource category assignment.

Public listings are ordered by display order, then latest publication date and ID; they contain 12 documents per page and preserve the page's language query. Only published resources in active categories whose publication timestamp has arrived and whose media record exists are listed. Detail and download endpoints apply the same publication checks and return 404 for drafts/future publication. Missing files return 404 on download.

Public URLs /resources/{slug} and /resources/{slug}/download are reserved for resource documents; avoid using those paths for generic child Pages. A generic /resources page itself is supported.

Attachments follow the existing public shared media storage model. The download endpoint checks publication status; underlying public storage URLs are not private draft access controls. This catalogue is intended for public documents. Replacing or deleting a resource preserves old uploaded files in shared media storage.

Inactive categories hide their documents on all public listings/detail/download routes. Existing category assignments stay editable in admin. Categories cannot be deleted while referenced by documents or Pages. Foreign keys reinforce that rule, and transactions lock category selections. Move references first.

## Architecture and permissions

New workflows follow Route -> FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model. Bindings, permissions, and sidebar entries are code-owned. Services manage transactions, audit events, publication rules, upload rollback cleanup, and category deletion safeguards. Document descriptions use CKEditor and the shared SafeHtml sanitizer on save and public detail rendering. Public catalogue excerpts remain plain text. Category descriptions use CKEditor and are not rendered as raw HTML on public pages.

resources.manage/create/edit/delete/publish and resource-categories.manage/create/edit/delete are separate abilities. Publishing, unpublishing, editing, or deleting published resources also requires resources.publish. Page category selection is available to authorized page editors without requiring resource management permission.

## Category ordering

Reordering requires resource-categories.edit. A dedicated FormRequest validates the ID list, and the service locks the complete category set, rejects stale/partial submissions, saves positions transactionally, and records an activity event. Search must be cleared before reordering. Move controls are disabled while saving, and failed requests restore the prior row order.

## Resource ordering

Reordering requires resources.edit and an unfiltered index. Drag rows or use the up/down buttons to move documents within the displayed page. Other pages retain their relative order. The request includes resources and original_order ID lists; its FormRequest validates unique existing IDs, and the service locks the complete ordered set, checks the original contiguous sequence, and saves the replacement positions transactionally. Stale or mismatched submissions return validation feedback without changing positions. Successful writes record resources.reordered and invalidate the frontend cache.

Move buttons are disabled at page edges and while saving. Failed requests restore the previous rows and announce the error. Clear search before reordering. Neither create nor edit accepts a user-entered display order.

## Setup and verification

Run:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

Run php artisan storage:link if the public media link is absent. Grant new permissions to appropriate existing roles. PHP upload_max_filesize and post_max_size must allow the desired document sizes. No new dependencies are needed.

Run php artisan test, php vendor/bin/pint --test, npm run build, and node --test tests/js/*.test.js before handoff. Resource feature coverage includes rich-text descriptions, uploads, validation, publication authorization, ordering across paginated lists, stale submissions, transactional rollback, and frontend cache invalidation.

Category Add/Edit forms place Save and Close beside the breadcrumb heading. Category name takes the full width, followed by the Published toggle and a full-width Description editor. Slugs remain internal and have no form field or listing text. The Published toggle still uses the existing is_active boolean and category edit permissions.
