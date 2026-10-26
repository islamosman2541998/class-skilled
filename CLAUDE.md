# Class Skilled Website — Project Rules for Claude Code

This file is read at the start of every session. It defines the project, the stack, the
non-negotiable rules, the design system and the conventions. The work itself is split into
phases in `_design/phases/`. **Work on exactly one phase per session.**

---

## 1. The project

Corporate bilingual website + custom admin dashboard for:

- **Class Skilled General Contracting L.L.C – S.P.C** (كلاس سكيلد للمقاولات العامة ذ.م.م – ش.ش.و)
- Abu Dhabi, UAE — Mussafah M44, Building A, Office 10
- Trade licence CN-5898460 (expires 11/05/2029), ADCCI membership 8800105771
- Classification: Fifth category, Building Contracting, Abu Dhabi City Municipality (DMT), valid until 25/03/2028
- Phones: +971 58 982 0017, +971 52 764 9086 — Email: classskilled@gmail.com
- Services: villas & residential buildings, commercial & government buildings, interior & exterior
  finishing, epoxy flooring, floor & roof insulation, acoustic insulation, film studio fit-out &
  production sets, infrastructure/concrete/renovation.

Two parts in ONE Laravel app:
1. **Public website** (Arabic default, RTL; English under `/en`, LTR).
2. **Custom admin dashboard** built with Livewire (NOT Filament, NOT Nova, NOT any admin kit).

**Golden rule: nothing on the public website is static.** Every text, image, link, menu,
section, SEO field, contact detail and UI label comes from the database and is editable from
the dashboard, in Arabic and English.

---

## 2. Environment

- Windows + **Laragon**. Project path: `C:\laragon\www\class-skilled` → local URL `http://class-skilled.test`
- MySQL from Laragon: host `127.0.0.1`, port `3306`, user `root`, empty password, database `class_skilled`
  (`utf8mb4` / `utf8mb4_unicode_ci`).
- Mail in local: `MAIL_MAILER=log`.
- Use the terminal Claude Code provides (Git Bash or PowerShell). Prefer cross-platform commands.
  Paths in code must use `base_path()`, `storage_path()`, etc. — never hardcoded Windows paths.

### Production target = ordinary shared hosting
The final site must run on plain shared hosting (cPanel/Hostinger-like). Therefore:
- **No Redis, no queue worker, no Supervisor, no cron, no FFmpeg, no Node on the server.**
- `CACHE_STORE=file`, `SESSION_DRIVER=file` (or `database`), `QUEUE_CONNECTION=sync`.
- Anything that would normally be queued runs inline or with `dispatch(...)->afterResponse()`
  (e.g. emails) so the visitor never waits.
- Anything that would normally be scheduled is done **on write** (observers) or **on read**
  (e.g. scheduled publishing = query `published_at <= now()`).
- Images are processed with **GD** only (Intervention Image v3 + spatie/laravel-medialibrary,
  conversions `->nonQueued()`).
- Videos are stored as uploaded (MP4/WebM). No server transcoding. Large videos → YouTube/Vimeo embed.
- Front-end assets are built locally with Vite (`npm run build`) and the `public/build` folder is deployed.

---

## 3. Stack (use latest stable versions that composer/npm resolve; adapt to their current APIs)

Backend
- PHP 8.2+ (check `php -v` in Laragon; switch Laragon PHP if lower)
- Laravel (latest stable) — MySQL
- Livewire (latest stable, v3+) — Alpine.js comes bundled with Livewire, do NOT install Alpine separately
- `laravel/fortify` — auth + two-factor (TOTP) with our own custom views
- `mcamara/laravel-localization` — `/` Arabic (hidden default), `/en` English, translated route segments
- `spatie/laravel-translatable` — JSON translatable columns
- `spatie/laravel-sluggable` — `HasTranslatableSlug` (unique slug per locale)
- `spatie/laravel-medialibrary` + `intervention/image` — files & conversions (GD)
- `spatie/laravel-settings` — typed settings groups
- `spatie/laravel-permission` — roles: `admin`, `editor`, `sales`
- `spatie/laravel-translation-loader` — UI strings stored in DB (`language_lines`), editable from dashboard
- `spatie/laravel-sitemap` — generated on content change
- `spatie/laravel-responsecache` — full-page cache for guests, cleared on any content/settings change
- `spatie/laravel-activitylog` — audit log
- `spatie/laravel-honeypot` — spam protection on public forms (+ optional Cloudflare Turnstile)
- `spatie/simple-excel` — streamed XLSX export (no queue)
- `mews/purifier` — sanitize rich text HTML
- `blade-ui-kit/blade-heroicons` — functional icons only (see design rules)
- Dev: `laravel/pint`, `larastan/larastan`, `pestphp/pest` (+ Laravel plugin), `barryvdh/laravel-debugbar` (local only)

Front-end (npm, bundled by Vite)
- Tailwind CSS (the version the Laravel skeleton ships; v4 expected → tokens via `@theme`)
- `swiper` (v11+) — hero slider + card sliders
- `notyf` — toasts (site + dashboard)
- `sweetalert2` — confirmations & warnings (site + dashboard)
- `@fontsource/cairo`, `@fontsource/tajawal` — self-hosted fonts (Arabic + Latin subsets)
- Dashboard only: `sortablejs` (drag & drop), `tom-select` (searchable selects), `flatpickr` (dates, with Arabic locale),
  `chart.js` (dashboard charts), `@tiptap/core` + `@tiptap/starter-kit` + `@tiptap/extension-link` (rich text)

Do not add any other package without asking first and explaining why.
If a listed package is incompatible with the installed Laravel version, stop and report the options.

Optional but recommended: `laravel/boost` (MCP + Laravel docs for Claude Code). If installed, keep
THIS file's content at the top; Boost guidelines live in their own tagged block below it.

---

## 4. Reference material (folder `_design/`)

- `_design/phases/` — the phase prompts (00 → 10). Execute one per session.
- `_design/PROGRESS.md` — phase log. **Read it first in every session, update it at the end of every phase.**
- `_design/screens/website/*.jpg` — website reference screens (layout, structure, content order).
- `_design/screens/dashboard/*.jpg` — dashboard reference screens (custom Livewire dashboard).
- `_design/photos/*.jpg` — real company photos (seed content, hero slides, galleries).
- `_design/brand/` — `logo.png`, `logo-white.png`, `mark.png`, `mark-white.png`, `brand-guidelines.pdf`.
- `_design/docs/` — licence PDF, classification certificate PDF, company profile PDF (web-compressed, 2.9 MB), letterheads.
- `_design/content/content.md` — approved Arabic/English seed copy.

**The screens are a structural reference, not a pixel spec.** Where this file differs from the
screens, THIS FILE WINS. In particular (newer decisions than the screens):
- Typography is **smaller** than in the screens (see §6.2). No huge headlines anywhere.
- The home hero is a **Swiper slider** (autoplay + arrows + swipe/drag), not a static split hero.
- Home sections that show cards (services, projects, articles, testimonials, certificates/clients)
  are **Swiper card sliders with a refined glass design** (see §6.5).
- The home page has **scroll animations** on both desktop and mobile (see §6.6).
- `_design/` is never served publicly and is excluded from the deploy package.

---

## 5. Non-negotiable rules

1. **One phase per session.** Read `CLAUDE.md` + `_design/PROGRESS.md` + the phase file. Finish the
   whole phase, verify it, update PROGRESS.md, commit. Never start the next phase on your own.
2. **Do not break or rewrite earlier phases.** If a change to earlier code is truly needed, make the
   minimal change and record it under "Changes to earlier phases" in PROGRESS.md.
3. **No hardcoded user-facing text** in Blade/PHP/JS. Use model fields or `__('group.key')`. Every UI
   string key is seeded in `language_lines` with Arabic and English values and editable in the dashboard.
   (Dashboard chrome labels may live in `lang/ar|en/admin.php` files.)
4. **Bilingual everywhere.** Every content field that a visitor can read is translatable (ar required,
   en optional but flagged as "missing" in the dashboard). Slugs are translatable. Dates/numbers are
   formatted per locale (Arabic UI keeps Western digits 0-9 for phone numbers, sizes and prices).
5. **RTL/LTR by logical properties only.** Use `ms-/me-/ps-/pe-/start-/end-`, `text-start`, `rtl:`/`ltr:`
   variants. Never hardcode `left/right` except for physical things that must not flip (e.g. phone numbers
   are wrapped in `<bdi dir="ltr">`).
6. **SEO wherever there is a public page.** Every page-type model uses the `HasSeo` concern.
7. **Security**: validate everything (Livewire Form objects / Form Requests), authorize everything
   (Policies + permissions), sanitize rich text with Purifier, private disk for visitor uploads, rate
   limiting on forms and login, 2FA available for admins, no `{!! !!}` on unsanitized data.
8. **Performance**: no N+1 (`Model::preventLazyLoading()` in non-production), eager load, index foreign
   keys/slugs/status/sort columns, cache settings and menus, full-page response cache for guests.
9. **Accessibility**: semantic HTML, alt text required for images, visible focus states, keyboard-usable
   sliders/menus/modals, `prefers-reduced-motion` respected.
10. **Quality gates at the end of every phase**: `php artisan migrate:fresh --seed` works,
    `./vendor/bin/pint` clean, `./vendor/bin/phpstan analyse` (larastan level 5) clean, `php artisan test`
    green, `npm run build` succeeds, no errors in `storage/logs/laravel.log`.
11. **Git**: commit at the end of each phase: `phase-NN: <short summary>`.

---

## 6. Design system (public website)

### 6.1 Colour tokens (defaults; editable in Dashboard → Appearance)
| Token | Hex | Use |
|---|---|---|
| `--c-navy` (Class Navy) | `#2B4168` | headings, structure, secondary buttons |
| `--c-deep` (Deep Navy) | `#0C122B` | top bar, footer, dark sections |
| `--c-sky` (Skilled Sky) | `#4FC3EE` | primary action (main CTA), active states, slider progress |
| `--c-ink` | `#24324D` | body text |
| `--c-muted` | `#5D6A80` | secondary text |
| `--c-line` (Concrete) | `#E8ECF1` | borders, dividers |
| `--c-paper` | `#F5F7FA` | alternate section background |
Colours are output as CSS variables in the layout from settings; Tailwind theme tokens reference the variables.
Primary button = sky background + deep-navy text (white on sky fails contrast).

### 6.2 Typography — restrained, never oversized
Fonts self-hosted. Heading font and body font are chosen in Dashboard → Appearance (Cairo or Tajawal each).
Defaults: headings **Cairo**, body **Tajawal**. Only the chosen families are used, so only they download.

| Role | Desktop (≥1024) | Mobile (<640) | Weight |
|---|---|---|---|
| Hero title | 40px / 1.3 | 26px / 1.35 | 800 |
| Page title (inner page header) | 32px / 1.35 | 24px / 1.4 | 800 |
| Section title | 28px / 1.4 | 21px / 1.45 | 800 |
| Card title | 17px / 1.5 | 16px / 1.5 | 700 |
| Lead paragraph | 16px / 1.85 | 15px / 1.8 | 400, muted |
| Body | 15.5px / 1.85 | 15px / 1.8 | 400 |
| Small / meta | 13.5px | 13px | 400–500 |
| Nav links | 14.5px | 16px (mobile menu) | 600 |
| Buttons | 14.5px, height 46px | 14px, height 44px | 700 |
English (LTR) body line-height 1.6–1.65. Use `clamp()` between mobile and desktop sizes.
Max text line length ~70ch. No ALL-CAPS labels, no tiny "eyebrow" labels above every heading,
no single highlighted word inside headings.

### 6.3 Layout & spacing
- Container 1200px + side gutter 32px desktop / 16px mobile. No horizontal scroll at any width.
- Section vertical padding 88px desktop / 56px mobile.
- Radius: buttons pill (`9999px`); inputs 12px; images 14px; glass cards 20px.
- Breakpoints: Tailwind defaults (sm 640, md 768, lg 1024, xl 1280).

### 6.4 Visual language
- Real project photography is the hero of the design. Large, well-cropped images.
- Brand colours only. No random gradients, no glow effects, no emoji, no decorative icons.
- **Functional icons only** (inline Heroicons): slider arrows, menu/close, chevrons for dropdowns,
  play button, phone/WhatsApp in contact actions, search, file type. Never icons as decoration next to headings.
- Numbered markers only for real sequences (the "How we work" steps, the quote form steps).
- Glass (§6.5) is reserved for: slider cards, slider controls, the hero text panel, the sticky header
  after scroll, and the mobile bottom action bar. Everything else stays clean and solid.

### 6.5 Sliders (Swiper) and the glass card style
**Hero slider** (home, first section)
- Full-bleed, height `min(86vh, 760px)` desktop, `78svh` mobile. Slides = image (or short muted looping
  video) with a solid dark overlay `rgba(12,18,43,.45)`; each slide has title, text, primary + secondary CTA,
  optional caption. All from dashboard.
- `effect: 'fade'` (crossFade) + slow Ken Burns zoom on the active slide image (scale 1.08 → 1 over 7s).
- Autoplay 6s, `pauseOnMouseEnter`, `disableOnInteraction: false`; loop; keyboard; a11y module.
- **Arrows** (prev/next) as round glass buttons, RTL-aware; **swipe/drag** with `grabCursor`;
  pagination = small bars where the active bar fills as a progress indicator of the autoplay timer.
- Text sits in a glass panel at the start side; its lines animate in (stagger 80ms) on every slide change.
- First slide image is preloaded (`fetchpriority="high"`), other slides lazy. Separate optional mobile image per slide.

**Card sliders** (services, projects, articles, testimonials, certificates, clients)
- `slidesPerView`: 1.15 (mobile) → 2.2 (≥640) → 3.2 (≥1024) → 3.5 (≥1280); `spaceBetween` 16/20/24.
- Arrows (glass round buttons in the section header, end side) + bullets/progress; swipe/drag; keyboard.
- Section background for glass to look right: deep navy with a large darkened project photo
  (overlay `rgba(12,18,43,.78)`), selectable per section in the dashboard (dark-photo / dark / light-paper).
- **Glass card (dark sections)**: `background: rgba(255,255,255,.08)`; `border: 1px solid rgba(255,255,255,.16)`;
  `backdrop-filter: blur(16px) saturate(140%)`; radius 20px; shadow `0 24px 48px -24px rgba(0,0,0,.55)`;
  inner image radius 14px; title white, text `#C9D3E3`. Hover/focus: lift 4px, border `rgba(79,195,238,.55)`,
  image scale 1.04 (300ms). 
- **Glass card (light sections)**: `background: rgba(255,255,255,.62)`; border `rgba(255,255,255,.7)`;
  same blur; shadow `0 20px 40px -24px rgba(12,18,43,.35)`; text in navy/ink.
- Fallback when `backdrop-filter` is unsupported: solid `rgba(12,18,43,.9)` (dark) / `#fff` (light).
- Swiper is loaded only on pages that use it (dynamic `import()`), initialised through Alpine components
  so it survives Livewire updates; `dir` taken from `<html dir>`.

### 6.6 Motion (home page, desktop AND mobile)
- One small vanilla util (`resources/js/site/reveal.js`) using IntersectionObserver; no GSAP/AOS.
- `data-reveal` variants: `fade-up` (opacity 0→1, translateY 18px→0), `fade`, `image` (clip-path inset 6% → 0
  and scale 1.06 → 1), `stagger` (children 70ms apart). Duration 650ms desktop / 480ms mobile,
  easing `cubic-bezier(.2,.7,.2,1)`, runs once, threshold 0.15.
- Header: transparent over the hero, becomes solid/glass and shrinks (76→64px) after 24px scroll.
- Process steps: the connecting line draws on reveal, steps appear in sequence.
- Count-up for number facts when visible.
- Everything disabled under `prefers-reduced-motion: reduce`. No layout shift (reserve space).

### 6.7 Mobile
- Elegant, one-thumb: sticky glass bottom bar with three actions (Call / WhatsApp / Request a quote).
- Off-canvas menu from the start side, accordion sub-menus, large tap targets (≥44px), close button, language switch.
- Top bar hidden on mobile; header 60px.
- Sliders show a peek of the next card (1.15 per view). Grids collapse to one column.

### 6.8 Toasts & alerts
- `Notyf` for toasts (top-end on desktop, top-center on mobile). Livewire components dispatch
  `toast` events `{type: success|error|warning|info, message}` → one global listener in each layout.
- `SweetAlert2` for confirmations (delete, discard changes, irreversible actions) with brand styling and
  RTL. Pattern: Alpine helper `$confirm({title, text, confirmText, danger})` returning a Promise, then `$wire.call(...)`.

---

## 7. Dashboard design (custom Livewire)
Follow `_design/screens/dashboard/*.jpg`:
- Right sidebar (start side, RTL) in deep navy `#0C122B`, 268px, collapsible groups, active item navy
  with a sky inset bar, count badges in sky. Topbar white 64px: global search (Ctrl/Cmd+K), "View site",
  language toggle, notifications, user menu.
- Content area `#F5F7FA`, white panels with 1px `#E3E8EF` border, radius 12px, 14–15px text.
- Dashboard UI is Arabic by default with an English toggle (dashboard chrome strings in `lang/*/admin.php`).
- Navigation uses `wire:navigate` (no full reloads). All lists/forms are Livewire; no page reloads on
  search, filter, sort, paginate, save, reorder, upload.
- Dashboard URL prefix comes from `.env` `ADMIN_PATH` (default `cs-panel`), never `/admin`.

---

## 8. Code organisation & conventions

```
app/
  Actions/                 single-purpose actions (SubmitQuoteRequest, ReorderItems, ExportToExcel ...)
  Enums/                   PublishStatus, MenuLocation, MenuItemType, DynamicSource, DynamicMode,
                           Visibility, AssetKind, HeroMediaType, SectionType, SectionBackground, RequestType ...
  Models/                  Eloquent models
  Models/Concerns/         HasSeo, HasTranslatedSlug, HasAssets, Publishable, Sortable, ClearsSiteCache
  Settings/                spatie settings classes (one per group)
  Services/                MenuResolver, SeoManager, SchemaBuilder, HomeSectionResolver, AssetUploader,
                           SiteCache, Sitemap, Analytics
  Observers/               cache/sitemap/redirect side effects
  Policies/
  Http/Controllers/Site/   thin controllers for public pages
  Livewire/Site/           public interactive components (QuoteForm, ContactForm, ProjectsIndex, PostsIndex, JobApplicationForm, SearchPage)
  Livewire/Admin/          dashboard screens, grouped by module: Requests/, Content/, Navigation/, Media/, Seo/, Settings/, System/
  Livewire/Admin/Concerns/ WithDataTable, WithTranslatableForm, WithAssetPicker, WithSortable, WithToasts
  Livewire/Forms/          Livewire Form objects
  View/Components/         Site/* and Admin/* class components
resources/
  css/site.css  css/admin.css  css/fonts.css
  js/site/{app.js, reveal.js, sliders.js, header.js, mobile-nav.js, toast.js, confirm.js, track.js}
  js/admin/{app.js, sortable.js, editor.js, select.js, datepicker.js, charts.js, uploader.js, toast.js, confirm.js}
  views/
    layouts/site.blade.php  layouts/admin.blade.php  layouts/auth.blade.php
    site/partials/  (head, topbar, header, mobile-nav, footer, cta-band, bottom-bar, breadcrumbs, seo)
    site/sections/  (one file per home section type)
    site/blocks/    (one file per content block type)
    site/pages/     (home, services/*, projects/*, posts/*, careers/*, quote, contact, faq, search, page, errors)
    components/site/*  components/admin/*
    livewire/site/*    livewire/admin/**
lang/ar/*.php lang/en/*.php   (route segments, admin chrome, validation)
```
- Controllers thin; logic in Actions/Services. Enums are backed string enums with `label()` methods.
- Every model with public pages: `HasSeo`, translatable fields, translatable slug, `Publishable` where relevant.
- Each Blade section/block lives in its own file. Shared UI → Blade components.
- Naming: tables snake_case plural, Livewire components `Admin\Content\Projects\Index|Form`.
- Write PHPDoc for model properties (helps larastan).

## 9. Public route map (named routes, translated segments; Arabic default without prefix, English `/en/...`)
| Name | Arabic URL | English URL |
|---|---|---|
| `home` | `/` | `/en` |
| `services.index` / `services.show` | `/الخدمات` / `/الخدمات/{slug}` | `/en/services` / `/en/services/{slug}` |
| `projects.index` / `projects.category` / `projects.show` | `/المشاريع` / `/المشاريع/تصنيف/{slug}` / `/المشاريع/{slug}` | `/en/projects` / `/en/projects/category/{slug}` / `/en/projects/{slug}` |
| `posts.index` / `posts.category` / `posts.show` | `/المقالات` / `/المقالات/تصنيف/{slug}` / `/المقالات/{slug}` | `/en/articles` / ... |
| `careers.index` / `careers.show` | `/الوظائف` / `/الوظائف/{slug}` | `/en/careers` / `/en/careers/{slug}` |
| `quote` | `/طلب-عرض-سعر` | `/en/request-a-quote` |
| `contact` | `/تواصل-معنا` | `/en/contact` |
| `faq` | `/الأسئلة-الشائعة` | `/en/faq` |
| `search` | `/بحث` | `/en/search` |
| `pages.show` | `/{slug}` (custom pages, registered LAST) | `/en/{slug}` |
| `track` (POST, beacon) | `/t` | — |
| `sitemap` | `/sitemap.xml` | — |
Segments live in `lang/ar/routes.php` and `lang/en/routes.php`.

## 10. Definition of done for every phase
- All tasks in the phase file done; acceptance checklist ticked in PROGRESS.md.
- Quality gates (§5.10) pass.
- Manual check at 390px and 1440px widths in Arabic and English for any UI touched.
  If a browser automation tool (e.g. Playwright MCP) is available, take screenshots and compare
  against `_design/screens`.
- PROGRESS.md updated: what was built, files/areas touched, decisions, known gaps, next phase.
- Commit `phase-NN: ...`. Then stop and report.
