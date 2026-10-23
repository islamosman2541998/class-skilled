# Progress log

Update this file at the end of every phase. Keep entries short and factual.

| Phase | Status | Date | Commit |
|---|---|---|---|
| 00 Setup & tooling | done | 2026-10-23 | phase-00 |
| 01 Data model & infrastructure | done | 2026-10-23 | phase-01 |
| 02 Dashboard shell & component kit | done | 2026-10-23 | phase-02 |
| 03 Media, settings, appearance, SEO | not started | | |
| 04 Content modules | not started | | |
| 05 Menu builder | not started | | |
| 06 Site layout & home | not started | | |
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

## Changes to earlier phases
-

## Known gaps / follow-ups
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
