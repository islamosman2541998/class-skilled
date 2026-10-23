# Phase 04 — Content modules in the dashboard (incl. Page Builder & Home Sections)

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/dashboard/06-page-editor.jpg`,
`07-home-sections.jpg`, `10-projects-delete-confirm.jpg`.
Goal: full CRUD for every content type, using the phase-02 kit. Every list: search, filters, status tabs, sorting,
drag-reorder where `sort` exists, bulk actions (publish/unpublish/feature/delete), Excel export, "missing English"
filter, preview link. Every form: AR/EN tabs, validation, autosave-draft indicator (no autosave writes; just dirty
state), SEO panel, publish panel, toasts, SweetAlert confirmations, unsaved-changes guard.

## 1. Shared editor layout (`06-page-editor.jpg`)
Two columns: main (content sections) + side (Publish: status, published_at, preview button; SEO: title + counter
60, description + counter 160, OG image picker, index/noindex, canonical, live Google snippet preview per locale;
Translation status). Header actions: Preview (signed temporary URL that shows drafts), Save draft, Publish/Update.

## 2. Block editor (used by Pages, Services, Projects)
- Blocks from phase 01 §6: add from a menu (block name + one-line description), reorder by drag, collapse/expand,
  duplicate, delete (confirm). Each block edits its `data` with the kit controls (rich text, asset picker, repeaters,
  link picker). AR/EN tabs inside each block for text fields. Validation per block type.
- Blade preview not needed in the dashboard (Preview button opens the real page).

## 3. Pages (custom page builder) — the user's required structure
- Header: title, slug (localized URL preview, auto redirect on change), description, **main media type**
  (image / uploaded video / YouTube-Vimeo / PDF / none) + the matching picker (image also has optional mobile image).
- Content: block editor (§2).
- Attachments: three groups shown under the content in this order — **Files (PDF), Videos, Images** — each group:
  visible toggle, group title AR/EN, display mode (files: download list; videos: grid; images: grid 2/3/4 cols or
  masonry), items picked from the library (multiple, ordered), per-item title override AR/EN.
- Settings: parent page (breadcrumbs), show in sitemap, template width (normal / wide).
- List: tree view by parent, status, updated at, "used in menus" indicator.

## 4. Services
Title, slug, summary (menus & cards, 140 chars), excerpt, cover image, gallery, scope (repeater list AR/EN),
stages (repeater AR/EN, ordered = real sequence), blocks, FAQs (inline repeater creating `faqs` morph rows),
featured, active, sort, SEO. Related projects are derived from the project↔service pivot.

## 5. Project categories & Projects
- Categories: name AR/EN, slug, sort (drag), active, count of projects.
- Projects: title, slug, category, services (multi), location AR/EN, area m², duration AR/EN, year, project status,
  client name AR/EN + "show client" toggle, excerpt, cover, gallery (ordered), before/after pairs (repeater of two
  images), videos (assets), scope list, blocks, featured, publish, SEO. List with thumbnail, category, year,
  translation badge, featured/published toggles inline (instant, toast), drag-reorder, delete confirm (as in screen 10).

## 6. Posts & categories
Post: title, slug, category, cover, excerpt, body (rich text per locale), featured, publish/schedule, author,
reading minutes auto, SEO. Category: name, slug, sort.

## 7. FAQs, Testimonials, Clients, Team, Certificates, Job posts
- FAQs: general FAQ page items (faqable null) — question/answer AR/EN, active, drag sort; filter by attached service.
- Testimonials: name, company, text, rating, related project, photo, active, sort. (Only real testimonials.)
- Clients: name AR/EN, logo, url, active, sort.
- Team: name, role, bio, photo, active, sort.
- Certificates: name, number, authority, issued/expiry dates, PDF file, image, verify URL, show on site, sort;
  list highlights "expires within 90 days" (amber) and "expired" (red).
- Job posts: title, slug, department, type, location, description (rich text), requirements (repeater), status,
  publish/close dates, applications count link.

## 8. Home sections (`07-home-sections.jpg`)
- Single screen: ordered list of sections (drag to reorder), visibility toggle inline, type badge, summary line,
  "edit" opens the section form in a side panel; "add section" by type; delete with confirm.
- Section form fields by type (all text AR/EN):
  - **hero**: slides repeater (desktop image, optional mobile image, optional short video asset, title, text,
    primary CTA {label, link}, secondary CTA {label, link}, caption, overlay strength 30–60%), autoplay seconds (4–10),
    show arrows, show progress pagination.
  - **facts**: items repeater {label, value} with "fill from company settings" button (licence, classification,
    authority, ADCCI).
  - **about**: title, text, 2 images, meta items {label, value}, link {label, link}.
  - **services / projects / posts / testimonials / clients / certificates**: title, subtitle, link label + link,
    data source (manual picks ordered / all active / featured / latest) + limit, **background (white / paper / dark /
    dark_photo + background image)**, display (slider / grid), slider autoplay on/off.
  - **feature_band** (studios): title, text, bullet items, 3 images, CTA, background.
  - **why_us**: title, subtitle, items {title, text}.
  - **process**: title, steps {title, text} (numbered automatically).
  - **stats**: items {value, suffix, label} with count-up toggle.
  - **cta**: title, text, primary/secondary link (defaults: quote page + WhatsApp).
  - **rich_text**: title + rich text.
- `HomeSectionResolver` (service) returns view-models per section with eager-loaded data and resolved links, cached per
  locale (flushed on any related model change).

## 9. Seed check
Re-run `migrate:fresh --seed`: demo content from phase 01 must open and save in every form without validation errors.

## Tests
CRUD + validation for each module (feature tests via Livewire testing), slug redirect on edit, publish scheduling,
block validation, attachments ordering, home section resolver output for each type, permissions (sales cannot edit content).

## Acceptance checklist
- [ ] Every module listed works end-to-end AR/EN with search/filter/sort/reorder/bulk/export, no reloads
- [ ] Page builder supports main media types + blocks + the three attachment groups
- [ ] Home sections fully configurable (order, visibility, data source, background, slider options)
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-04: content modules`
