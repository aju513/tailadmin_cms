# Raw API snapshots

These files were collected from the public API under `https://pcgg.lumbini.gov.np/api` on 2026-10-04. They are intentionally kept in the handoff so the full structured dataset is available for import and audit.

Important snapshots:

- `tenant-info.json` and `site-settings.json` — identity and contact settings.
- `navigation.json` — full navigation tree and stable node IDs.
- `introduction.json` — Nepali and English introduction body.
- `sliders.json` — homepage slider records and image metadata.
- `gallery-albums.json` — gallery albums and photos.
- `office-hours.json` and `important-links.json` — footer/contact data.
- `content-*.json` — section-specific content, including IDs, dates, active flags, HTML bodies, and attachments.

The API wraps responses in `{ "success": true, "data": ... }`. Treat raw JSON as UTF-8. Signed cloud-storage URLs are included for traceability but are not the recommended import source after the local assets have been copied.
