# PCGG website content handoff

This folder is a copy-ready content package based on the public site:

- Source: <https://pcgg.lumbini.gov.np/>
- Collected: 2026-10-04
- Primary locale: Nepali (`ne`)
- Secondary locale: English (`en`)
- Tenant: प्रदेश सुशासन केन्द्र नेपालगञ्ज, बाँके

## Folder map

- `site/` — identity, homepage copy, navigation, contact details, office hours, and important links.
- `content/` — content-group notes and the records most useful for seeding the CMS.
- `media/images/` — downloaded logos, homepage sliders, gallery photos, and image attachments.
- `media/documents/` — downloaded document attachments that were available during collection.
- `raw/` — UTF-8 JSON snapshots from the public API. These are the source of truth for complete records, IDs, dates, file metadata, and any entries not repeated in the Markdown summaries.

## Suggested import order

1. Create the tenant/site identity from `site/identity.md`.
2. Import the navigation tree from `site/navigation.md`.
3. Add the introduction and homepage blocks from `site/about.md` and `site/homepage.md`.
4. Upload `media/images/` and use `media/manifest.md` for captions and placement.
5. Import legal documents, downloads, and notices using `content/catalog.md` plus the matching JSON in `raw/`.
6. Add contact details, office hours, map, and external links from `site/contact.md` and `site/important-links.md`.

## Important notes

- The live API currently returns an empty active news feed and an empty services list, while the navigation contains the complete section structure. Keep those sections available in the CMS but do not invent records.
- Several content records are retained in the raw snapshots with `is_active: false`; these are historical records and should only be published if desired.
- Original remote asset URLs are signed URLs and expire. Use the local files in `media/` for copying.
- All Markdown and JSON files in this package are UTF-8. Preserve Nepali Unicode when importing.
- The current site settings identify the heading as “Lumbini Research and Training Institute / लुम्बिनी अनुसन्धान तथा प्रशिक्षण प्रतिष्ठान”, while the tenant identity remains PCGG. This distinction is preserved in `site/identity.md`.
