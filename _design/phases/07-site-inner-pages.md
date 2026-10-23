# Phase 07 — Website inner pages

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/website/04-services.jpg`,
`05-service-detail.jpg`, `06-projects.jpg`, `07-project-detail.jpg`, `08-custom-page-about.jpg`, `12-careers.jpg`,
`13-articles.jpg`, `14-mobile.jpg`. Apply §6 type scale (smaller than the screens) and keep the clean solid style on
inner pages; glass only on sliders (related items) and controls.
Goal: every remaining public page except the quote/contact/application forms (phase 08).

## Shared
- Inner page header partial: breadcrumbs (also as JSON-LD later), page title `.t-page`, lead; paper background.
- Pages render blocks through `site/blocks/{type}.blade.php` (one file per block type).
- Related content sliders reuse `cardSlider`.
- Every page sets SEO data through `SeoManager` (title/description/OG/canonical/hreflang wired in phase 09, but pass the
  data now).

## 1. Custom page (`pages.show`) — `08-custom-page-about.jpg`
Structure: header (title + description), **main media** by type (image with optional mobile source / uploaded video
with poster and controls / YouTube-Vimeo lite embed that loads the iframe only after click / PDF: inline cover + "open"
& "download" buttons), then blocks, then **attachments** in order Files → Videos → Images with each group's title and
display mode: files as a download list (type badge "PDF", title, size, download link), videos as a grid of posters with
play overlay opening a lightbox player, images as a grid/masonry with a lightweight lightbox (keyboard, swipe, captions).
Draft preview via signed URL shows a "preview" ribbon.

## 2. Services
- Index (`04-services.jpg`): grid 2 columns desktop / 1 mobile: image, title, summary, "تفاصيل الخدمة" link. Services
  without image show a clean text card (no placeholder image).
- Show (`05-service-detail.jpg`): header; main column = cover image, blocks, scope list, stages (numbered),
  gallery (grid + lightbox, "+N" tile), FAQ accordion (accessible `<details>` or Alpine with aria); sticky side column
  = navy "request this service" box (link to quote page with `?service=` preselected) + WhatsApp + other services list
  (current highlighted); related projects glass slider at the bottom.

## 3. Projects
- Index (`06-projects.jpg`) as **Livewire** `ProjectsIndex`: category filter tabs with counts, service filter (select),
  sort (newest / oldest / featured), search, "load more" (9 per step) — URL query synced (`#[Url]`), no reloads,
  skeleton while loading; category route `projects.category` preselects the filter (and has its own SEO).
- Show (`07-project-detail.jpg`): gallery (main image + thumbs, swipe on mobile, lightbox), facts list (service,
  type/category, location, area, duration, status/year, client if allowed), CTA "اطلب مشروعًا مشابهًا", description
  blocks, scope list, before/after comparison slider (draggable handle, keyboard accessible) when pairs exist,
  videos, related projects slider (same category/service).

## 4. Articles
- Index (`13-articles.jpg`) as **Livewire** `PostsIndex`: category tabs, search (debounced), featured post on top,
  grid of posts, pagination (no reload). Category route preselects.
- Show: cover, meta line (category, date, reading time), prose body, share links (copy link, WhatsApp, LinkedIn, X —
  text labels), related posts slider, CTA band.

## 5. Careers
- Index (`12-careers.jpg`): open jobs list (title, posted date, department, type & location, "التفاصيل والتقديم"),
  side box for general application (form itself is built in phase 08 — render a button linking to the general
  application section for now).
- Show: job description, requirements, closing date, apply section placeholder (form in phase 08).
- Closed/expired jobs return 410-style friendly page with link to open jobs.

## 6. FAQ page
Grouped accordion (general FAQs), search within FAQs (Alpine filter), CTA to contact.

## 7. Search (`search`)
Livewire `SearchPage`: query across published services, projects, posts, pages (title/excerpt in current locale),
grouped results with counts, highlighting, empty state with suggestions; min 2 chars; rate-limited.

## 8. Legal pages
Privacy and terms are custom pages (seeded) — just verify rendering.

## Tests
Each route 200 in both locales with demo data; unpublished/draft 404; scheduled post hidden until time; translated slug
URLs and language switch; ProjectsIndex filters/sort/load-more; PostsIndex search; Search page results; lightbox markup.

## Acceptance checklist
- [ ] All pages built, responsive (360–1440), AR/EN, no hardcoded strings
- [ ] Page builder output matches the required structure (main media → content → files/videos/images)
- [ ] Livewire filters/search without reloads; URLs shareable
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-07: inner pages`
