# Analytics Dashboard

The dashboard follows the reference admin's layout: active, new, and returning visitor cards; country page-view and device active-user charts; top 15 pages; and top 15 Google web search queries. It uses TailAdmin cards, tokens, responsive layouts, dark mode, and locally bundled ApexCharts. Chart data is also available as text. Search queries include clicks, impressions, CTR percentage, and average position.

`admin.dashboard` requires authentication and `dashboard.view`. `DashboardRequest` authorizes and validates the optional `days` filter (7, 30, or 90; default 90). The controller delegates to `DashboardService`, which fetches Google reports through `GoogleReportingService` and stores snapshots through `DashboardRepositoryInterface`, its bound Eloquent implementation, and `DashboardReport`.

Reports cover the selected number of complete days ending yesterday. Active users are period totals, not real-time users. New and returning visitors are mapped by Google's `newVsReturning` dimension values, not row order. Country charts show page views rather than sessions. Search Console requests final web-search data; recent dates may be incomplete due to Google processing delays. Country results and page/query tables are limited to the top 15.

## Configuration

Set `ANALYTICS_PROPERTY_ID` to the numeric GA4 property ID and `SEARCH_CONSOLE_SITE_URL` to the exact Search Console property (for example `sc-domain:your-domain.com` or `https://your-domain.com/`). Store service-account JSON files in:

- `storage/app/analytics/service-account-credentials.json`
- `storage/app/analytics/search-console-credentials.json`

Both files and `.env` are excluded from Git and are never sent to the browser. The reference credentials and reporting identifiers were copied locally for initial setup. Its Search Console identifier is `sc-domain:example.com`, which needs replacement with an accessible property to load real queries. Enable the Analytics Data API and Search Console API and grant the service accounts read access to their respective properties. Replace these local files and environment variables when the new project's properties are ready, then run `php artisan config:clear` (or rebuild the production configuration cache).

The initial read-only connection check succeeded for the copied Analytics property. Search Console returned HTTP 403 for the copied `example.com` property. The dashboard supports this partial configuration and continues showing Analytics reports.

Google service-account OAuth uses signed RS256 JWT assertions and read-only scopes. Tokens are cached server-side for 50 minutes. Successful report snapshots are cached in the database for 15 minutes; failures for one minute. Cache identities include provider, property, credential fingerprint, and date range, so configuration changes cannot reuse another property's reports. Each provider fails independently. Missing settings, inaccessible properties, invalid credentials, and network failures show unavailable states; successful empty reports show no-data states. Exception payloads are neither logged nor rendered because they can contain tokens. No report refresh endpoint or new permission is needed.

Run `php artisan migrate` before opening the dashboard. Snapshot records contain reporting data only, not credentials. Expired records can be removed periodically with an operational database cleanup if long-term accumulation becomes material. Tests disable real reporting identifiers and prevent live HTTP calls.

API references: [GA4 batch reports](https://developers.google.com/analytics/devguides/reporting/data/v1/rest/v1beta/properties/batchRunReports), [Search Analytics queries](https://developers.google.com/webmaster-tools/v1/searchanalytics/query), and [service-account OAuth](https://developers.google.com/identity/protocols/oauth2/service-account).
