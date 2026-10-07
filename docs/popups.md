# Popup Manager

Open **Popup Manager** at `/admin/popups`. Add a title and JPG, PNG, or WebP image (the configured image size limit applies). Portrait and landscape images display without cropping. Optionally provide an image description, plain-text message, and a button label plus HTTP/HTTPS URL. New popups start as drafts. Publish or unpublish using the edit form; there is no scheduling or translation editing.

The first published popup in priority order with an available image opens immediately on every homepage visit, including reloads and browser back navigation. Close it with the Close button, Escape, or the backdrop. Dismissal is not stored. Other public pages never show it. Without JavaScript the dialog stays closed. Long content scrolls within the viewport.

Search titles on the manager. Clear search to reorder the complete list using drag-and-drop or up/down buttons. New records append; edits preserve priority. Reordering requires both edit and publish permissions because it changes the live announcement. Stale or partial ordering submissions are rejected.

Permissions: `popups.manage`, `popups.create`, `popups.edit`, `popups.delete`, and `popups.publish`. Publishing, unpublishing, editing, and deleting published popups require publish permission in addition to the action permission. Server-side FormRequests and services enforce authorization.

The feature follows Route -> FormRequest -> PopupController -> PopupService -> PopupRepositoryInterface -> Eloquent PopupRepository -> Popup. Services transact writes, record activity, clean newly uploaded files after failed saves, and clear frontend caches after successful changes. Replaced images and deleted popups retain shared media assets. Missing media/files are skipped in public selection.

Deploy with `php artisan migrate`, `php artisan admin:permissions-sync`, `php artisan admin:menu-regenerate`, `php artisan frontend:cache-clear`, and `npm run build`. No announcement appears until one is published. Verify with the PHP suite, Pint, production build, and JavaScript suite.
