# Lumbini public website

## References and structure

The Lumbini PHP design at `C:/Users/User/Documents/reference/lumbini/desing-website-lumbini` is the source of truth. Its HTML containers, classes, source CSS, fonts, icons, assets and responsive behavior are copied into the Laravel frontend. Only content bindings, application URLs, library initialization and asset paths are adapted. There is no separately designed layout stylesheet. The travel portal at `D:/2026/humantreks.com` informs the separate Front namespace, frontend assets, reusable Blade sections, sitemap index and schema services. Travel packages and booking workflows are excluded from this institute website.

- `routes/front.php`: public catalogues, detail, search, XML sitemap and robots routes. The CMS catch-all stays after admin routes in `routes/web.php`.
- `app/Http/Controllers/Front`: thin controllers.
- `app/Http/Requests/Front`: public input validation and public-access authorization.
- `app/Services/Frontend`: page orchestration, metadata/JSON-LD, XML sitemaps, HTML sanitation, video providers, images, cache and maintenance.
- `FrontendRepositoryInterface` / `Eloquent/FrontendRepository`: publication-aware queries, pagination, eager loading, search and sitemap records. Registered in AppServiceProvider.
- `resources/views/front/{layouts,partials,components,sections,catalogues,details,pages}`: public templates. Homepage sections follow the original order: banner/notices, trainings, About/services, capacity report, resources, team, hall banner, videos and news.
- `resources/front/css/design.css`: the original `css/main.css`, with Laravel Blade source paths and automatic project-wide utility scanning disabled. Style rules are unchanged.
- `resources/front/css/icons.css`: the original icon definitions from `inc/common-header.php`, with local font paths.
- `resources/front/{js,fonts,images,vendor}`: public JavaScript, copied assets and the reference's Fancybox 6 distribution. Swiper 12 uses the existing npm dependency. Admin assets remain separate.
- `public/front/images`: design assets referenced by the HTML, including the original banner photographs used when Home Slides is empty.

## Admin-to-website mapping

| Admin content | Public behavior |
| --- | --- |
| Site Settings | Identity/contact/footer, province, office hours, hero/About text, four service cards, capacity reports by year, important footer links, TMIS/map/social links, contact officer details and default meta description. Save stays in breadcrumb actions; About uses full-width CKEditor. |
| Home Slides | Published photos and captions in the original fading Swiper banner, with five-second autoplay and pagination. Original design photos are the fallback when no slides are published. |
| Trainings | **Static**, copied from the provided homepage, including its six cards. No training records, API client or admin module is added; API integration is deferred. Links open the supplied TMIS training listing. |
| Pages | Published articles and nested URLs, banners, social images, meta overrides, translated content and visible children. |
| News | `/news`, active category/tag/author routes, details, homepage updates and search. |
| Notices | `/notices`, published category filter, details, attachment/deadline, latest-notice bar and typed Notices pages. |
| Resources | `/resources`, details/downloads, homepage cards and typed Resource pages with their selected category. |
| Team | `/team`, category tabs and numeric member detail URLs. Active members of active categories. Optional public email/phone fields are editable on the existing team form. |
| Halls | `/halls` and slug details, capacity, location, amenities, photos and contact information. Display only; no booking workflow or CTA. |
| Galleries | `/gallery` uses the design's album-grid layout; gallery slug pages use its photo grid with ordered images and Fancybox previews. |
| Videos | `/videos` and detail pages retain the source iframe layout with lazy loading. Homepage previews use the source Fancybox video controls. Other safe URLs remain provider links. |
| Menus | Nested main/footer assignments and ordering. Add link supports module paths, external links and a parent item under `menus.manage`. Unpublished pages and descendants of hidden parents are omitted. |

A published CMS page at a module root such as `resources`, `notices`, `halls`, `gallery`, `team`, `videos`, `news`, `contact` or `sitemap` takes precedence over the default catalogue heading/body. Its page type selects its module. Resource and Notices category selections continue to constrain listings. If a detail slug has no public module record, a published CMS child page at that path can still render. Keep taxonomy paths (`news/category`, `news/tag`, `news/author`) and admin/search/robots/XML paths reserved for their routes.

Contact retains the provided floating-label form and information/map layout. `POST /contact` validates the original fields, limits submissions to three per minute and sends a message to the Site Settings office email. The visitor is a Reply-To address; the sender uses Laravel mail configuration. Missing recipients and transport failures return an error rather than a delivery confirmation. Configure a real mail transport for production. No inquiry database is introduced. Google embed URLs render in the original iframe; other map URLs are links.

FAQ pages use the existing rich-text page body. Homepage service and About defaults are copied from the design in `config/frontend.php` and can be edited in Site Settings. Capacity reports and important links use validated structured settings, with reporting years/add/remove controls in the existing settings screen. Missing report figures show a dash. Managed collections retain the original containers when empty. No demo staff, news, documents, rates, statistics or contact numbers are inserted into the database.

`lang=en|ne` preserves the existing language preference when Nepali is enabled. Selected language gets the appropriate canonical URL. Hreflang links are emitted for Pages with actual Nepali title and body translations; English remains the fallback. Public rich text is sanitized with an allowlist before rendering, removing dangerous tags, event handlers, styles and protocols. Homepage About text is also sanitized when saved.

## SEO and sitemaps

Set `APP_URL` to the actual HTTPS production origin before caching configuration. Canonical and sitemap URLs use this setting instead of a request host.

- All pages receive titles, descriptions, canonical URLs, robots policy, Open Graph, Twitter metadata, Organization/WebSite/WebPage and breadcrumb JSON-LD.
- News uses NewsArticle; halls use Place; team details use Person; galleries use ImageGallery; documents use DigitalDocument/MediaObject.
- VideoObject is emitted only with publication time, a managed thumbnail and a supported embed. Populate a real thumbnail for every video. The existing automatic publication timestamp remains authoritative.
- JSON-LD escapes script-context delimiters; metadata uses escaped Blade output. No invented ratings, offers, prices or travel agency schema is emitted.
- Search and query-filtered/paginated views have `noindex,follow` and retain the main canonical URL. Search remains crawlable so crawlers can read noindex. `/admin` is disallowed.
- `/sitemap.xml` is an automatic index. `/sitemaps/{type}-{chunk}.xml` splits pages, news, notices, resources, halls, galleries, videos and team into configurable 1,000-record chunks.
- XMLWriter escapes absolute URLs. Legacy Pages marked published without a publication timestamp remain visible; future timestamps still stay private. Drafts, future publications, inactive notice/resource/team categories, inactive members, resources without attachments and galleries without photos are omitted.
- Detail `lastmod` uses record update time. Catalogue URLs omit `lastmod` because no reliable aggregate edit time is stored. Request-time synthetic dates and obsolete sitemap pings are not used.
- `/robots.txt` is dynamic and points at the canonical XML index. The previous static file is removed so the web server forwards this request to Laravel.
- XML responses, homepage data, settings and menus use a bounded 60-second cache. Content observers invalidate after commit; menu bulk ordering invalidates explicitly. Scheduling becomes visible within that interval. External database writes must call `frontend:cache-clear`.
- Rendered HTML, session cookies, authenticated responses, CSRF tokens and downloads are not shared through a public response cache.

## Performance

- Public pages load separate CSS and a small JavaScript entry, excluding admin charts, calendar, CKEditor and admin dependencies.
- The original Google Fonts declaration for Plus Jakarta Sans and DM Sans is retained with preconnects. IcoMoon, Swiper and Fancybox assets are served locally through Vite, avoiding runtime script CDN requests. No font family or design token is substituted.
- First hero/detail images are eager with high fetch priority. Other images are lazy and asynchronously decoded. Intrinsic dimensions and reserved aspect ratios stabilize banners, cards, galleries and videos.
- New local JPEG/PNG/WebP uploads receive 480/960/1600px WebP derivatives after commit when GD is available and images are at most 8 megapixels. Images are never upscaled. GIF/SVG, remote disks and larger images retain originals. JPEG orientation is respected. Components use available `srcset`/`sizes` variants with original-file fallback.
- `frontend:images-optimize` processes existing media. Originals and the Media library API are retained. Derivatives use immutable asset-path hashes and are cleaned on model deletion.
- Catalogue queries paginate and eager-load media/categories. Gallery listings load the first photo instead of all photos. Sitemap rows skip media eager loading. A migration adds composite publication indexes.
- Catalogue video/PDF iframe previews are lazy, preserving their source containers. Homepage video/gallery previews open through the original Fancybox library. Hero autoplay is disabled for reduced-motion preferences and single-slide collections. Resource tabs update their Swiper after becoming visible.
- Configure Brotli/gzip, long-lived cache headers for Vite hashed assets and immutable `storage/optimized` files, HTTPS and HTTP/2 or newer in the production server/CDN. Avoid indiscriminate HTML caching.

No PageSpeed score is asserted. Measure deployed mobile LCP/CLS/INP on the homepage, news details, gallery and hall after setup; results depend on hosting, built assets and actual content.

## Deferred setup

Run from the CMS repository. CSS and JavaScript were built with `npm run build` after the user's later explicit build request. Tests, migrations, permission/menu regeneration and Git actions remain deferred.

```powershell
php artisan migrate
php artisan storage:link
php artisan frontend:images-optimize
php artisan frontend:cache-clear
php artisan optimize:clear
```

`migrate` applies the public team contact fields, publication indexes and earlier pending category/module migrations. Use `storage:link` if the link is absent. No new Composer package is required; Swiper is already installed and Fancybox files are copied from the exact distribution referenced by the design. The production Vite manifest includes the public entries. Use `npm run dev` during local development; use `npm run build` again after future template/asset edits.

Existing `settings.manage` and `menus.manage` permissions protect additions; there is no new permission or sidebar item. If earlier category/menu updates have not been installed, also run `php artisan admin:permissions-sync` and `php artisan admin:menu-regenerate` as their module docs describe.

After verifying `.env`, optional deployment optimization is `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`.

`tests/Feature/FrontendIntegrationTest.php` covers publication/category visibility, sitemap splitting and invalid requests, typed resource root pages, safe HTML/schema encoding, query validation, robots, team visibility, provider allowlisting, menu/settings authorization, report validation and contact delivery/failure behavior. Tests were added without execution. PHP/JavaScript and compiled Blade syntax are checked. Public catalogues, details, the homepage and settings fields were rendered with in-memory sample data, without database writes or mail sends. All temporary helper scripts were removed.
