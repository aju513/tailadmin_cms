# Lumbini public website

## References and structure

The Lumbini PHP design at `D:/websitepcgg` is the source of truth. Its HTML containers, classes, source CSS, fonts, icons, assets and responsive behavior are copied into the Laravel frontend. Content bindings, application URLs, library initialization, asset paths and small accessibility corrections are adapted. There is no separately designed layout stylesheet. The travel portal at `D:/2026/humantreks.com` informs the separate Front namespace, frontend assets, reusable Blade sections, sitemap index and schema services. Travel packages and booking workflows are excluded from this institute website.

- `routes/front.php`: public catalogues, detail, search, XML sitemap and robots routes, followed by the CMS catch-all. `routes/web.php` loads protected routes from `routes/admin.php` first.
- `app/Http/Controllers/Front`: thin controllers.
- `app/Http/Requests/Front`: public input validation and public-access authorization.
- `app/Services/Frontend`: page orchestration, metadata/JSON-LD, XML sitemaps, HTML sanitation, video providers, images, cache and maintenance.
- `FrontendLayoutService` and `Http/ViewComposers/Front/LayoutComposer`: common settings, menus and metadata for the public shell. Header/footer partials, branding, fallback navigation and default office hours are configured in `config/frontend.php`; saved CMS settings and menus take precedence. See [folder-structure.md](folder-structure.md).
- `FrontendRepositoryInterface` / `Eloquent/FrontendRepository`: publication-aware queries, pagination, eager loading, search and sitemap records. Registered in AppServiceProvider.
- `resources/views/front/{layouts,partials,components,sections,catalogues,details,pages,emails}`: public templates. Homepage sections follow the original order: banner/notices, trainings, About/services, capacity report, resources, team, hall banner, videos and news.
- `resources/front/css/design.css`: the reference's `css/main.css`, with Laravel Blade source paths and automatic project-wide utility scanning disabled. Adaptations retain keyboard-accessible nested menus, readable catalogue descriptions on dark cards and the direct-child resource button selector.
- `resources/front/css/icons.css`: the original icon definitions from `inc/common-header.php`, with local font paths.
- `resources/front/{js,fonts,images,vendor}`: public JavaScript, copied assets and the reference's Fancybox 6 distribution, with an ESM adapter so Vite imports the viewer reliably. Swiper 12 uses the existing npm dependency. Admin assets remain separate.
- `public/front/images`: design assets referenced by the HTML. The top banner uses uploaded Home Slide images.

Footer contact details use the shared `footer__contact-item-content` styling and white icons and text on the blue background. Office Hours retains separate seasonal lines, a spaced heading, and compact line height.

## Admin-to-website mapping

The October 2026 sync includes the reference's latest working files, including its uncommitted design edits: the tertiary blue token, training/resource cards and badges, white action buttons, About and hall backgrounds, neutral capacity-report text, revised resource background and capacity/organization icons. Homepage resource cards use the compact training-card layout and show one, two or three slides at the reference's mobile, 640px and 1024px breakpoints. Resource catalogue cards omit category badges while retaining category tabs, descriptions, publication metadata and named detail links. The hall action continues to open the existing public hall catalogue. Updated artwork is copied into both `resources/front/images` for Vite and `public/front/images` for Blade asset URLs.

| Admin content | Public behavior |
| --- | --- |
| Site Settings | Identity/contact/footer, static hero heading and description, province, office hours, four service cards, capacity reports by year, TMIS/map/social links, contact officer details and default meta description. Save stays in breadcrumb actions. |
| Homepage | Welcome/About text, optional subtitle, gallery main image and thumbnail slider, social sharing image, and homepage SEO. The editor is under Pages and imports existing About content and displayed album images. See [Homepage content](homepage.md). |
| Home Slides | Published images and caption titles in the original fading Swiper banner, in the admin's saved order. The left heading, description, Apply Roaster and Explore Trainings buttons stay static. Five-second autoplay and pagination apply only to multiple slides; reduced-motion preferences disable autoplay. No stock-photo fallback is shown when slides are empty. |
| Trainings | Up to six ongoing routines from the public TIMS API, preserving the supplied card design. Cards show training information and link to the routine's TIMS detail page. |
| Pages | Published articles and nested URLs, banners, social images, meta overrides, translated content and visible children. |
| News | `/news`, active category/tag/author routes, reusable news item cards, details, homepage updates and search. Public presentation omits author, category and tag metadata. |
| Notices | `/notices`, published category filter, details, attachment/deadline, latest-notice bar and typed Notices pages. |
| Resources | `/resources`, details/downloads, homepage cards and typed Resource pages with their selected category. |
| Team | `/team`, category filter buttons and numeric member detail URLs. Filters use filled blue for the selected category and gray backgrounds for other options, with wrapping on small screens and visible keyboard focus. Active members of active categories. Optional public email/phone fields are editable on the existing team form. |
| Halls | `/halls` and slug details, capacity, location, amenities, photos and contact information. Display only; no booking workflow or CTA. |
| Galleries | `/gallery` uses the design's album-grid layout; gallery slug pages use its photo grid with ordered images and Fancybox previews. |
| Videos | `/videos`, typed Videos pages and detail pages use reusable video cards with Fancybox previews. YouTube/Vimeo URLs are normalized by the provider allowlist; other safe URLs remain external links. |
| Menus | Nested main/footer assignments and ordering. Add link supports module paths, external links and a parent item under `menus.manage`. Unpublished pages and descendants of hidden parents are omitted. |
| Important Links | A flat list of full HTTP/HTTPS external URLs managed under Menus. Links open in a new tab with `noopener noreferrer`; page assignments, internal URLs and parent items are excluded. |

A published CMS page at a module root such as `resources`, `notices`, `halls`, `gallery`, `team`, `videos`, `news`, `contact` or `sitemap` takes precedence over the default catalogue heading/body. Its page type selects its module. Resource and Notices category selections continue to constrain listings. If a detail slug has no public module record, a published CMS child page at that path can still render. Keep taxonomy paths (`news/category`, `news/tag`, `news/author`) and admin/search/robots/XML paths reserved for their routes.

Contact and typed Contact Us pages show the saved organization and contact details on the left and office location on the right. There is no contact form. The map renders only for a configured Google Maps embed URL; other safe map URLs become links, and an unset map is omitted. The existing validated, throttled `POST /contact` endpoint remains available for compatibility. No inquiry database is introduced.

FAQ pages use the existing rich-text page body. Homepage service defaults are copied from the design in `config/frontend.php` and can be edited in Site Settings; About content is managed in Homepage. [Capacity Reports](capacity-reports.md) manages the contribution cards as editable key/value rows for each fiscal year, with year choices in `config/settings.php`. Saved years appear newest first on the homepage; blank figures display a dash and recorded zeros remain zero. Important Links are managed under Menus; see [Permissions and Menus](menus-permissions.md) for URL validation and migration details. Homepage Resources, Team, News, and Videos sections are omitted completely when their public collections have no displayable content. Videos require at least one supported embed URL in the loaded collection. These conditions use the existing publication, scheduling, active-category/member, and resource-attachment filters without extra database queries. No demo staff, news, documents, rates, statistics or contact numbers are inserted into the database.

Public flags use the official GTranslate widget with English source HTML and English/Nepali choices. `FRONTEND_TRANSLATION_MODE=gtranslate` is the default, independent of the admin Nepali editing toggle. The existing flag controls trigger a hidden widget, wait for asynchronous loading, and preserve the preference in browser storage. `lang=en|ne` can select the initial preference without changing canonical URLs. Switching to English restores the source language. If the widget cannot load, the controls announce that translation is unavailable. Input controls and language controls are excluded from translation. Allow the official widget and Google's translation resources through any deployment CSP. Automatic translation needs no API key; browser verification on the production network remains appropriate.

Saved bilingual CMS values are preserved. Set `FRONTEND_TRANSLATION_MODE=manual` to use the previous server-rendered `lang=en|ne` behavior and admin Nepali toggle. In manual mode selected language gets the appropriate canonical URL, and hreflang links are emitted for Pages with actual Nepali title/body translations. Public rich text is sanitized with an allowlist before rendering, removing dangerous tags, event handlers, styles and protocols. Homepage About text is also sanitized when saved.

`GET /search?q=term` searches published pages, news, notices, resources, halls, galleries, videos and active team members, including descriptive text. Each group paginates twelve results independently through its `{type}_page` parameter and retains the query. Hidden, scheduled and inactive content follows the existing publication filters. Video and album catalogues also accept `q` (or the compatible `search` alias) and paginate their filtered results. Typed Gallery/Videos CMS pages use the same collections and templates as the root catalogues.

## Homepage banner

The homepage “Professional Spaces for Trainings & Events” section uses the original `public/front/images/dynamic/book-a-hall.jpg` artwork. Its image stays fixed when halls are added or edited; hall catalogue and detail pages continue to use each hall's saved media.

The homepage loads up to twelve published Home Slides with attached media through the existing frontend service and repository. Each slide supplies only its image and escaped caption title. The left side retains the original heading and description from Site Settings with configuration fallbacks, plus the original Apply Roaster and Explore Trainings buttons and destinations. Changing slides does not change this copy or these buttons. Subtitle and Link URL are absent from Home Slide forms and public banner data; legacy database values are retained but ignored. The original split banner layout is retained, and pagination sits above the desktop notice bar. Autoplay pauses while hovering or focusing within the banner. A single slide has no autoplay or pagination; an empty catalogue omits the hero and keeps the notice bar without stock images. Admin saves, publication changes and deletions use the existing cache observer; index reordering invalidates the frontend cache immediately.

## Public training integration

The homepage requests `GET https://tmis.pcgg.lumbini.gov.np/api/v1/trainings?status=ongoing&limit=6` server-side, without authentication. Set `TIMS_BASE_URL` to change the origin; the default is the production TIMS host. `TIMS_API_ENABLED=false` hides the section. The endpoint must be deployed in TIMS before live records can appear.

The existing public homepage Route/FormRequest/Controller calls FrontendService, then PublicTrainingService and the bound PublicTrainingRepositoryInterface / Http/TimsTrainingRepository. This external read uses an HTTP repository instead of Eloquent because TIMS owns the records; no CMS training table, migration, write operation, permission or admin manager is added. Blade receives normalized public card data and never calls the remote API.

Cards use the API's `routine_id` for `/trainings/{routine_id}` links. Names, formatted descriptions, delivery type, department, date range and venue are shown when provided; training codes, duration and seat availability are omitted. Descriptions retain paragraphs, emphasis, lists and safe links through the shared SafeHtml sanitizer, without truncating HTML at a character boundary. Description styling preserves readable light text and list spacing on the blue cards. All other API text is escaped in Blade. Nepali pages prefer the supplied BS dates. Missing optional fields are omitted, and invalid/non-ongoing records are skipped. The normalized training cache uses a versioned key so a rendering change does not reuse old plain-text card data.

Successful responses are cached independently of CMS homepage data for five minutes. HTTP errors, timeouts and malformed responses show a temporary-unavailable message and are cached for one minute before retrying. A valid empty response shows No ongoing trainings. Requests use a two-second connection timeout and three-second total timeout. Response bodies and error details are never exposed in the page. Cache keys include the API origin and card limit. Feature tests disable live API calls globally and exercise the integration with HTTP fakes.

After changing deployment environment values, refresh cached configuration with `php artisan config:cache`. No API key or browser CORS setup is needed.

## SEO and sitemaps

Public search uses the normal/sticky header buttons and the mobile search link. Header popups have labelled native controls, focus the query field when opened, and close on Escape, Close, outside click, or keyboard focus leaving the popup. Search icons are centered in fixed-width buttons with reserved input padding. The results page retains the query and language. Every whitespace-separated search word must match a searchable field in a published record; words can occur in different fields. `%` and `_` are searched literally, and blank queries show a prompt. Existing publication, scheduling, and category visibility rules apply across all searched modules.

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

Header and mobile menus follow the current Pages parent/child hierarchy, including after a page is moved without being reassigned. Only assigned pages appear; a missing menu ancestor connects to the nearest assigned ancestor. Hidden, draft, or scheduled assigned parents suppress their descendants. Manual links retain their menu parent. At every depth, parent labels and icons form a single button that toggles their submenu on click, Enter or Space instead of navigating to the parent's URL. Items without children remain normal links, including external links. Desktop nested menus use the button's expanded state; closing a top-level dropdown also resets its nested dropdowns. Navbar labels and dropdown items use the primary color on hover on desktop and mobile. At desktop widths (1024px and wider), scrolling beyond 300px applies the `sticky` class to `.header__menu`, matching the fixed-navbar and compact-logo CSS selectors. The mobile navbar becomes sticky beyond 120px; scrolling back or crossing the desktop breakpoint resets the relevant classes.
- Rendered HTML, session cookies, authenticated responses, CSRF tokens and downloads are not shared through a public response cache.

## Performance

- Public pages load separate CSS and a small JavaScript entry, excluding admin charts, calendar, CKEditor and admin dependencies.
- The original Google Fonts declaration for Plus Jakarta Sans and DM Sans is retained with preconnects. IcoMoon, Swiper and Fancybox assets are served locally through Vite, avoiding runtime script CDN requests. No font family or design token is substituted.
- First hero/detail images are eager with high fetch priority. Other images are lazy and asynchronously decoded. Intrinsic dimensions and reserved aspect ratios stabilize banners, cards, galleries and videos.
- The shared No image placeholder contains only the existing site emblem, without added names or text. `placeholder-logo.svg` uses a transparent 300x252px logo export at a 150x126px intrinsic size. Missing-image cards, banners, gallery items, and the Homepage About image center the logo with `scale-down` and 24px padding on the existing neutral background, preserving its proportions and the image area's layout. Source and public assets are kept together under their respective `front/images` directories.
- New local JPEG/PNG/WebP uploads receive 480/960/1600px WebP derivatives after commit when GD is available and images are at most 8 megapixels. Images are never upscaled. GIF/SVG, remote disks and larger images retain originals. JPEG orientation is respected. Components use available `srcset`/`sizes` variants with original-file fallback.
- The public disk uses `/storage`; `MediaAsset::url()` and optimized image URLs resolve it against the current request origin, preserving its scheme, host, port and application base path. This prevents a stale `APP_URL` from directing uploaded images or attachments to another server. Absolute URLs configured for remote/CDN disks remain unchanged. `APP_URL` still controls canonical and sitemap URLs. `public/storage` must point to `storage/app/public`; run `php artisan storage:link` if that link is absent and clear/rebuild cached configuration after updating filesystem settings.
- `frontend:images-optimize` processes existing media. Originals and shared media storage are retained. Derivatives use immutable asset-path hashes and are cleaned on model deletion.
- Catalogue queries paginate and eager-load media/categories. Gallery listings load the first photo instead of all photos. Sitemap rows skip media eager loading. A migration adds composite publication indexes.
- Catalogue video/PDF iframe previews are lazy, preserving their source containers. Homepage video/gallery previews open through the original Fancybox library. Hero autoplay is disabled for reduced-motion preferences and single-slide collections. Resource tabs update their Swiper after becoming visible.
- Configure Brotli/gzip, long-lived cache headers for Vite hashed assets and immutable `storage/optimized` files, HTTPS and HTTP/2 or newer in the production server/CDN. Avoid indiscriminate HTML caching.

No PageSpeed score is asserted. Measure deployed mobile LCP/CLS/INP on the homepage, news details, gallery and hall after setup; results depend on hosting, built assets and actual content.

## Setup and verification

Run from the CMS repository. Build both asset manifests before feature tests or deployment.

```powershell
php artisan migrate
php artisan storage:link
php artisan frontend:images-optimize
php artisan frontend:cache-clear
php artisan optimize:clear
```

`migrate` applies the public team contact fields, publication indexes and earlier pending category/module migrations. Use `storage:link` if the link is absent. No new Composer package is required; Swiper is already installed and Fancybox files are copied from the exact distribution referenced by the design. `npm run build` creates separate manifests under `public/build/front` and `public/build/admin`. `npm run dev` starts both development servers; `dev:front` and `dev:admin` start individual servers. Rebuild after template/asset edits.

Existing `settings.manage` and `menus.manage` permissions protect additions; there is no new permission or sidebar item. If earlier category/menu updates have not been installed, also run `php artisan admin:permissions-sync` and `php artisan admin:menu-regenerate` as their module docs describe.

After verifying `.env`, optional deployment optimization is `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`.

`tests/Feature/FrontendIntegrationTest.php` covers publication/category visibility, sitemap splitting and invalid requests, typed resource root pages, safe HTML/schema encoding, query validation, robots, team visibility, provider allowlisting, menu/settings authorization, report validation and contact delivery/failure behavior. `ApplicationStructureTest.php` adds header/footer configuration, CMS precedence, layout composition, admin route protection and independent production/development assets. Run `php artisan test`, `php vendor/bin/pint --test` and `npm run build` before handoff.
