# Homepage content

Open **Pages → Homepage** at `/admin/homepage`. The editor uses the same breadcrumb Save/Close actions, TailAdmin tabs, rich-text editor, upload previews, and removal checkboxes as Pages, News, Gallery, and Halls.

## Fields and public behavior

The About section shows the service cards without a decorative background image below them.

Save and Close remain available in a sticky bar below the admin header when the original Save button scrolls above the visible area, matching the Pages form. Scrolling back to the original actions hides the bar. Both Save buttons submit the same homepage form and use the same permission checks.

- **Description:** welcome title, optional subtitle, and rich-text description for the public homepage's About section.
- **Gallery Images:** multiple uploads, current image previews, and removal checkboxes. The public section's main image and thumbnail slider use these photos in upload order.
- **Social Media Image:** one sharing image, alt text, and an optional removal checkbox. It supplies the homepage's Open Graph and Twitter image.
- **SEO Details:** editable SEO title, keywords, and description. The title follows the English welcome title until customized. An empty submitted SEO title is generated server-side from the welcome title, including without JavaScript. The canonical homepage URL remains `/`.

Welcome titles and subtitles allow 255 characters, rich text 50,000, SEO title 255, keywords 500, and SEO description 1,000. Rich text is sanitized by `Frontend/SafeHtml` before persistence and public rendering. When SEO description is blank, the homepage description falls back to the welcome text.

Set `settings.nepali` to enable English/Nepali content tabs. The gallery and SEO fields are shared. English is required; existing Nepali content is preserved when translation editing is disabled. Public `?lang=ne` rendering follows the existing locale handling.

The gallery defaults to at most 30 images, configured by `settings.homepage.gallery_limit`. Image profiles in `config/settings.php` supply MIME, size, and dimension rules for both the upload component and FormRequest:

| Profile | Recommended dimensions | Allowed types |
| --- | --- | --- |
| `images.homepage.gallery` | 1200 × 950 px | JPG, JPEG, PNG, WebP |
| `images.homepage.social` | 1200 × 630 px | JPG, JPEG, PNG, WebP |

Both inherit the configured image file limit (5 MB by default). Exact dimensions are enforced only when `enforce_dimensions` is enabled for the profile. Removing an image detaches it from the homepage; existing media assets and files remain available to other content.

Home Slides, service cards, banner text, capacity reports, and content catalogues retain their existing admin destinations. Site Settings links to the Homepage editor rather than duplicating its welcome text fields.

## Architecture and permissions

The feature follows `Route → FormRequest → HomepageController → HomepageContentService → HomepageContentRepositoryInterface → Eloquent/HomepageContentRepository → HomepageContent`.

The homepage uses a dedicated singleton `homepage_contents` record identified by the unique, server-owned key `home`. This keeps the fixed `/` homepage separate from the nested Page path/status workflow. `homepage_gallery_images` links ordered photos to the existing media assets. The service locks the singleton before changes, enforces the total gallery limit, saves all database changes in a transaction, removes newly uploaded files after failure, logs `homepage.updated`, and clears the frontend cache after successful saves. Both models also use the existing frontend content observer.

`homepage.manage` allows opening the editor and its sidebar entry. `homepage.edit` allows saving live content. These are independent code-owned permissions, enforced in route middleware, FormRequests, and the service for mutations. Saves apply immediately to the public homepage; this singleton has no publication toggle or destructive delete endpoint.

## Setup and migration

Run `php artisan migrate`, `php artisan admin:permissions-sync`, `php artisan admin:menu-regenerate`, `php artisan frontend:cache-clear`, and `npm run build` when deploying.

The migration imports existing About text and the first image from each of the up to eight published albums previously displayed on the homepage. It preserves the original settings, albums, photos, and media. Fresh installations without legacy content show configured welcome defaults until the first save. Once a homepage record exists, its gallery is authoritative; removing every image shows the placeholder rather than repopulating it from albums.

Feature tests cover permissions, UI fields, metadata, public rendering, cache refresh, translations, upload limits and types, gallery ownership, media preservation, migration compatibility, and rollback after upload failure. JavaScript tests cover SEO title suggestions, manual overrides, restored form state, and revealing hidden invalid fields.
