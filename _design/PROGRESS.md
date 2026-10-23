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
| 07 Inner pages | not started | | |
| 08 Forms & requests | not started | | |
| 09 SEO, analytics, dashboard home | not started | | |
| 10 Hardening & deploy | not started | | |

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
- Dashboard: Fortify routes live under ADMIN_PATH (login, reset, 2FA challenge); registration disabled; 2FA
  enable/confirm/disable done in "My account" with Fortify actions + current-password check.
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

## Changes to earlier phases
- Phase 00: `SetupTest` placeholder dataset no longer lists `home` (now a real page, covered by `SiteLayoutHomeTest`);
  `placeholder.blade.php` wraps its content in the site container.
- Phase 03: `AppServiceProvider` registers `SiteContext`/`SeoManager` and an extra view composer.

## Known gaps / follow-ups
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
