# Content management

The CMS provides a small government website content foundation. Blog/news is intentionally not part of this implementation.

## Implemented modules

- Pages with nested parent/child hierarchy, generated public paths, drag-and-drop ordering, automatic slugs, and validated page types for Standard Page, Article, Contact, Sitemap, Team, Photo Gallery, Video, FAQ, and Legal Document classifications.
- Local media library for images and office documents.
- Dynamic menu positions with separate Header Menu and Footer Menu managers; any additional database-defined locations are grouped as Dynamic Menus. Pages can be multi-selected and assigned to a menu in one operation. Header and footer links are rendered from their active menu records on the public site.
- Site identity and contact settings.
- Homepage slides.
- Reusable categories, tags, and author records for future content modules.

Categories, tags, and authors are independent resources. They do not publish a blog by themselves and can be attached to future content types without introducing travel-specific concepts.

## Pages

Pages store a `slug` and a generated `path`. A top-level page such as `about` has the public URL `/about`. A child page with slug `history` has `/about/history`. Parent changes and slug changes update descendant paths in the page service transaction.

Pages use `draft` and `published` statuses. The admin form exposes this as a toggle, and public routes only resolve published pages. Publishing requires `pages.publish`; route middleware, FormRequests, and the service all enforce authorization.

Page types are metadata classifications used by the admin list and filter. They do not change public rendering until a dedicated module is introduced for that type.

The page editor includes rich-text summary and content fields, plus separate local uploads for banner and social media images. Banner images are shown on public page headers; social media images are exposed as Open Graph sharing metadata. The page index displays nested rows with `--` indentation and persists drag-and-drop ordering among siblings.

Page content is currently rendered as administrator-provided HTML. A production deployment should sanitize rich text at the input boundary before allowing untrusted editors to publish it.

## Media storage

CMS uploads always use Laravel's local `public` disk and are stored under `storage/app/public/cms`. Run `php artisan storage:link` once per environment. No S3 bucket or cloud storage configuration is required.

Allowed uploads are validated as images, PDFs, and common office documents with a 10 MB media-library limit. Homepage slide images have a 5 MB limit.

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
