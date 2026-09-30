# Photo gallery and videos

Both modules live under Media, alongside Home Slides and the reusable Media Library. Their Add and Manage pages use the existing TailAdmin shell, breadcrumb Save/Close actions, form components, dark mode, simple title search, and pagination.

## Photo gallery

Routes use /admin/gallery and the admin.gallery.* names. An album has a title, stable unique slug, plain-text description, optional cover image and event date, display order, draft/published status, and optional publication timestamp. Album photos reference MediaAsset records and have editable captions and display order. Lower order values appear first; photo ID breaks ties.

Upload up to 30 JPG/PNG/WebP photos per save, up to 100 per album, and 5 MB per image. Save an album first, then edit its photo captions/order. Removing a photo detaches it from that album. Deleting an album removes its photo associations. The reusable uploaded files remain in Media Library.

Bulk uploads also depend on the server's PHP upload_max_filesize, post_max_size, and max_file_uploads settings. Configure those limits for the intended batch size, or upload smaller batches.

## Videos

Routes use /admin/videos and the admin.videos.* names. A video has a title, stable unique slug, plain-text description, HTTP/HTTPS video URL, optional thumbnail, display order, draft/published status, and automatic publication timestamp. The form omits slug/category fields and uses a Published toggle. Slugs are generated internally with a numeric suffix for duplicate titles. Publication time is set automatically by the service on publishing and preserved while published; unpublishing clears it.

Videos are URL-based catalogue entries, not uploaded video files. The manager opens links in a new tab. No remote fetching or arbitrary iframe rendering is performed.

## Architecture and permissions

Both workflows follow FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model. Contracts are bound in AppServiceProvider. Services own authorization, transactions, audit events, and upload rollback cleanup. Existing records are locked while saving or deleting. Photo IDs are scoped to their owning album during validation and persistence.

gallery.* and videos.* each include manage, create, edit, delete, and publish permissions. Publishing, unpublishing, editing, or deleting published records requires the publish permission in addition to the action permission. The admin menu remains config-owned.

Selecting Published sets a missing publication timestamp to now. Gallery albums support future scheduled publication; Videos set their timestamp automatically. Public gallery/video routes and frontend templates remain a later integration step. They must only expose published records whose publication timestamp has arrived.

## Setup

Run these commands after pulling the change:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

Run php artisan storage:link if the public storage link does not already exist. Grant the new permissions to appropriate existing roles. No new Composer or npm dependencies are required.

At the user's request, tests, builds, migrations, permission sync, menu regeneration, and Git actions were not run for this change. PHP syntax is checked separately. CRUD, publication permissions, validation, photo ownership/order/limits, upload rollback, and UI behavior require a later verification pass.
