# Content management

The CMS provides a small government website content foundation. News articles are a dedicated resource, separate from hierarchical pages.

Menu assignment selects a complete page branch when its parent is picked: children and deeper descendants are included even when filtered out of the dropdown search. The server independently expands submitted parent IDs and avoids duplicate items. Reassign an existing parent to add missing or newly created children; existing assignments are not retroactively expanded. Draft descendants can be assigned but remain hidden publicly. This cascading selection is enabled only for menu page assignment, not other multiselect fields.

## Implemented modules

- Dedicated **Pages → Homepage** editor for welcome text, ordered gallery images, social sharing image, and SEO. It uses the existing TailAdmin form patterns and immediately updates the public About section. See [Homepage content](homepage.md).

All selectable admin content indexes now use the same animated 22px white-tick checkboxes and select-all controls. News, Notices, Resources, their categories, Team Members/Categories, Halls, Homepage Slides, Gallery, Videos, and Users share AJAX row and bulk status updates, loading guards, error feedback, and server-side authorization. Select-all affects only displayed records; nested Resource Category controls share the header's selection state. Menu item tables reuse the checkbox styling and keep their existing selection and reorder logic. Existing labels, status values, permission gates, and non-JavaScript redirect responses are preserved.

- Hall catalogue with seating capacity, NPR rates and rate units, operational availability, amenities, bilingual content, contacts, images/gallery, and SEO. See [Halls and bookings](halls-and-bookings.md) for the detailed implementation and booking roadmap. Date reservations and public booking forms are planned separately.

- Pages with nested parent/child hierarchy, generated public paths, editable slugs, drag-and-drop ordering, and validated page types: Article, News, Notices, Resource, Team, Contact Us, Grievance Form, Sitemap, Hall, FAQs, Videos, and Gallery. Grievance Form pages render a public form with database storage and reCAPTCHA v3; see [Grievance pages](grievances.md).
- Shared local upload storage for images and office documents, attached through each content form.
- Menu positions default to Main Menu, Footer Menu, and Important Links Menu; additional database-defined locations appear under Dynamic Menus. The main position retains the internal `header` location; the other two use `footer` and `important_links`. Main, Footer, and dynamic menus have a searchable page-assignment control, custom internal/external links, and an assigned-item manager with serial numbers, sibling-level drag ordering, row selection, single deletion, and bulk deletion. The assignment control remains available when every page is already assigned. Pages retain their hierarchy, dragging only changes order within the same parent, and surviving descendants reconnect to their nearest assigned ancestor after deletion. Main branches render recursively on the public site; Footer Menu supplies the top-level Quick Links. Important Links accepts only a label and full HTTP/HTTPS external URL, with a flat ordered footer list, no parent selector, and no page assignment. It retains drag ordering and single/bulk deletion. Important Links use Menus rather than Site Settings. Migrations preserve existing saved links and their display order; see [Permissions and menus](menus-permissions.md) for deployment and rollback behavior.
- Site identity and contact settings.
- Homepage slides.
- Team member directory with names, designations, biographies, photos, and active status.
- Existing news category, tag, and author records remain available to the public compatibility layer.
- News articles with draft/published status, a featured article, thumbnail/banner/social images, SEO fields, and public listings.
- Notices with title, generated slug, description, display order, draft/published status, optional downloadable media, and SEO title/description. Notices are managed at `/admin/notices` and published at `/notices`.

News taxonomy data is retained for existing public content, but its management and assignment are no longer available in the admin.

## News

News is managed at `/admin/news` and published at `/news` and `/news/{slug}`. Category, tag, and author listing routes use `/news/category/{slug}`, `/news/tag/{slug}`, and `/news/author/{slug}`. The public listing provides search, reusable news item cards, and pagination. News cards and article pages show the image, title, publication date, story, sharing controls, and recent articles without displaying author, category, or tag metadata. The homepage shows the three latest published articles.

The admin News index displays article titles, featured labels, status and selection controls, creation dates, and permission-controlled Edit and Delete actions. Clicking an editable row opens the editor.

The sidebar has a nested News group with Add News and Manage News. News category, tag, and author admin routes, menu entries, permissions, selectors, filters, and detail fields have been removed. Existing taxonomy data and public category/tag/author URLs remain available for previously configured content. System and Users Management remain hidden.

The editor has the same header Save news and permission-controlled Close actions as Resources, with article text, a publish toggle, images stored through the shared upload service, and SEO fields. Taxonomy assignments are no longer accepted from admin requests; existing assignments remain stored for public compatibility. New articles suggest a URL slug and SEO title as the title changes. Both fields are editable, and custom values survive subsequent title edits and validation errors. Existing URLs remain unchanged in the edit form until edited or cleared. An SEO title matching the article title continues to follow title changes; a custom SEO title is preserved.

The server generates a slug and SEO title from the title when their submitted values are empty, including without JavaScript. Slugs are stored in lowercase, must be unique, and accept ASCII letters/numbers separated by single hyphens, with a maximum of 255 characters. Titles that cannot generate a usable slug require a manually entered alias. SEO titles have a 255-character limit. Summary replaces Excerpt in the editor, model, public search snippets, and SEO description fallback; it accepts up to 10,000 characters of rich text. The `2026_10_03_100000_rename_excerpt_to_summary_in_news_table` migration renames the column while preserving existing content; run `php artisan migrate` when deploying this change.

`news.publish` is required to publish or edit a published article. A future publish date holds the article off the public site until that date. Only a currently published article can be featured; the service clears the previous flag in a transaction. Removing an article preserves uploaded image files.

The reference travel blog's package offers, view counter, inquiry panel, and travel-specific links are not part of news. News body and summary, like page content, are rendered as administrator-provided HTML. Sanitize rich text before granting publishing to untrusted editors.

## Pages

Pages store a `slug` and a generated `path`. A top-level page such as `about` has the public URL `/about`. A child page with slug `history` has `/about/history`. The URL slug may be edited directly and defaults to a slug generated from the English title when left blank. Parent changes and slug changes update descendant paths in the page service transaction.

Pages use `draft` and `published` statuses. The admin form exposes this as a toggle, and public routes only resolve published pages. Publishing requires `pages.publish`; route middleware, FormRequests, and the service all enforce authorization. Page title, summary, and body use Spatie Laravel Translatable with English content in JSON columns. Nepali translations are controlled by `config/settings.php` (`nepali`, default `false`). When enabled, the editor shows English and Nepali tabs, Nepali fields are optional and fall back to English on public pages, and public language links use `?lang=en` and `?lang=ne` with the chosen language persisted in the visitor session. When disabled, the editor and public pages use the original single-language English experience. Existing page text is migrated to English in either mode.

Page types are metadata classifications used by the admin list and filter. They do not change public rendering until a dedicated module is introduced for that type.

The page editor presents the English page title and editable URL slug first, followed by the parent and page type selectors. English and Nepali tabs contain the remaining editorial fields. Menu assignment is handled on the menu position pages. A second tab group below the language tabs holds the shared Banner Image, Social Media Image, and SEO fields. Resource and notice category selectors are not part of the page editor. The editor provides formatting, list, link, table, source, and custom block quote section tools; embedded file-manager uploads are not configured. Banner images are shown on public page headers; social media images are exposed as Open Graph sharing metadata. Create and edit screens show the full-width Save/Close bar only after the breadcrumb Save action scrolls above the header, matching the Hall editor behavior. The page index displays nested rows with `--` indentation and persists drag-and-drop ordering among siblings.

The language tabs use locally stored public domain SVG flags of England and Nepal from Wikimedia Commons.

The page index provides row selection and bulk Publish, Unpublish, and Bulk delete actions. Bulk changes validate the selected page IDs and run in one transaction. Publishing uses `pages.publish`; deleting uses `pages.delete`. Each changed page retains its individual activity entry. The table shows an unlabeled order handle and row checkbox, status, page title, and created date beside the row actions. Header and row selection use the shared 22px animated checkbox with white ticks. The header selects all displayed pages (including nested rows), shows a dash for partial selection, and is disabled when the list is empty. Checkbox interactions do not trigger row navigation or drag ordering.

Page content is currently rendered as administrator-provided HTML. A production deployment should sanitize rich text at the input boundary before allowing untrusted editors to publish it.

On the Pages manager, publication status uses filled green/red circles with white check/cross marks, matching the selection control's approximately 22px visual size and 32px click target. Authorized status buttons have animated color, hover enlargement, click compression, and keyboard focus states, with reduced-motion support. Read-only indicators retain the same size without interactive effects; publication endpoints and permissions are unchanged.

Pages row status toggles and bulk Publish/Unpublish use CSRF-protected AJAX requests to the existing endpoints. JSON responses update the shared icon state without navigating or clearing selections. Status controls are disabled while a request is pending; failures preserve the previous icons and show accessible feedback. Standard form submissions still return redirects, and the existing FormRequest/service authorization and activity logging remain in force.

## Media storage

The standalone Media Library screen, sidebar entry, admin routes, `media.manage`, `media.create`, and `media.delete` permissions, and unused `uploads.media` profile have been removed. Each content module continues to validate and attach uploads through its own permissions and forms. `MediaAsset`, `MediaAssetService`, and the bound repository contract remain shared infrastructure for uploaded files; existing asset rows and files are preserved. Run `php artisan admin:permissions-sync` and `php artisan admin:menu-regenerate` when deploying this removal. No database migration is needed.

CMS uploads always use Laravel's local `public` disk and are stored under `storage/app/public/cms`. Run `php artisan storage:link` once per environment. No S3 bucket or cloud storage configuration is required.

`config/settings.php` owns upload sizes, accepted formats and recommended pixel dimensions. `uploads.image` defaults to 5 MB; `uploads.notice_attachment` and `uploads.document` default to 10 MB. These values are in KB (`5120` = 5 MB). Each image field uses an `images` profile and can override `max_size_kb` or `mimes` independently.

The shared upload component reads the same profile as its FormRequest, so the browser file-size checks, accepted extensions, displayed guidance and server validation agree. Home Slides also shows its upload limits. Images keep their uploaded dimensions by default; the recommended dimensions guide selection. Set `enforce_dimensions` to `true` on an image profile to require its exact width and height on both create and update. Set it in `uploads.image` to enable it for all dimensioned image profiles. File-size and image-type checks always run on the server.

| Image profile under `settings.images` | Recommended size |
| --- | --- |
| `homepage_slide` | 1600 × 900 px |
| `page.banner`, `news.banner`, `hall.banner` | 1400 × 630 px |
| `page.social`, `news.social`, `hall.social` | 1200 × 630 px |
| `news.thumbnail` | 600 × 400 px |
| `homepage.gallery` | 1200 × 950 px |
| `homepage.social` | 1200 × 630 px |
| `team_member` | 600 × 600 px |
| `hall.thumbnail` | 600 × 450 px |
| `hall.gallery`, `gallery_photo` | 1200 × 900 px |
| `video_cover` | 1200 × 675 px |
| `site_logo` | 150 × 126 px |
| `contact_officer` | 400 × 400 px |

Logo and contact-officer settings currently accept image URLs and show dimension guidance. Content uploads are handled through their own forms.

For example, adjust one field in `config/settings.php`:

```php
'news' => [
    'thumbnail' => [
        'width' => 600,
        'height' => 400,
        'max_size_kb' => 2048,
        'enforce_dimensions' => true,
    ],
    // Keep the other news profiles here.
],
```

After editing the configuration, run `php artisan config:clear` locally or rebuild `config:cache` on a cached deployment. The PHP/web-server request-body limits must also accommodate the configured uploads, especially when multiple images are uploaded together. `AdminUploadSettingsTest` covers configured limits for every image field, successful uploads, exact dimension checks, MIME validation, authorization, document/media limits and admin guidance.

Team member records are managed through the permission-protected admin directory. Photos use shared media storage on the local public disk; deleting a member preserves the uploaded photo.

## Feature workflow

New content resources must use:

`Route -> FormRequest -> Controller -> Service -> Repository contract -> Eloquent repository -> Model`

Permissions belong in `config/permissions.php`; admin navigation belongs in `config/admin-menu.php`. After changing either catalog, run the corresponding synchronization command.

## Local setup

```bash
php artisan migrate --seed
php artisan storage:link
php artisan admin:permissions-sync
php artisan admin:menu-regenerate
```

## Photo gallery and videos

Homepage Slides has a select-all checkbox for the current paginated page. Its header shows a partial-selection dash when some rows are selected, and is disabled for an empty list. Header and row controls use the shared larger square checkbox with brand-color transitions and white checkmarks; selections continue to drive the existing bulk forms.

The Media sidebar is ordered as Add Video, Manage Video, Add Gallery, Manage Gallery, Add Home Slide, and Manage Home Slide. Homepage Slides uses the same Resources-style index header and table, with Publish, Unpublish, Add Slide, and Bulk delete. The existing homepage-slides.edit permission controls publication status and ordering. Row checkboxes enable bulk forms, and status icons toggle an individual slide through the same status endpoint. Drag handles and up/down buttons save page-scoped ordering, preserve rows on other pages, reject stale lists, and refresh the frontend cache. Bulk workflows are transactional and audited; uploaded media files are preserved. Home Slide forms use separate Slide details and Slide image cards, equal Title/Subtitle columns, a full-width Link URL, a Published toggle, current-image previews, and the shared header/scrolling Save/Close controls. Numeric display order is omitted; new slides append and edits preserve their positions.

Dedicated album and video management now lives under Media. See [Photo gallery and videos](media-catalogues.md) for fields, permissions, upload limits, and setup commands.

## Resources and downloads

Resource Pages now select a resource category or All categories, and automatically display matching published documents. See [Resources and page connections](resources.md).

## Notice Sections

Admin notices now select a Notice Section from the Pages hierarchy (Page Type Notices), including direct assignment to a parent Notice Board. The manager supports descendant-inclusive section filtering. Notice Category administration is retired, but legacy data and public category-based listings remain unchanged during this admin-only phase. See [Notice Sections](notices.md).

## Category editors and ordering

The Team sidebar is ordered as Add Team, Manage Team, Add Team Category, and Manage Team Category. Team Members and Team Categories index screens share the Resources manager layout: title and breadcrumb on the left; Publish, Unpublish, Add Team Member/Add Category, and Bulk delete in the breadcrumb actions slot. Bulk actions enable when rows are selected and submit to the existing team bulk routes. Publish/Unpublish maps to the member `is_active` or category `status` boolean. Each table has five columns, with member designation/category or category slug/member count inside the title cell. Status icons submit a single-record status change; Edit/Delete and the created date share the final cell. The existing team reorder icons remain display-only.

Team Category forms expose the category name, Active toggle, and description in that order. URL slugs continue to be generated and stored by the service from the category name, but the slug is not an editable admin field.

Resource Categories use full-width CKEditor descriptions and Published toggles. Numeric display-order inputs are removed; reorder rows using drag handles or arrow buttons in the manager. Inactive categories hide associated public content. Notice Category administration has been replaced by Notice Sections; legacy notice category visibility remains in force on the public site.

## Lumbini public frontend

Public pages now use Front controllers/services and resources/views/front templates. Site Settings controls homepage and contact/SEO fields. Public menus can add module or external links using menus.manage. See [frontend.md](frontend.md) for module/page connections, publication visibility and setup.
