# Phase 09 — SEO completion, analytics & the dashboard home

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/dashboard/02-dashboard.jpg`.

## 1. SEO (site-wide)
- `SeoManager` finalised: title (`page title | suffix`), description, canonical (current locale URL without query
  except allowed params), `hreflang` ar/en + `x-default` (ar) using translated slugs (omit a locale whose translation is
  missing → that page has no English alternate), Open Graph + Twitter cards (image = OG asset → cover → default),
  robots meta (noindex for search results, previews, paginated filters beyond page 1 if needed).
- JSON-LD via `SchemaBuilder`: `GeneralContractor` (name, logo, address Mussafah Abu Dhabi, geo, phones, email,
  opening hours, sameAs socials, identifier licence no.) on all pages; `WebSite` + `SearchAction`; `BreadcrumbList`;
  `Service`; `Article`; `FAQPage` (service FAQs & FAQ page); `JobPosting` (with validThrough, employmentType,
  hiringOrganization, jobLocation AE); `ImageObject` for project galleries.
- Sitemap: `/sitemap.xml` index → per type sitemaps with `xhtml:link` alternates; regenerated on content change
  (observer, debounced to once per request) and on demand from the dashboard; respects `show_in_sitemap`/noindex.
- `robots.txt` dynamic from settings (+ sitemap URL). Breadcrumb UI everywhere.
- Image SEO: alt required, meaningful file names on upload (slugified), width/height present.

## 2. Tracking & analytics
- Inject GA4/GTM/Meta Pixel from TrackingSettings only in production and only when IDs exist; custom head/body scripts.
- `track.js`: sends `navigator.sendBeacon('/t', {type, path, locale})` for: page view (once per page load),
  WhatsApp click, call click, email click. Endpoint `track` (POST, CSRF-exempt, throttle 60/min/IP, bots ignored by UA
  check) → increments `page_views_daily` or inserts `site_events`. Works with full-page cache because it is client-side.
- Also push the same events to GA4/Meta when present (`gtag`/`fbq` if defined).
- First-touch UTM cookie (30 days) read by forms in phase 08 (verify integration).

## 3. Dashboard home (`02-dashboard.jpg`)
Period switch (7 / 30 / 90 days, vs previous period). Widgets (Livewire, cached 5 min, lazy-loaded with skeletons):
- Stat cards: quote requests, won + conversion rate, WhatsApp/call clicks, site visits — each with delta vs previous period.
- Chart (Chart.js): requests per day (bars; current week highlighted) with toggle to visits.
- Requests by service (horizontal bars).
- Latest quote requests table (status badges, open link).
- "Needs attention": unassigned new requests, unread messages, images missing alt (ar/en), content missing English,
  certificates expiring within 90 days (licence 11/05/2029, classification 25/03/2028 from seed).
- Top pages (views) for the period.

## Tests
SEO tags per page type (canonical, hreflang, OG), JSON-LD validity (decode + required keys), sitemap contains published
items with alternates and excludes drafts/noindex, robots.txt, beacon endpoint (throttle, increments), dashboard widgets
numbers with seeded data.

## Acceptance checklist
- [ ] Rich results test-ready JSON-LD on home/service/project/article/job/FAQ
- [ ] Sitemap & robots correct in both locales
- [ ] Dashboard home shows real numbers; tracking works with response cache on
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-09: seo, analytics, dashboard home`
