# Phase 01 — Data model & core infrastructure (backend only)

Read first: `CLAUDE.md`, `_design/PROGRESS.md`.
Goal: the **complete database schema**, all models, enums, concerns (traits), settings classes, the asset (media)
system, SEO/slug/redirect infrastructure, cache invalidation and essential seeders. After this phase later phases
should **not need new tables** (only in exceptional cases, recorded in PROGRESS.md).

## Out of scope
Any dashboard screen, any website design. Only minimal tinker/test verification.

## 1. Enums (`app/Enums`, backed string enums, each with `label(): string` translated)
`PublishStatus` (draft, published, scheduled — scheduled derived from `published_at > now()`),
`AssetKind` (image, video, document, embed), `EmbedProvider` (youtube, vimeo),
`HeroMediaType` (image, video, embed, pdf, none), `SectionType` (hero, facts, about, services, feature_band,
projects, why_us, process, stats, testimonials, clients, posts, certificates, cta, rich_text),
`SectionBackground` (white, paper, dark, dark_photo), `DataSource` (manual, all, featured, latest),
`MenuLocation` (header, topbar, footer_1, footer_2, footer_3, footer_legal, mobile),
`MenuItemType` (home, page, system_page, model, external, heading, dynamic_group),
`DynamicSource` (services, projects, project_categories, posts, post_categories, pages),
`Visibility` (all, desktop, mobile), `ProjectStatus` (completed, ongoing),
`RequestType` (quote, message, application), `ActivityType` (note, status, assign, system, email),
`JobType` (full_time, part_time, contract), `BlockType` (rich_text, media_text, gallery, video, cards, faq, cta,
files, quote, two_columns, values_list).

## 2. Concerns (`app/Models/Concerns`)
- `HasTranslatedSlug`: wraps spatie sluggable `HasTranslatableSlug` (slug from title, per locale, unique per locale);
  on slug change creates a 301 `Redirect` from old localized URL to the new one (per locale), avoiding chains/loops.
- `HasSeo`: `morphOne(SeoMeta)`; helpers `seoTitle()`, `seoDescription()`, `seoImage()` with fallbacks
  (model title/excerpt/cover → global SEO settings).
- `HasAssets`: morph-to-many to `Asset` through `assetables` with `collection` + `sort` + `meta` json.
  API: `assetsIn(string $collection)`, `firstAsset(string $collection)`, `syncAssets(string $collection, array $ids)`
  (keeps order), `attachAsset(...)`, `detachAsset(...)`.
- `Publishable`: `status`, `published_at`; scopes `published()` (status published AND published_at <= now()), `draft()`;
  accessor `isScheduled`.
- `Sortable`: `sort` int, global ordering scope option, static `reorder(array $ids)` in one transaction.
- `ClearsSiteCache`: on saved/deleted → `SiteCache::flush()` (response cache clear + menu/settings caches) and
  `Sitemap::regenerate()` for page-type models (synchronously, guarded against running twice per request).
- Activity log on all admin-editable models (`LogsActivity`, only dirty attributes).

## 3. Asset system (central media library)
- `media_folders`: id, parent_id nullable, name (json translatable), sort, timestamps.
- `assets`: id, folder_id nullable, kind (AssetKind), original_name, mime, size (bytes), width, height,
  duration (seconds, video), external_url (embed), provider (EmbedProvider nullable), poster_asset_id nullable (self FK),
  alt (json), title (json), caption (json), focal (string: top|center|bottom, default center), uploaded_by, timestamps, soft deletes.
  Uses spatie medialibrary `HasMedia` with ONE collection `file` (single file).
- Media conversions on `Asset` (images only, all `->nonQueued()`, format **webp**, GD): `thumb` 320×240 crop (focal),
  `sm` 640w, `md` 1024w, `lg` 1600w, `xl` 2048w; `->withResponsiveImages()` off (we use our own fixed widths).
  Quality from `MediaSettings::webp_quality` (default 80).
- `assetables`: id, asset_id, assetable_type, assetable_id, collection (string), sort, meta (json, e.g. title override),
  unique (asset_id, assetable_type, assetable_id, collection). Indexes on morph + collection.
- `App\Services\AssetUploader`:
  - images: validate mime (jpeg/png/webp), max size from settings; downscale original to max dimension
    (`MediaSettings::max_image_dimension`, default 2560) with Intervention (GD) BEFORE adding to medialibrary; strip EXIF,
    auto-orient; store width/height; generate conversions; return Asset.
  - video: mp4/webm only, max size from settings, store as-is; duration + poster provided by the client (poster is an image Asset).
  - document: pdf only (plus dwg/zip only for private visitor uploads, not in the library).
  - embed: parse YouTube/Vimeo URL → provider + id, require poster asset or fetch nothing remotely (no outbound calls).
- Blade component `<x-site.picture :asset :sizes :class :eager>` → `<picture>` with `srcset` (sm/md/lg/xl webp),
  width/height attrs, `loading="lazy"` unless eager, `decoding="async"`, alt from current locale (fallback ar), focal → `object-position`.
- Visitor uploads (quote drawings, CVs) use a **private** disk `private` (storage/app/private) via medialibrary on the
  request models, served only through signed, authorized dashboard routes.

## 4. Tables & models (all translatable fields = json via spatie translatable)
- `seo_meta`: seoable morph, title, description, canonical (json), og_asset_id, robots (index|noindex), timestamps.
- `redirects`: from_path (unique, indexed), to_path, status_code (301/302), hits, is_auto, last_hit_at.
- `pages`: title, slug, excerpt (description), hero_type, blocks (json, see §6), attachments_config (json: groups
  files|videos|images → {visible, title{ar,en}, display}), parent_id, status, published_at, show_in_sitemap, sort.
  Assets collections: `hero_image`, `hero_mobile_image`, `hero_video`, `hero_pdf`, `att_files`, `att_videos`, `att_images`.
- `services`: title, slug, excerpt, summary (short for menus/cards), blocks (json), scope (json list per locale),
  stages (json list per locale), is_featured, is_active, sort. Assets: `cover`, `gallery`. FAQs via morph.
- `project_categories`: name, slug, sort, is_active.
- `projects`: title, slug, category_id, location (json), area_m2 (decimal nullable), duration (json, e.g. "7 أسابيع"),
  year, project_status (ProjectStatus), client_name (json), show_client (bool), excerpt, blocks, scope (json list),
  is_featured, status, published_at, sort. Pivot `project_service`. Assets: `cover`, `gallery`, `before_after` (meta: pair index, side), `videos`.
- `post_categories`: name, slug, sort. `posts`: title, slug, excerpt, body (json html per locale), post_category_id,
  author_id, reading_minutes (computed on save), is_featured, status, published_at. Assets: `cover`.
- `faqs`: question, answer, faqable morph nullable (null = general FAQ page), is_active, sort.
- `testimonials`: name (json), company (json), body, rating (1–5 nullable), project_id nullable, is_active, sort. Asset `photo`.
- `clients`: name, url nullable, is_active, sort. Asset `logo`.
- `team_members`: name, role, bio, is_active, sort. Asset `photo`.
- `certificates`: name, number, authority, issued_at, expires_at, verify_url, show_on_site, sort. Assets `file` (pdf), `image`.
- `job_posts` (table name avoids clash with Laravel `jobs`): title, slug, department, job_type, location, description (json html),
  requirements (json list), status, published_at, closes_at, sort.
- `home_sections`: type (SectionType), title, subtitle, cta_label, cta_link (json link object, see §7), data (json per type),
  source (DataSource), items_limit, background (SectionBackground), is_visible, sort. Assets via collections named per type
  (e.g. `slides.{n}.image`, `bg_image`, `images`).
- `menus`: location (MenuLocation, unique), name (json), is_active.
- `menu_items`: menu_id, parent_id nullable (max depth 2), type (MenuItemType), label (json, nullable = inherit),
  linkable morph nullable, system_route (nullable route name), url (nullable, external), dynamic_source, dynamic_mode
  (DataSource), dynamic_limit, show_all_link (bool), target_blank, visibility, is_active, sort.
- `menu_item_entries`: menu_item_id, entry morph, sort (manual picks for dynamic groups).
- `request_statuses`: request_type (RequestType), key, name (json), color (one of: sky, amber, violet, navy, green, red, gray),
  is_default, is_final, sort. Seed defaults (quote: new, contacted, visit, quote_sent, won, lost, cancelled;
  message: new, read, replied, closed; application: new, shortlisted, interview, accepted, rejected).
- `quote_requests`: number (QR-0001 sequential), name, phone, email, service_id nullable, facility_type, project_state,
  emirate, area_text, area_m2, start_when, description, locale, status_id, assigned_to nullable, follow_up_at,
  source (json: page, referrer, utm_*, device), ip_hash, read_at, timestamps, soft deletes. Private media `attachments`.
- `contact_messages`: name, phone, email, subject, message, locale, status_id, assigned_to, source json, ip_hash, read_at, soft deletes.
- `job_applications`: job_post_id nullable, name, phone, email, specialty, message, status_id, assigned_to, locale, ip_hash,
  read_at, soft deletes. Private media `cv`.
- `request_activities`: subject morph (quote/message/application), user_id nullable, type (ActivityType), body, meta json.
- `site_events`: type (whatsapp_click, call_click, email_click, quote_submit, contact_submit, application_submit), path,
  locale, device, utm json, created_at (indexed).
- `page_views_daily`: date, path, locale, views (unique date+path+locale) — incremented by the beacon in phase 09.
- Package tables: settings, permissions, activity_log, language_lines, media.
- `users`: add `locale` (dashboard UI language), `is_active`, `last_login_at`; Fortify 2FA columns.

All FKs indexed; `sort`, `status`, `published_at`, `is_active`, `type` indexed where queried.

## 5. Settings classes (`app/Settings`, spatie/laravel-settings, cached)
`GeneralSettings` (site_name{ar,en}, tagline{ar,en}, logo_asset_id, logo_white_asset_id, favicon_asset_id, default_locale),
`CompanySettings` (legal_name{ar,en}, trade_licence_no, licence_expiry, classification{ar,en}, classification_authority{ar,en},
classification_expiry, adcci_no, founded_year),
`ContactSettings` (phones[], whatsapp_number, whatsapp_message{ar,en}, emails[], address{ar,en}, map_lat, map_lng,
map_embed_url, working_hours[] {days{ar,en}, hours{ar,en}}),
`SocialSettings` (instagram, facebook, linkedin, x, tiktok, youtube, snapchat),
`AppearanceSettings` (heading_font: cairo|tajawal, body_font: cairo|tajawal, base_font_size, line_height_ar,
colors{navy,deep,sky,ink,muted,line,paper}),
`SeoSettings` (title_suffix{ar,en}, default_description{ar,en}, default_og_asset_id, google_verification,
bing_verification, robots_txt, schema extra json),
`TrackingSettings` (ga4_id, gtm_id, meta_pixel_id, head_scripts, body_scripts — admin only, rendered raw),
`NotificationSettings` (quote_emails[], contact_emails[], careers_emails[], from_name, autoreply_enabled,
autoreply_subject{ar,en}, autoreply_body{ar,en} with placeholders {name} {number}),
`FormSettings` (emirates[] {ar,en}, facility_types[] {ar,en}, project_states[] {ar,en}, start_options[] {ar,en},
max_upload_mb, allowed_extensions[], turnstile_site_key, turnstile_secret, honeypot_enabled),
`MediaSettings` (max_image_mb, max_video_mb, max_image_dimension, webp_quality),
`MaintenanceSettings` (enabled, message{ar,en}, allowed_ips[]).
Defaults filled from CLAUDE.md §1 and `_design/content/content.md`.

## 6. Content blocks (json) — schema used by pages, services, projects
`blocks` = array of `{ id: uuid, type: BlockType, data: {...} }`; every text inside `data` is `{ar, en}`.
Define per type in `app/Support/Blocks/*` classes: `rules()`, `defaults()`, `toView()`:
- rich_text {html{ar,en}} — purified on save
- media_text {asset_id, title, html, image_side: start|end}
- gallery {asset_ids[], columns 2|3|4, title}
- video {asset_id, title}
- cards {title, items[{title, text, asset_id?}]}
- two_columns {left{title,html,dark:bool}, right{...}}  (used for vision/mission)
- values_list {title, items[{title, text}]}
- faq {title, faq_ids[] | items[{q,a}]}
- files {title, asset_ids[]}
- quote {text, author}
- cta {title, text, primary{label,link}, secondary{label,link}}

## 7. Link object (json) — reused by menus, CTAs, hero buttons
`{ type: system_page|page|model|external|anchor, route?, model_type?, model_id?, url?, anchor? , target_blank? }`
`App\Support\LinkResolver::url(array $link, ?string $locale)` returns a localized URL or null when the target is
missing/unpublished (callers then hide the element).

## 8. Cache & side effects
`App\Services\SiteCache`: keys for settings, menus per location+locale, home sections per locale;
`flush()` clears them and `ResponseCache::clear()`. Observers on all content models, settings saved events,
asset updates, menu changes → `flush()`.

## 9. Seeders
- `EssentialSeeder` (safe for production): roles & permissions (granular: view/create/update/delete per module,
  `manage settings`, `manage users`, `export requests`), admin user from `.env`, all settings defaults, request statuses,
  menus with default items (header/footer/mobile/legal), home sections in the default order (hero, facts, about,
  services, feature_band, projects, why_us, process, testimonials [hidden], clients [hidden], posts, cta),
  UI strings (`language_lines`) — create a complete list of keys used by the site with ar/en values (buttons, labels,
  form fields, validation-friendly messages, toasts, empty states, 404 text, cookie/privacy notes, etc.).
- `DemoContentSeeder` (local/staging only): imports `_design/photos/*.jpg` as Assets into folder "المشاريع",
  creates the 8 services, 4 project categories, 9 sample projects, 6 sample posts, FAQs, certificates (with the two PDFs
  from `_design/docs` and their real numbers/dates), the "About" page with blocks + attachments, hero slides using
  studio/office/slab photos — all copy from `_design/content/content.md`.
- `DatabaseSeeder` runs Essential always and Demo only when `app()->environment('local')`.

## 10. Tests (Pest)
Slug per locale + auto redirect on change; Publishable scopes incl. scheduled; HasAssets ordering; AssetUploader
(fake disk, image downscale, conversions exist); LinkResolver for each link type; SiteCache flush on model save;
settings defaults load.

## Acceptance checklist
- [ ] `php artisan migrate:fresh --seed` creates everything with demo content and processed images (webp conversions on disk)
- [ ] All models have relations, casts, translatable arrays, PHPDoc; larastan level 5 clean
- [ ] Tests above pass
- [ ] PROGRESS.md documents the schema decisions; commit `phase-01: data model & infrastructure`
