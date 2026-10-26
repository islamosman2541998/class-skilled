# Progress log

Update this file at the end of every phase. Keep entries short and factual.

| Phase | Status | Date | Commit |
|---|---|---|---|
| 00 Setup & tooling | done | 2026-10-23 | phase-00 |
| 01 Data model & infrastructure | done | 2026-10-23 | phase-01 |
| 02 Dashboard shell & component kit | done | 2026-10-23 | phase-02 |
| 03 Media, settings, appearance, SEO | done | 2026-10-23 | phase-03 |
| 04 Content modules | done | 2026-10-23 | phase-04 |
| 05 Menu builder | done | 2026-10-23 | phase-05 |
| 06 Site layout & home | done | 2026-10-23 | phase-06 |
| 07 Inner pages | done | 2026-10-24 | phase-07 |
| 08 Forms & requests | done | 2026-10-24 | phase-08 |
| 09 SEO, analytics, dashboard home | done | 2026-10-24 | phase-09 |
| 10 Hardening & deploy | done | 2026-10-24 | phase-10 |

## Installed versions
PHP 8.3.16, Laravel 12.70.1, Livewire 4.4.8, Fortify 1.41.0, mcamara/laravel-localization 2.4.2,
spatie: translatable 6.14.1, sluggable 4.0.4, medialibrary 11.23.10, settings 3.10.0, permission 8.3.2,
translation-loader 2.8.3, sitemap 8.0.0, responsecache 7.7.2, activitylog 4.12.3, honeypot 4.7.3, simple-excel 3.11.0;
intervention/image 3.11.10, mews/purifier 3.4.4, blade-heroicons 2.7.0. Dev: pint 1.32.1, larastan 3.13.1,
pest 4.7.8 (+laravel 4.1.0), debugbar 4.4.4.
npm: tailwindcss 4.3.3, vite 7.3.7, swiper 14.3.0, notyf 3.10.0, sweetalert2 11.26.25, @fontsource cairo/tajawal 5.3.0,
sortablejs 1.15.7, tom-select 2.6.2, flatpickr 4.6.13, chart.js 4.6.1, @tiptap/* 3.32.0. Node 24.18.0.

## Decisions
- Fortify routes ignored in phase 00 (custom auth screens come in phase 02).
- Composer platform pinned to PHP 8.3.16 (Laragon version).
- Slugs are JSON per locale (spatie translatable slug, `usingLanguage('')` keeps Arabic letters, `preventOverwrite`:
  a slug only changes when edited explicitly). No DB index on JSON slugs (tables are tiny; portable to SQLite tests).
- `slug` changes create auto 301s (`Redirect::createAuto`) that re-point chains and delete loops.
- Polymorphic columns use an enforced morph map (`AppServiceProvider::MORPH_MAP`, e.g. `page`, `service`).
- Blocks / home-section `data` hold asset ids; referenced assets are mirrored into the `blocks` / `data` asset
  collections only for "used in" tracking and delete guards.
- `SiteCache` is versioned (flush = bump version + `ResponseCache::clear()`); response cache uses its own cache
  store `responsecache` so clearing it never wipes rate limiters. `SiteCache::withoutFlushing()` for bulk writes.
- Sitemaps are XML files in `storage/app/sitemaps` (index + per type, hreflang alternates), regenerated once at the
  end of any request that changed a page-type model.
- Link object types extended with `whatsapp` and `phone` (used by CTA defaults).
- Section `data` schemas: hero `{autoplay, show_arrows, show_progress, slides[{id, image_asset_id,
  mobile_image_asset_id, video_asset_id, title, text, primary{label,link}, secondary{label,link}, caption, overlay}]}`;
  facts `{items[{label,value}]}`; about `{text, image_asset_ids[2], meta[{label,value}]}` + cta_label/cta_link;
  list sections `{item_ids[], display: slider|grid, autoplay, bg_asset_id, show_filters?}` + source/items_limit;
  feature_band `{bullets[], image_asset_ids[3]}`; why_us `{items[{title,text}]}`; process `{steps[{title,text}]}`;
  stats `{items[{value,suffix,label}], count_up}`; cta `{primary, secondary}`; rich_text `{html}`.
- Extra settings group `SystemSettings` (backup_keep, admin_session_minutes); `CompanySettings` also stores
  licence authority and classification licence no.; `FormSettings` also has `contact_subjects`.
- Users got `force_two_factor`; quote requests got `preferred_contact`; menus got `use_header_menu`;
  menu items got `show_descriptions`; pages got `template`; assets got `external_id`.
- Seeding runs in one DB transaction (MySQL commits are very slow on this machine).
- Dashboard: Fortify routes live under ADMIN_PATH (login, password reset); registration disabled.
  **Two-factor authentication was removed on the owner's request (2026-10-24)**: no Fortify 2FA feature, challenge
  screen, account section, "force 2FA" user option, banner or menu link; the columns are dropped by
  `2026_10_24_120000_drop_two_factor_columns_from_users_table`. This overrides "2FA available for admins" in CLAUDE.md §5.7.
- Livewire/Alpine are bundled from `vendor/livewire/livewire/dist/livewire.esm` (`@livewireScriptConfig`) so our
  Alpine components register before start. Custom inputs use `x-modelable` + `wire:model`.
- Sidebar items appear only when their route exists and the user passes `can` (config/admin.php navigation),
  so modules of later phases never show dead links.
- Gate::before grants admins everything except User abilities (self-delete / last-admin rules still apply).
- Idle dashboard sessions expire after SystemSettings::admin_session_minutes (EnsureUserIsActive).
- Livewire temporary upload limit raised to 100 MB; per-type limits enforced by AssetUploader.
- Local admin: `.env` ADMIN_EMAIL=admin@class-skilled.test / ADMIN_PASSWORD=ChangeMe-2026! (change it).

- Settings screens: one Livewire page per group (`App\Livewire\Admin\Settings\*` on a shared `SettingsPage` base);
  Tracking is admin-only. Favicons (32/180/192/512) are generated with GD into the public disk on General save.
- Fonts: each family is its own Vite CSS entry (`resources/css/fonts/{cairo,tajawal}.css`); the site layout loads
  only the chosen ones, preloads the heading 800 woff2 and prints theme CSS variables (`Appearance::styleTag()`,
  cached in SiteCache).
- Redirects run as a global middleware (before routing) so old URLs that match no route still redirect;
  Location headers are percent-encoded per segment.
- robots.txt is served by a route (static public/robots.txt removed); non-production always disallows all.
- Maintenance mode = MaintenanceMode middleware on the localized public group (users + allowed IPs bypass).
- Backups: pure-PHP chunked SQL dump + optional media, zipped in storage/app/backups, signed 10-minute downloads.

- Content editors share `ContentForm` (+ WithSeoForm, WithBlockEditor, PairLists); lists share `ContentIndex`;
  small types (FAQs, testimonials, clients, team, certificates, categories) are schema-driven `SimpleResource`s
  with a modal form. Routes use `{id}` (`/projects/{id}/edit`).
- Content models extend `App\Models\ContentModel` (no behaviour, only method annotations for static analysis).
  `Sortable::reorder()` was renamed `saveOrder()` (collided with Builder::reorder for analysis).
- Draft preview: signed 30-min link (`admin.preview`) → session flag (`App\Support\Preview::allows()`) → public URL.
  Public pages (phase 07) must honour it and show a ribbon + noindex.
- Page attachments: groups files/videos/images in asset collections att_files/att_videos/att_images; per-item
  title overrides stored in the pivot meta `{title: {ar, en}}`. Project before/after pairs: collection
  `before_after` with meta `{pair, side}`.
- Service FAQs are `faqs` rows with faqable = service, managed inline in the service form.
- `HomeSectionResolver` resolves visible sections per locale (cached in SiteCache); list sections support
  manual / all / featured / latest; empty sections are dropped.

- Menus: `App\Services\Menus\MenuResolver` returns `MenuNode` trees (cached per location+locale; active flags
  applied per request). Targets come from `LinkIndex` (one cached array per linkable type) so building a menu
  costs 2–3 queries; items whose target is unpublished, inactive or not translated into the locale are skipped.
  System-page labels are UI strings (`nav.*`); footer column titles are the menu names. The site layout gets
  `$menus` (all locations) through a view composer.

- Public layout: `SiteContext` (scoped) wraps settings for views (names, phones, WhatsApp, registration line,
  CTA band); `SeoManager` (scoped) holds title/description/alternate URLs (controllers call `alternates()` for
  translated slugs; otherwise mcamara `getLocalizedURL`). Both are shared with `layouts.site`, `site.*`, `errors.*`.
- Header shows the bundled CS mark + site name/tagline from settings (the uploaded logo in Settings → General is
  the stacked logo, used in the footer). Header is transparent over a first-section hero (`@section('header_overlay')`).
- The CTA band before the footer reuses the home `cta` section (fallback: `site.cta_*` UI strings); pages hide it
  with `@section('hide_cta', '1')`.
- Site JS bundles Livewire ESM (Alpine) like the dashboard; `heroSlider` / `cardSlider` Alpine components import
  `sliders.js` (Swiper) lazily; SweetAlert2 is imported on the first `$confirm()`. Reveal states live in CSS under
  `.js` so no-JS visitors see everything.
- `errors/500.blade.php` is self-contained (no DB/cache) and carries last-resort fallback texts in case the
  translation store is unreachable.

- Public detail pages use `ResolvesPublicModels::findPublic()`: slug in the current locale, public or an active
  signed preview (ribbon + noindex), then `SeoManager::forModel()` (HasSeo overrides + translated alternates; locales
  without a translation are dropped from hreflang and the switch falls back to that locale's home).
- `translated()` scope (HasTranslatedSlug) keeps English listings to items that exist in English.
- Lists: `Livewire\Site\ProjectsIndex` (cat/service/sort/q in the query string, load more by 9),
  `PostsIndex` (cat/q, featured post kept out of the grid, custom localized pagination view), `SearchPage`
  (min 2 chars, 30 searches/min per IP, results escaped then highlighted by `Support\Highlight`).
- Media on inner pages: Alpine `lightbox` (images, uploaded videos, YouTube/Vimeo) fed by `Support\Lightbox::items()`;
  embeds are "lite" (iframe after click); before/after uses a native range input; file sizes via
  `Asset::humanSize()` so the site does not depend on the intl extension.
- Closed jobs (published but past `closes_at`) answer 410 with a link to open jobs. Quote/contact stay placeholders
  until phase 08; the job page has an `#apply` slot that phase 08 fills (`site.partials.job-application`).

- Public forms (Livewire `QuoteForm` 4 steps, `ContactForm`, `JobApplicationForm`) share `Services\RequestIntake`:
  honeypot field + minimum fill time (locked server timestamp, 3 s) + optional Turnstile (`Support\Turnstile`, only
  when both keys are set) + 5 submissions/hour per IP and form; then default status, `system` activity, `site_events`
  row, badge cache reset and e-mails via `dispatch(fn)->afterResponse()` (no queue worker on shared hosting).
  The spatie honeypot package has no Livewire support in the installed version, so the check lives in the forms.
- Choice fields (facility type, project state, emirate, start, contact subject) are posted as indexes of the
  Settings → Forms lists and stored as their Arabic label so dashboard filters stay stable.
- Visitor uploads go to the `private` disk (media collections `attachments` / `cv`); the dashboard serves them through
  the signed, permission-checked `admin.requests.file` route (`?inline=1` for the preview modal).
- First-touch attribution: `CaptureFirstTouch` middleware stores landing page, external referrer and UTM in the
  `cs_ft` cookie (30 days); requests store `{page, landing, referrer, utm, device}` in `source`.
- Dashboard requests: `Admin\Requests\RequestIndex` / `RequestShow` are shared by the three types (thin subclasses per
  type); statuses per type in `Admin\Requests\Statuses` (one default per type, unused non-default statuses deletable).
  Request models extend `App\Models\VisitorRequest` (base with the `IsVisitorRequest` trait).
- Mails: markdown, theme `class-skilled` (navy header, mark, RTL for Arabic); team notification in Arabic with every
  field (`Support\RequestPresenter`) and a dashboard link; auto-reply in the visitor's locale from Settings →
  Notifications templates (`{name}`, `{number}`).

- SEO head: `SeoManager` now also gives the canonical (SEO override or this page in the current locale without
  query), Open Graph/Twitter (image = SEO image → cover → default share image → logo, `og:type` article for posts),
  breadcrumbs (filled by the page header partial) and page JSON-LD nodes; hreflang lists only existing locales plus
  `x-default` (Arabic). `SchemaBuilder` outputs one `@graph`: GeneralContractor (+ Settings → SEO extra JSON) and
  WebSite/SearchAction on every page, BreadcrumbList, and Service + FAQPage / Article / JobPosting / FAQPage /
  CreativeWork with ImageObjects per page type.
- Sitemaps are served by `SitemapController` (`/sitemap.xml`, `/sitemaps/{type}.xml`), regenerated on content change
  (existing observer) or from Dashboard → SEO; URLs are percent-encoded (Arabic slugs) as the protocol requires.
- Analytics: `track.js` beacons `/t` (CSRF-exempt, 60/min/IP, bots ignored) for one page view per load and
  WhatsApp/call/e-mail clicks; page views are upserted into `page_views_daily` (path + locale + day), clicks into
  `site_events`; events are mirrored to `gtag`/`fbq` when present. GA4/GTM/Meta and custom scripts are printed only in
  production and only for IDs that are set.
- Dashboard home: `Admin\Dashboard` (period 7/30/90 in the URL) + lazy `Admin\DashboardWidget` (stats, chart, services,
  latest, attention, pages) with skeleton placeholders; numbers from `Services\DashboardStats`, cached 5 minutes per
  widget/period/locale. Request widgets need `view quote_requests`.

- Full-page cache: `CacheResponse` on the public route group with `Http\CacheProfiles\SiteCacheProfile` (guests only;
  no query strings, Livewire/AJAX, flashed errors/toasts, previews; quote/contact/careers/search never cached). As a
  route middleware it caches before the `web` group adds session/XSRF cookies, so cookies are never replayed.
- `SecurityHeaders` (global): CSP that opens third parties only for integrations in use (GA4/GTM/Meta in production,
  Turnstile, YouTube/Vimeo, Google Maps; Vite dev origin when hot), X-Frame-Options, nosniff, Referrer-Policy,
  Permissions-Policy, HSTS on HTTPS production. Livewire/Alpine require `unsafe-inline`/`unsafe-eval` for scripts.
- Visitor uploads are also checked by sniffed MIME (`Rules\SafeUpload`); `storage/app/public/.htaccess` blocks scripts;
  hosts without symlinks get the `/storage/{path}` fallback (`PublicStorageController`, media types only).
- `php artisan site:check` and Dashboard → System → Health share `Support\SiteCheck` (PHP, extensions, folders,
  storage link/fallback, build manifest, DB, cache, mail, queue, debug, caches).
- `public/.htaccess`: gzip, long-lived caching for build assets/fonts/media, dotfiles denied, HTTPS redirect ready.

## Changes to earlier phases
- Phase 03: `BackupManager` only raises the time limit for web requests (it leaked a 300 s limit into the CLI/test run).
- Phase 06: brand marks use small `-sm.webp` / `-sm.png` files (the originals are 170–230 KB); dark-photo section
  backgrounds use `sm` on mobile and `lg` on desktop instead of `xl`; hero slides 2+ are injected after page load.
- Phase 07: `ResolvesPublicModels::ensurePublic()` split out so the job page looks the job up once.
- Phase 00: the placeholder controller/view and the `setup.*` UI strings were removed (every route has its page now).
- Phase 01: `QuoteRequest`, `ContactMessage`, `JobApplication` now extend `VisitorRequest` (same trait, moved to the base).
- Phase 07: careers page gained the general application form section; the job page `#apply` slot renders the form.
- Phase 01: `EssentialSeeder` clears the translation-loader cache per group after seeding UI strings (seeders run
  without model events, so stale `language_lines` caches showed raw keys after `migrate:fresh --seed`).
- Phase 00: `SetupTest` placeholder dataset reduced to quote/contact (other routes are real pages now).
- Phase 00: `SetupTest` placeholder dataset no longer lists `home` (now a real page, covered by `SiteLayoutHomeTest`);
  `placeholder.blade.php` wraps its content in the site container.
- Phase 03: `AppServiceProvider` registers `SiteContext`/`SeoManager` and an extra view composer.

## Known gaps / follow-ups
- Lighthouse performance on local Laragon is 56–73 (a11y 100 everywhere): the local server has no HTTPS, no gzip, no
  HTTP/2 and slow PHP; robots.txt disallows non-production. Re-run on the production host and record the numbers.
- Items marked "☐ manual" in `_design/QA.md` (keyboard/screen-reader passes, 360/768/1024 widths, real SMTP rendering,
  Turnstile with real keys, GA4/Meta firing, 500 page with the DB down) need a person before launch.
- Home JS budget: Livewire+Alpine runtime alone is ~85 KB gzip, so home ships ~108 KB (shared chunk incl. Notyf)
  + 28 KB Swiper chunk (lazy) — above the 90 KB target; the only way under it is dropping Livewire from pages
  without components (revisit in phase 10).
- Lighthouse CLI is not available offline here; layout checked with headless Chrome screenshots at 390 px
  (iframe harness) and 1440 px in AR/EN instead. Run Lighthouse in phase 10 on the deployed build.
- `lang/ar/validation.php` (Arabic validation messages) to be added with the dashboard forms (phase 02).

---
## Phase entries
<!-- Template:
### Phase NN — title (date)
- Built:
- Key files/areas:
- Acceptance checklist: all ticked / exceptions:
- Lighthouse / metrics (if relevant):
- Next: Phase NN+1
-->

### Phase 00 — Project setup & tooling (2026-10-23)
- Built: Laravel 12 skeleton, all §3 packages, localized route map with placeholder controller, admin prefix
  group with session locale, Vite entries (site/admin), Tailwind v4 tokens, self-hosted fonts, folder structure,
  base layouts, Pest smoke tests, brand assets in `public/images/brand`.
- Acceptance checklist: all ticked (route:trans:cache OK, build OK, pint/larastan/pest green, migrate:fresh OK).
- Next: Phase 01

### Phase 01 — Data model & infrastructure (2026-10-23)
- Built: full schema (assets/media folders/assetables, seo_meta, redirects, all content tables, menus, requests,
  analytics), 18 enums with DB-translated labels, concerns (HasTranslatedSlug, HasSeo, HasAssets, HasBlocks,
  Publishable, Sortable, ClearsSiteCache, LogsModelActivity, IsVisitorRequest), 12 settings groups with defaults,
  AssetUploader (GD downscale + WebP conversions thumb/sm/md/lg/xl), `<x-site.picture>`, block registry
  (`App\Support\Blocks`), LinkResolver, LocalizedRoute, SiteCache, Sitemap, Permissions catalogue,
  EssentialSeeder (roles, admin, statuses, 358 UI strings, legal pages, menus, 12 home sections) and
  DemoContentSeeder (16 images, 3 PDFs, 8 services, 4 categories, 9 projects, 6 posts, FAQs, 2 certificates,
  4 jobs, About page, hero/feature images, header menu links).
- Acceptance checklist: all ticked (migrate:fresh --seed OK with 80 WebP files; larastan clean; 48 tests green).
- Next: Phase 02

### Phase 02 — Dashboard shell & component kit (2026-10-23)
- Built: custom Fortify views (login split screen, forgot/reset, 2FA challenge, confirm), admin layout (navy
  sidebar with collapsible persisted groups + badges, topbar with Ctrl/Cmd+K command palette, view site, language
  toggle, notifications, user menu, off-canvas below 1024px), component kit (`components/admin/*`: page-header,
  panel, badge, stat-card, empty-state, skeleton-rows, modal, field, input, textarea, select/Tom Select, toggle,
  segmented, radio-cards, date/flatpickr, color, slug, rich-text/TipTap, link-picker, repeater, locale-tabs,
  asset-thumb, table/* parts), Livewire concerns (WithDataTable incl. XLSX export, WithToasts, WithRepeaters,
  WithTranslatableForm, WithSortable), global AssetPicker (library + uploader with progress and video poster
  capture) and AssetField, policies for every model, Users (list/form), Roles matrix, Activity log (diff modal),
  My account (profile, password, 2FA), Arabic validation/auth/passwords translations.
- Acceptance checklist: ticked except the visual screenshot comparison (no browser automation available here;
  all screens verified via HTTP 200 + Livewire tests).
- Known gap: admin JS bundle ~291 KB gzip (TipTap etc. eager) — code-split in phase 10.
- Next: Phase 03

### Phase 03 — Media, settings, appearance, SEO (2026-10-23)
- Built: media library (folder tree with counts, grid/list, search/kind/missing-alt/sort filters, drag-anywhere
  upload with progress + video poster capture, embeds, bulk move/delete guarded by usage, details panel with sizes,
  "used in" links, AR/EN alt/title/caption, focal point with conversion regeneration, replace file, copy URL,
  download, delete/force-delete), settings pages (general + favicons, company, contact with WhatsApp/map previews,
  social, notifications + test email, forms lists, media + chunked "regenerate all", tracking, maintenance,
  system), appearance (fonts radio cards, size/line-height, colours with contrast warnings, live preview), UI
  strings (search, filters, inline edit, reset to default, JSON import/export), SEO settings, dynamic robots.txt,
  redirects (CRUD, loop prevention, CSV import, export, middleware), SEO health list, backups.
- Shared: `WithAssetUploads` concern, `x-admin.uploader`, `x-admin.settings-frame`, `AdminLinks`.
- Acceptance checklist: all ticked (72 tests green; all screens HTTP 200 for the real admin; larastan clean).
- Next: Phase 04

### Phase 04 — Content modules (2026-10-23)
- Built: page builder (header + main media image/mobile/video/embed/PDF, 11 block types with drag/duplicate/
  collapse, attachments files→videos→images with display options and per-item titles, parent/template/sitemap),
  services (summary, cover, gallery, scope, ordered stages, blocks, inline FAQs), projects (category, services,
  facts, gallery, before/after pairs, videos, scope, blocks), posts (rich body, category, author, reading time),
  jobs (description, requirements, closing date, applications count), FAQs (general / per service), testimonials,
  clients, team, certificates (expiry highlighting), project/post categories, home sections (drag order,
  visibility, add by type, side-panel editor for all 15 section types, "fill from company" for facts).
  Every list: search, status tabs, featured / missing-English filters, inline toggles, bulk actions, drag reorder,
  Excel export; every editor: AR/EN tabs, SEO panel with Google snippet preview, publish/schedule panel,
  translation status, unsaved-changes guard, signed previews.
- Acceptance checklist: all ticked — 83 tests green (incl. every seeded demo record opening and saving without
  validation errors), larastan clean, all screens HTTP 200.
- Next: Phase 05

### Phase 05 — Menu builder (2026-10-23)
- Built: menu builder screen (7 location tabs, nested drag & drop tree with depth-2 guard, add panel: pages /
  system pages / specific item search / dynamic group / external / heading, item side panel: inherited labels,
  target, new tab, visibility, active; dynamic groups: source, mode manual/all/featured/latest, limit, ordered
  manual picks, own link, "view all", descriptions; mobile "use header menu"; editable menu names = footer
  column titles), MenuResolver + LinkIndex + MenuNode, site view composer, `nav.*` UI strings, menu names in
  EssentialSeeder.
- Acceptance checklist: all ticked (resolver correct in AR/EN, ≤3 queries per menu asserted, 91 tests green).
- Next: Phase 06

### Phase 06 — Site layout & home (2026-10-23)
- Built: site design system in `resources/css/site.css` (type scale `.t-*`, buttons, glass utilities + fallbacks,
  section variants incl. dark-photo, glass cards, chips, prose, header/bottom-bar/hero styles, reveal states,
  reduced motion); layout + partials (head with hreflang/favicons/preloads, top bar, sticky header with keyboard
  dropdowns, off-canvas mobile menu with focus trap + accordion, mobile bottom bar, floating WhatsApp, CTA band,
  footer with menus/contact/social/registration/legal); breadcrumbs component; styled 403/404/410/419/429 + 500;
  `HomeController` + `site/pages/home` looping `HomeSectionResolver` into one Blade per section type (15 types);
  JS: `app.js`, `sliders.js` (hero fade + Ken Burns + progress bars + pause, card sliders + category filter),
  `reveal.js` (fade-up/fade/image/stagger/draw-line/count-up), `header.js`, `mobile-nav.js`, `toast.js`, `confirm.js`.
- New UI strings: `site.quote_short`, `site.cta_title`, `site.cta_text`, `site.confirm`, `site.cancel`, `site.social_*`.
- Tests: `SiteLayoutHomeTest` (AR/EN render, hidden sections, immediate updates, Swiper only when needed, dropdown
  children from resolver, language switch URLs, 404 page, a view per section type). 99 tests green.
- Acceptance: layout + home per §6 ✔; dropdown + off-canvas driven by menu builder ✔; cache flush updates the site ✔;
  Lighthouse not run (see known gaps); gates pass ✔.
- Next: Phase 07

### Phase 07 — Inner pages (2026-10-24)
- Built: controllers for services, projects (+category), articles (+category), careers (+closed 410), FAQ, search,
  custom pages; inner page header + breadcrumbs + preview ribbon; one Blade per block type (11); custom page main
  media (image / video / lite embed / PDF card) → blocks → attachments (files list → video grid → image grid/masonry);
  service detail (scope, numbered stages, gallery with "+N", FAQ accordion, sticky quote box with `?service=`, other
  services, related projects slider); project detail (gallery + thumbs + swipe + lightbox, facts, similar-project CTA,
  scope, before/after, videos, related slider); article detail (meta, prose, share links, related slider);
  Livewire projects/articles listings and site search; FAQ page with client-side filter.
- Tests: `InnerPagesTest` (11 tests: both locales, inactive/draft 404, preview, scheduled post, untranslated items,
  lightbox markup, filters/sort/load more, posts search/category, search page, page structure order, FAQ/careers/410,
  seeded legal pages). 82 tests green, larastan clean, 26 public URLs 200 in AR/EN, log clean.
- Checked visually with headless Chrome at 1440 (project detail, projects index) and 390 (service detail, articles).
- Next: Phase 08

### Phase 08 — Forms & requests (2026-10-24)
- Built: quote page (stepper with done/current states, per-step validation, service chips with `?service=` preselect,
  drag & drop multi-upload with progress, file list with remove, toast after uploads, summary box from step 2, direct
  contact box, sticky step buttons above the mobile bar, success screen with request number + WhatsApp); contact page
  (channels, form with inline validation, click-to-load map, working hours); job application form (job page + general);
  dashboard quotes / messages / applications lists (status tabs with counts, search incl. number, filters per type +
  assignee/device/unread/date range, sorting, column toggle, bulk status/assign/read/export/delete, Excel export) and
  details (fields, attachments with preview/download, WhatsApp/call/email quick actions, timeline with notes, status,
  assignee, follow-up date, source, mark unread, delete for admins); statuses screen; branded mails.
- Tests: `FormsRequestsTest` (10: step validation, preselect, full quote flow with files/status/activity/event/mails,
  auto-reply locale, honeypot/fill time/rate limit, contact, applications + closed job, sales list/filter/export/follow-up
  without delete, editor forbidden + signed attachment access, statuses default). 92 tests green, larastan clean.
- Checked visually: quote step 1 (1440, AR) and contact (390, EN).
- Next: Phase 09

### Phase 09 — SEO, analytics, dashboard home (2026-10-24)
- Built: SeoManager completion + `SchemaBuilder`, SEO/OG/Twitter/verification partial, tracking partials, sitemap
  routes + regenerate button, `TrackController` + `track.js`, `DashboardStats`, dashboard home with six lazy widgets
  (stat cards with deltas, requests/visits per day chart with current week highlighted, requests by service, latest
  quotes, needs attention incl. licence/classification expiry dates, top pages).
- Tests: `SeoAnalyticsDashboardTest` (7: canonical/hreflang/x-default/OG/noindex, JSON-LD per page type, sitemap
  alternates + drafts/noindex excluded, robots, beacon counting/bots/throttle, dashboard numbers per period, editor
  view). 99 tests green, larastan clean, log clean after `migrate:fresh --seed`.
- Not checked visually: the dashboard home (no authenticated browser session in headless Chrome); covered by tests.
- Next: Phase 10

### Phase 10 — Hardening & deploy (2026-10-24)
- Built: page cache profile + middleware, security headers, MIME-sniffing upload rule, public storage `.htaccess` and
  fallback route, `site:check` command + System → Health page (with cache buttons), production-ready `.env.example`,
  `public/.htaccess` compression/caching, lighter brand marks and hero/background images, `DEPLOY.md` (AR + EN),
  `_design/QA.md`.
- Measured: warm query counts 2–12 per public page; cache hit verified by the debug header; `optimize` +
  `route:trans:cache` work; Lighthouse results recorded in QA.md.
- Tests: `HardeningTest` (6: cache served + flushed, bypass rules, headers, upload sniffing, site:check + health
  permissions, storage fallback safety). Final gates: `migrate:fresh --seed` ✔, Pint ✔, Larastan level 5 ✔,
  Pest 105 passed ✔, `npm run build` ✔, laravel.log empty ✔.

## Final summary
All eleven phases are built: bilingual public site (home with sliders and motion, every inner page, forms with
uploads), custom Livewire dashboard (content, media, menus, settings, SEO, requests, users/roles, backups, health),
SEO (meta, hreflang, JSON-LD, sitemaps, robots), first-party analytics, full-page caching and a shared-hosting
deployment guide. Before launch: follow DEPLOY.md, then complete the "☐ manual" items in QA.md and run Lighthouse on
the live host.

### Post-launch changes (2026-10-24)
- Dashboard top bar redesigned: frosted white bar (72 px, aligned with the sidebar header), pill search with
  `Ctrl` `K` key hints, "view site" button, segmented ع / EN language switch, bell icon with a counter badge and a
  notifications popover with header and empty state, user chip (gradient avatar, name, role, chevron) with a menu
  showing name + e-mail and icon items. Checked at 1440 and 390 px with an authenticated headless Chrome session.
- Two-factor authentication removed everywhere (see Decisions). Tests updated: `DashboardShellTest` now asserts that
  login goes straight to the dashboard and that no 2FA route or UI exists.

- Demo activity (local only, `DemoActivitySeeder`, chained after `DemoContentSeeder`): sales/editor users, 5
  testimonials, 8 clients, 4 team members, 26 quote requests / 10 messages / 8 applications spread over 90 days with
  statuses, assignment, notes and attachments, 90 days of page views and contact clicks, 3 redirects; the testimonials
  and clients home sections are switched on. Users: `sales@`, `sales2@`, `editor@class-skilled.test` (same local
  password as the admin).
- Fixed: contact-message and job-application detail screens returned 500 (media eager load / quote-only follow-up
  date); regression test added.
- Google appearance (Dashboard → SEO → "Appearance in Google results", with a live AR/EN result preview): site name +
  alternate name (WebSite structured data, `og:site_name`), home page title (used as is) and description, icon next
  to the result (favicon source; falls back to Settings → General favicon) and the organisation logo for structured
  data (falls back to the site logo). Settings migration `2026_10_24_130000_add_search_appearance_settings`.
- Favicons: sizes 32/48/96/180/192/512 + a real `/favicon.ico` (PNG in an ICO container, served by a route; the empty
  Laravel default file was removed), generated on first use and regenerated when General or SEO settings are saved.
- Sidebar badges = unread requests per type (`read_at` empty), cached 60 s and reset when a request is opened or
  marked; the bell = their sum. The blue dot in request lists = unread (not a status).
- Fixed: textarea character counter showed 0 until typing; requests list number/phone line no longer wraps.
- Fixed (unstyled site after rebuilding assets): the page cache key now includes the asset version (build manifest
  hash, or the Vite dev server when `public/hot` exists), so cached HTML never points to deleted CSS/JS. The CSP header
  is skipped only while the local Vite dev server runs (it may listen on `[::1]`, which CSP cannot express);
  production always sends it.
- Home (owner request): hero text sits directly on the photo (no glass box), no Ken Burns zoom, hero = full viewport
  height (100svh) on every screen; the facts bar section type is removed from the site, dashboard, defaults and
  enum (migration `2026_10_24_140000_remove_facts_section_and_simplify_about` deletes it and converts the about
  section); the about section is title + full text on one side and a single image on the other.
- Hero slides (owner request): each slide is added/edited on its own dashboard page
  (`/cs-panel/home-sections/{section}/slides/create` and `/slides/{slideKey}/edit`, `Admin\Content\HeroSlide`) with
  texts AR/EN, two buttons, overlay strength, image / mobile image / video and a live preview. The hero panel in Home
  sections now shows the autoplay/arrows/progress settings and a sortable slide list (edit, delete with confirmation,
  drag to reorder); slides carry a stable `id` (missing ids are added on open). The old in-panel slide editor was too
  cramped, so elements overlapped and covered the image remove/change buttons. `AssetField` now emits
  `asset-field-changed` after a pick/remove/reorder so a parent page can re-render (used by the slide preview).
- Card sliders (owner request): whole cards only, no half-visible card (1 / 2 / 3 / 4 per view at <640 / 640 / 1024 /
  1280; clients 2 / 3 / 5 / 6); equal-height cards and visible card shadows (Swiper's unlayered `height` / `overflow`
  rules were overriding ours); a service without a cover shows the brand mark in the image box. The services section
  no longer has a background photo (solid dark navy; migration `2026_10_24_150000_remove_services_section_background_photo`).
- Project page gallery (owner request): Swiper gallery (`gallerySlider` → `initGallery` in `sliders.js`) with glass
  arrows, swipe/drag, keyboard, 5 s autoplay (pauses on hover and while the lightbox is open), image counter and
  synced thumbnails (Swiper Thumbs module); a click opens the existing lightbox. Images = cover + gallery from
  Dashboard → Projects. Demo projects now have 4–5 photos each.
- Project gallery accepts videos: the asset picker takes a comma list of kinds (`kind="image,video,embed"`); the
  project gallery field uses it (images, MP4/WebM uploads, YouTube/Vimeo embeds from the library). On the project page
  a video shows as its poster with a play button (slide + thumbnail) and plays in the lightbox when clicked. New UI
  string `lightbox.play_video`.
- Footer & CTA (owner request): the request-a-quote band (and the home CTA section) is now a navy card on the light
  paper band, so it no longer merges with the footer. Footer is shorter (smaller logo, tighter spacing, two link
  columns per row on mobile); working hours and the licence/classification row are removed; contact shows one mobile
  number (first phone that is not the WhatsApp number) and the WhatsApp number with its icon, from Settings → Contact.
- Footer, round 2: horizontal logo (white mark + site name + tagline, larger); social links as round icon buttons
  (`x-site.social-icon`, label in aria-label/title); dynamic menu groups in footer columns expand to their items (the
  "Services" column lists the services; default limit 12); desktop columns follow the number of footer menus with
  links; mobile room for the bottom bar is now footer padding (navy) instead of body padding.

### Full review (2026-10-24)
- Gates: `migrate:fresh --seed` (separate scratch database), 110 tests / 759 assertions green, Pint, Larastan level 5,
  `npm run build`, clean log.
- Browser crawl: 96 public URLs (AR/EN) × 390/1440 — no JS errors, broken images, horizontal scroll, raw keys, missing
  alt/title/description/canonical; one H1 per page; 404 pages return 404. 142 dashboard screens (all records' edit /
  detail pages, every home section panel, every hero slide) at 1440 AR, list screens at 390 AR and 1440 EN.
- Fixed: system-page labels in the link picker / menus showed raw keys (`admin.link.routes.services.index` — dots in
  the key were read as nesting); now `LinkOptions::routeLabel()` with underscore keys + test.
- Fixed: 13 dashboard screens scrolled sideways on mobile (absolutely positioned `.sr-only` labels escaped table
  scroll areas; single-column grids grew to their widest child; SEO health / system health tables not wrapped).
- Contact form submitted end-to-end in a mobile browser (validation messages, then saved); test record deleted.
- Note (local only): Laragon's threaded Apache on Windows can intermittently miss `.env` under concurrent requests
  (500, "production"/sqlite in the log). Not reproducible one request at a time; production uses a cached config
  (`php artisan optimize`, DEPLOY.md), which avoids it.
- Live English 404 (class-skilled.com/en*): a plain route cache holds only the default (Arabic) routes. `php artisan
  optimize` now replaces its "routes" step with `route:trans:cache` (and `optimize:clear` with `route:trans:clear`) via
  `AppServiceProvider::optimizes(key: 'routes')`; System → Health / `site:check` fails "translated route cache" when a
  plain route cache has no per-locale files; DEPLOY.md warns against `route:cache` alone; test added.
- Card sliders: prev/next arrows now visible on mobile too (section header row: "view all" at the start, arrows at the end).
- Projects section filters: the category chips wrap onto several lines (all visible) instead of a sideways-scrolling
  row; chip padding 12px on mobile, 16px from 640px.
- Quote form upload (owner report: "no sign of the upload, Next does nothing"): Livewire morphed step 3 into an element
  of step 2, so Alpine never started the upload `x-data` (JS errors `uploading/progress is not defined`), the progress
  bar never showed and Next stayed disabled while the file uploaded (5–8 s on the live server). Each step is now wrapped
  in `wire:key="quote-step-N"`; the Next button reads "Uploading…" during an upload; a failed upload shows
  `forms.upload_failed` (quote form + job application CV). Verified in a browser on a throttled network; test added.
- SEO settings would not save (owner report: share image change lost, no toast): the default English home title
  (72 chars, settings migration) broke its own 70-char rule, so every save failed on a field hidden in the English tab.
  Limit is now 90 (the hint still recommends 50–60). Dashboard-wide: a failed validation raises an error toast with the
  first message (`WithToasts::exception()` Livewire hook); locale tabs open the English tab (red mark) when only English
  fields have errors; settings pages name fields by their labels ("Home page title (English)"). Notyf icon spacing fixed
  in RTL. Test: every settings page saves its default values; error toast test.
- Deep colour (owner request): `--c-deep` is now `#0C122B` (footer, top bar, dark sections, hero, dashboard sidebar,
  e-mails, 500 page). Settings migration `2026_10_26_120000_update_deep_colour` replaces only the old default. Hardcoded
  `rgb(27 42 71 / x)` overlays/shadows now use `color-mix(... var(--c-deep) ...)` so they follow Appearance.
- Service "From our work" section (owner request): the 6-tile image grid is replaced by a media gallery
  (`site/partials/media-gallery`, Alpine `mediaGallery` → `sliders.js` `initMedia`): stage slider with images,
  uploaded videos and YouTube/Vimeo, glass arrows, counter, autoplay pause button, swipe/drag, keyboard, thumbnail
  strip whose active thumb fills with the autoplay timer. Autoplay holds while a video plays, moves on when it ends,
  pauses off screen / hidden tab / lightbox. New custom video player `<x-site.video>` (`js/site/video-player.js`):
  play, seek with buffered range, time, mute + volume, full screen (iPhone fallback), auto-hide, keys k/space/m/f;
  timeline stays LTR in Arabic (media controls are not mirrored). Also used by `site/partials/video-player` (video
  block, page hero video). Dashboard: service gallery accepts image, video and embed. UI strings group `media`
  (+ migration `2026_10_26_130000_add_media_player_ui_strings` for existing databases). Test added.
- Media uploader, videos (owner report: "after capturing the poster it stops"): (1) several videos added at once
  shared one poster panel, so only the last got one and the others stayed at "poster" forever; the panel now takes
  them one by one (file name shown, Capture enabled once a frame is loaded). (2) With Livewire v4, `this.$wire` /
  `this.$dispatch` called from the poster panel's button belonged to the panel element, which is removed on capture:
  `storeUpload` never reached the server, so every video with a poster ended as failed/stuck and the picker never
  returned to the library. The uploader now keeps the root `$wire`/element from `init()`. Poster processing shows
  "Processing". Picker multiple mode opens empty and adds to the field; the amber "alt missing" badge is removed from
  asset thumbnails. Verified in a browser with three videos.
- Multi-selects (owner report: removed a project's services, published, nothing happened): Tom Select passes its own
  items array to onChange and removes items in place, so after an add the next removal assigned the same array and
  Alpine/Livewire never saw it; the removed services stayed. `select.js` now copies the array (all multi-selects).
  Dashboard: any failed Livewire request now shows an error toast (`admin.common.server_error` / `connection_error`,
  `Livewire.interceptRequest` in `admin/toast.js`; 419 keeps Livewire's prompt, APP_DEBUG keeps the error modal).
  Test: a project publishes with all services removed.
- Asset fields on a slow host (owner report: changed a service image, saved, no toast): the field took a pick only
  after its own Livewire round trip, so a Save pressed before it returned was sent without the new image (lost) and
  the button showed nothing for seconds. `js/admin/asset-field.js` (Alpine `assetField`) now applies picks, removals
  and new order to `$wire.value` in the browser at once (server methods still run for the thumbnails);
  `x-sortable.scope` calls the Alpine method. Every `.a-btn` that sent a request shows a spinner (Livewire v4
  `data-loading`, `admin.css`). Verified in a browser with 1.5 s added to every request.
- Dashboard toasts after wire:navigate (root cause of the "no toast" reports): Notyf appends its container to
  <body> once; wire:navigate swaps <body>, so after moving between pages every toast went to a detached node.
  `toast.js` (admin + site) makes a new instance when the container is gone.
- Create forms (owner review of "new service"): after a successful create the unsaved-changes guard blocked the
  redirect to the edit page (Livewire v4 redirects before `form-saved` arrives); "Cancel" there and a second Save
  made a duplicate. Server redirects now pass the guard (`interceptRequest` onRedirect → `cs-server-redirect`); the
  guard's `form-saved` listener is removed on destroy. Media picker: tabs switch in the browser (entangled `tab`), and
  while files upload the picker cannot be closed or confirmed (`uploader-busy`), so an upload is never dropped.
  Create titles agree in gender (`admin.content.create_titles`: خدمة جديدة / مشروع جديد). Verified in a browser with
  1.2 s added to every request.
- Hero slide overlay (owner request): 0–100 % instead of 30–60 (slider, validation, preview, HomeSectionResolver
  clamp); 0 = no overlay at all, so the bottom text shade (`.hero-slide__overlay::after`) now fades with the value
  and is gone at 0. Test added.
- Hero text on bright photos (owner chose option 1 of 4 mock-ups): a fixed text-side shade independent of the slide
  overlay — from the start side from 1024px (RTL right / LTR left, `--scrim-to`), from the bottom on smaller screens —
  plus a top band under the transparent header (`.hero-slide__overlay::before/::after`). The slide overlay (0–100)
  now only darkens the whole photo; the dashboard preview shows the same shade and the hint says so.
- Deep colour on the live server (owner report: footer still old after upload): the colour lives in the
  `appearance` settings (DB) and is cached (settings cache + theme style + page cache), so uploading files alone
  changes nothing. The settings migration now also clears the settings caches and flushes SiteCache, so
  `php artisan migrate --force` is enough; without a terminal, saving Dashboard → Settings → Appearance does the same.
- Sliders (owner request: every slider works with arrows, drag and autoplay): the hero ignored normal drags —
  Swiper's default long-swipe needs half the slide (720 px on a 1440 px hero) and edge resistance shrank drags back
  from the first slide; now `longSwipesRatio` 0.15 + `resistance: false` (it rewinds). Card sliders: autoplay on by
  default (resolver, seeds, migration `2026_10_27_120000_turn_on_home_slider_autoplay`; the per-section toggle stays),
  `disableOnInteraction: false`, rewind at the end, paused off screen / on keyboard focus / on hover. Galleries
  `longSwipesRatio` 0.2. Verified in a browser (mouse and touch).
