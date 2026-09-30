# Photo gallery and videos

Both modules live under Media, alongside Home Slides and the reusable Media Library. Their Add and Manage pages use the existing TailAdmin shell, breadcrumb Save/Close actions, form components, dark mode, simple title search, and pagination.

## Photo gallery

Routes use /admin/gallery and the admin.gallery.* names. Add/Edit Gallery is a single card with a title, stable unique URL slug, Published toggle beside the slug, and multiple-image upload. There are no separate album, cover, or publication cards, and no event date, description, caption, or ordering controls. Publication time is set automatically by the service on publishing and preserved while published; unpublishing clears it.

Upload up to 30 JPG/PNG/WebP images per save, up to 100 per gallery, and 5 MB per image. Existing images show previews with Remove image checkboxes. Removing an image detaches it from that gallery. Deleting a gallery removes its image associations. Uploaded files remain in Media Library. Images appear in their stored upload order.

The existing GalleryAlbum/gallery_albums and GalleryPhoto storage structure is retained. Legacy optional fields and captions remain stored, but the simplified form no longer edits them. No schema migration is required for this simplification.

Bulk uploads also depend on the server's PHP upload_max_filesize, post_max_size, and max_file_uploads settings. Configure those limits for the intended batch size, or upload smaller batches.

## Videos

Routes use /admin/videos and the admin.videos.* names. A video has a title, stable unique slug, CKEditor rich-text description, HTTP/HTTPS video URL, optional thumbnail, display order, draft/published status, and automatic publication timestamp. The form omits slug/category fields and places the Published toggle immediately after the Video URL field in Video details, with no separate Publication card. Slugs are generated internally with a numeric suffix for duplicate titles. Publication time is set automatically by the service on publishing and preserved while published; unpublishing clears it.

Videos are URL-based catalogue entries, not uploaded video files. The manager opens links in a new tab. No remote fetching or arbitrary iframe rendering is performed.

## Architecture and permissions

Both workflows follow FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model. Contracts are bound in AppServiceProvider. Services own authorization, transactions, audit events, and upload rollback cleanup. Existing records are locked while saving or deleting. Photo IDs are scoped to their owning gallery during validation and persistence.

gallery.* and videos.* each include manage, create, edit, delete, and publish permissions. Publishing, unpublishing, editing, or deleting published records requires the publish permission in addition to the action permission. The admin menu remains config-owned.

Selecting Published sets a missing publication timestamp to now. Galleries and Videos set their timestamps automatically. Public gallery/video routes and frontend templates remain a later integration step. They must only expose published records whose publication timestamp has arrived.

## Setup

Run these commands after pulling the change:

    php artisan config:clear
    php artisan migrate
    php artisan admin:permissions-sync
    php artisan admin:menu-regenerate

Run php artisan storage:link if the public storage link does not already exist. Grant the new permissions to appropriate existing roles. No new Composer or npm dependencies are required.

At the user's request, tests, builds, migrations, permission sync, menu regeneration, and Git actions were not run for this change. PHP syntax is checked separately. CRUD, publication permissions, validation, photo ownership/order/limits, upload rollback, and UI behavior require a later verification pass.
