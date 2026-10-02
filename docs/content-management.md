# Content management

The CMS provides a small government website content foundation. News articles are a dedicated resource, separate from hierarchical pages.

## Implemented modules

All selectable admin content indexes now use the same animated 22px white-tick checkboxes and select-all controls. News, Notices, Resources, their categories, Team Members/Categories, Halls, Homepage Slides, Gallery, Videos, and Users share AJAX row and bulk status updates, loading guards, error feedback, and server-side authorization. Select-all affects only displayed records; nested Resource Category controls share the header's selection state. Menu item tables reuse the checkbox styling and keep their existing selection and reorder logic. Existing labels, status values, permission gates, and non-JavaScript redirect responses are preserved.

- Hall catalogue with seating capacity, NPR rates and rate units, operational availability, amenities, bilingual content, contacts, images/gallery, and SEO. See [Halls and bookings](halls-and-bookings.md) for the detailed implementation and booking roadmap. Date reservations and public booking forms are planned separately.

- Pages with nested parent/child hierarchy, generated public paths, editable slugs, drag-and-drop ordering, and validated page types: Article, News, Notices, Resource, Team, Contact Us, Sitemap, Hall, FAQs, Videos, and Gallery.
- Local media library for images and office documents.
- Menu positions default to Main Menu and Footer Menu; additional database-defined locations appear under Dynamic Menus. The main position retains the internal `header` location for existing routes and data. Each position has a searchable page-assignment control and an assigned-item manager with serial numbers, sibling-level drag ordering, row selection, single deletion, and bulk deletion. The assignment control remains available when every page is already assigned, and there is no separate menu-item creation or edit screen. Pages retain their hierarchy, dragging only changes order within the same parent, and surviving descendants reconnect to their nearest assigned ancestor after deletion. Main and footer links render recursively on the public site.
- Site identity and contact settings.
- Homepage slides.
- Team member directory with names, designations, biographies, photos, and active status.
- Existing news category, tag, and author records remain available to the public compatibility layer.
- News articles with draft/published status, a featured article, thumbnail/banner/social images, SEO fields, and public listings.
- Notices with title, generated slug, description, display order, draft/published status, optional downloadable media, and SEO title/description. Notices are managed at `/admin/notices` and published at `/notices`.

News taxonomy data is retained for existing public content, but its management and assignment are no longer available in the admin.

## News

News is managed at `/admin/news` and published at `/news` and `/news/{slug}`. Category, tag, and author listing routes use `/news/category/{slug}`, `/news/tag/{slug}`, and `/news/author/{slug}`. The public listing provides a featured story, category selector, search, article cards, and pagination. Article pages show the author, publication date, tags, banner, story, and recent articles. The homepage shows the three latest published articles.

The sidebar has a nested News group with Add News and Manage News. News category, tag, and author admin routes, menu entries, permissions, selectors, filters, and detail fields have been removed. Existing taxonomy data and public category/tag/author URLs remain available for previously configured content. System and Users Management remain hidden.

The editor follows the existing page form pattern with article text, a publish toggle, images stored through the media library, and SEO fields. Taxonomy assignments are no longer accepted from admin requests; existing assignments remain stored for public compatibility. The URL slug is generated from the title if left empty and must be unique. `news.publish` is required to publish or edit a published article. A future publish date holds the article off the public site until that date. Only a currently published article can be featured; the service clears the previous flag in a transaction. Removing an article leaves media library assets available for reuse.

The reference travel blog's package offers, view counter, inquiry panel, and travel-specific links are not part of news. News body and excerpt, like page content, are rendered as administrator-provided HTML. Sanitize rich text before granting publishing to untrusted editors.

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


CMS uploads always use Laravel's local `public` disk and are stored under `storage/app/public/cms`. Run `php artisan storage:link` once per environment. No S3 bucket or cloud storage configuration is required.

Allowed uploads are validated as images, PDFs, and common office documents with a 10 MB media-library limit. Homepage slide images and team member photos have a 5 MB limit.

Team member records are managed through the permission-protected admin directory. Photos use the shared media library and local public disk; deleting a member keeps the photo available in the library for reuse.

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

Homepage Slides uses the same Resources-style index header and table, with Publish, Unpublish, Add Slide, and Bulk delete. The existing homepage-slides.edit permission controls publication status. Row checkboxes enable bulk forms, and status icons toggle an individual slide through the same status endpoint. Bulk workflows are transactional and audited; uploaded media remains in the Media Library. Reorder icons remain display-only.

Dedicated album and video management now lives under Media. See [Photo gallery and videos](media-catalogues.md) for fields, permissions, upload limits, and setup commands.

## Resources and downloads

Resource Pages now select a resource category or All categories, and automatically display matching published documents. See [Resources and page connections](resources.md).

## Notice types

Notices now use editable categories and optional deadlines, and Notices Pages select a category or All categories. See [Notice types and page connections](notices.md).

## Category editors and ordering

Team Members and Team Categories index screens share the Resources manager layout: title and breadcrumb on the left; Publish, Unpublish, Add Team Member/Add Category, and Bulk delete in the breadcrumb actions slot. Bulk actions enable when rows are selected and submit to the existing team bulk routes. Publish/Unpublish maps to the member `is_active` or category `status` boolean. Each table has five columns, with member designation/category or category slug/member count inside the title cell. Status icons submit a single-record status change; Edit/Delete and the created date share the final cell. The existing team reorder icons remain display-only.

Resource Categories and Notice Categories use full-width CKEditor descriptions and Published toggles. Numeric display-order inputs are removed; reorder rows using drag handles or arrow buttons in the manager. Inactive categories hide associated public content.

## Lumbini public frontend

Public pages now use Front controllers/services and resources/views/front templates. Site Settings controls homepage and contact/SEO fields. Public menus can add module or external links using menus.manage. See [frontend.md](frontend.md) for module/page connections, publication visibility and setup.
