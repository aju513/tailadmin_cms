# Photo gallery and videos

Both modules live under Media, alongside Home Slides. Their Add and Manage pages use the existing TailAdmin shell, breadcrumb Save/Close actions, form components, dark mode, simple title search, and pagination.

## Photo gallery

Routes use /admin/gallery and the admin.gallery.* names. Add/Edit Gallery has separate Gallery details and Gallery images cards. Title and editable URL slug use equal desktop columns, with the Published toggle below. The image card contains the existing previews, removal controls, and multiple-image upload. There are no event date, description, caption, or numeric ordering fields. Publication time is set automatically by the service on publishing and preserved while published; unpublishing clears it.

Upload up to 30 JPG/PNG/WebP images per save, up to 100 per gallery, and 5 MB per image. Existing images show previews with Remove image checkboxes. Removing an image detaches it from that gallery. Deleting a gallery removes its image associations. Uploaded files remain in shared media storage. Images appear in their stored upload order.

The existing GalleryAlbum/gallery_albums and GalleryPhoto storage structure is retained. Legacy optional fields and captions remain stored, but the simplified form no longer edits them. No schema migration is required for this simplification.

Bulk uploads also depend on the server's PHP upload_max_filesize, post_max_size, and max_file_uploads settings. Configure those limits for the intended batch size, or upload smaller batches.

## Videos

Routes use /admin/videos and the admin.videos.* names. A video has a title, stable unique slug, CKEditor rich-text description, HTTP/HTTPS video URL, optional thumbnail, draft/published status, and automatic publication timestamp. Title and Video URL use equal desktop columns, followed by the Published toggle and full-width Description editor. The thumbnail has its own card. The form omits slug/category and numeric display-order fields. Slugs are generated internally with a numeric suffix for duplicate titles. Publication time is set automatically by the service on publishing and preserved while published; unpublishing clears it.

Videos are URL-based catalogue entries, not uploaded video files. The manager opens links in a new tab. No remote fetching or arbitrary iframe rendering is performed.

## Public media

Published Gallery and Videos page types render the same searchable, paginated listings as `/gallery` and `/videos`, including at nested CMS URLs. Clicking an album opens `/gallery/{slug}` with all its photos in saved order; photo links open a grouped Fancybox viewer with keyboard navigation. Homepage thumbnails update the main image and open the same gallery group. The main image opens the currently selected photo.

Video catalogue, detail and homepage previews use the existing local Fancybox distribution. Only normalized YouTube/Vimeo URLs become iframe previews; other valid HTTP/HTTPS URLs remain external links. The reusable `front.components.video_item` supplies catalogue/detail cards and placeholders for missing thumbnails. Captions remain escaped text. Search retains its query while paginating and clear resets the page.

## Architecture and permissions

Both workflows follow FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model. Contracts are bound in AppServiceProvider. Services own authorization, transactions, audit events, and upload rollback cleanup. Existing records are locked while saving or deleting. Photo IDs are scoped to their owning gallery during validation and persistence.

gallery.* and videos.* each include manage, create, edit, delete, and publish permissions. Publishing, unpublishing, editing, or deleting published records requires the publish permission in addition to the action permission. The admin menu remains config-owned.

Selecting Published sets a missing publication timestamp to now. Public gallery/video routes expose only published records whose publication timestamp has arrived.

## Index ordering

Gallery, Videos, and Homepage Slides use drag handles and accessible up/down buttons in their paginated indexes. The corresponding edit permission controls both the UI and the order endpoint. Gallery and Video searches must be cleared before reordering. Header actions wrap on small screens, while table overflow stays inside the card. Pending requests disable moves; failures restore the original row sequence and announce the error.

Each POST to the named admin module's `.order` route submits `records` and `original_order` as distinct lists of existing IDs. Services lock the complete ordered catalogue, validate that the submitted rows still form the same contiguous sequence through `App\Support\RecordOrder`, and replace only that sequence. Other pages keep their relative positions. A stale or mismatched list returns validation errors without writes. Successful transactions normalize positions, record an activity event, and invalidate the frontend cache; failed writes roll back every position.

New records append automatically. Editing preserves the stored position even if a request includes `sort_order`. Gallery image ordering continues to follow the stored upload order. These changes use existing columns and permissions and need no migration or permission additions.

## Setup

Run these commands after pulling the change:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

Run php artisan storage:link if the public storage link does not already exist. Grant the new permissions to appropriate existing roles. No new Composer or npm dependencies are required.

Verify changes with `php artisan test`, `php vendor/bin/pint --test`, `npm run build`, and `node --test tests/js/*.test.js`. Media ordering feature tests cover creation/editing, paginated moves, authorization, invalid/stale input, transaction rollback, and cache invalidation. JavaScript tests cover arrow/drag behavior, pending requests, and restoring rows after failures.
## Manager layout and bulk actions

Gallery and Videos use the Resources manager layout: title/breadcrumb left and Publish, Unpublish, Add Gallery/Add Video, and Bulk delete in the right-hand actions slot. The columns contain move controls when permitted, status icon, selection, title/media details, and created date/actions. Selection enables real bulk forms; status icons submit a single record through the bulk-status route. Status updates use the publish permission; deletion uses the delete permission and retains existing published-record safeguards. Bulk writes are transactional and audited.
