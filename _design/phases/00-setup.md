# Phase 00 — Project setup & tooling

Read first: `CLAUDE.md` (root) and `_design/PROGRESS.md`.
Goal: a clean, running Laravel project in `C:\laragon\www\class-skilled` with every package from
CLAUDE.md §3 installed and configured at a basic level, the build pipeline working, fonts self-hosted,
localization routing working, and quality tools ready. **No features, no UI design yet.**

## Out of scope
Models, migrations for content, dashboard screens, website design. Do not start them.

## Tasks

### 1. Create the Laravel app inside the existing folder
The folder is not empty (it contains `CLAUDE.md` and `_design/`), so `composer create-project` cannot
target it directly:
1. `composer create-project laravel/laravel _tmp` inside the project folder.
2. Move everything from `_tmp/` (including dotfiles: `.env`, `.env.example`, `.gitignore`, `.editorconfig`,
   `.gitattributes`) to the project root. Do not overwrite `CLAUDE.md` or `_design/`. Delete `_tmp/`.
3. Verify `php artisan --version`, `php -v` (must be ≥ 8.2), `node -v`, `npm -v`.

### 2. Environment
- `.env`: `APP_NAME="Class Skilled"`, `APP_URL=http://class-skilled.test`, `APP_LOCALE=ar`,
  `APP_FALLBACK_LOCALE=ar`, `APP_TIMEZONE=Asia/Dubai`, DB = Laragon MySQL (`class_skilled`, root, empty password),
  `CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=log`, `FILESYSTEM_DISK=public`.
- Add custom keys (also to `.env.example` with comments): `ADMIN_PATH=cs-panel`, `ADMIN_NAME`, `ADMIN_EMAIL`,
  `ADMIN_PASSWORD` (used by the seeder in phase 01/02).
- Create the database `class_skilled` (utf8mb4_unicode_ci) if missing. Set `Schema::defaultStringLength(191)` only if needed.
- `config/app.php`: timezone from env, locale `ar`, faker locale `ar_SA`.

### 3. Git
- `git init`, sensible `.gitignore` (Laravel default + `/public/build`, `/storage/*.key`, `.phpunit.cache`).
  Keep `_design/` tracked (it is reference material) but it must never be web-accessible (it is outside `public/`).
- First commit after this phase.

### 4. Install packages (CLAUDE.md §3)
Composer: fortify, mcamara/laravel-localization, spatie (translatable, sluggable, medialibrary, settings,
permission, translation-loader, sitemap, responsecache, activitylog, honeypot, simple-excel), intervention/image,
mews/purifier, blade-ui-kit/blade-heroicons. Dev: pint, larastan, pest + pest-plugin-laravel, laravel-debugbar.
npm: swiper, notyf, sweetalert2, @fontsource/cairo, @fontsource/tajawal, sortablejs, tom-select, flatpickr,
chart.js, @tiptap/core, @tiptap/starter-kit, @tiptap/extension-link, @tiptap/extension-placeholder.
Publish configs/migrations of each package (do not run feature-specific setup beyond defaults yet).
Record every installed version in PROGRESS.md.

### 5. Localization skeleton
- Configure `mcamara/laravel-localization`: supported locales `ar` (native "العربية", script Arab, dir rtl)
  and `en` (dir ltr); `hideDefaultLocaleInURL = true`; `useAcceptLanguageHeader = false`.
- Middleware group for localized public routes (`localize`, `localizationRedirect`, `localeViewPath` not needed).
- `lang/ar/routes.php` and `lang/en/routes.php` with ALL segments from CLAUDE.md §9.
- Register the full public route map from CLAUDE.md §9 with `LaravelLocalization::transRoute()`, each pointing to a
  temporary `PlaceholderController` that renders a minimal view showing the route name + current locale + dir.
  Custom page catch-all `pages.show` registered last.
- Dashboard routes group: prefix `config('admin.path')` (new `config/admin.php` reading `ADMIN_PATH`), not localized
  by URL; dashboard locale stored in session (default `ar`). One placeholder dashboard route for now.
- Verify: `/`, `/en`, `/الخدمات`, `/en/services`, `/en/projects/category/test` all resolve.
  Confirm `php artisan route:trans:cache` works (needed in production).

### 6. Front-end build pipeline
- Two Vite entries: `resources/css/site.css` + `resources/js/site/app.js`; `resources/css/admin.css` + `resources/js/admin/app.js`.
- Tailwind configured (v4: `@theme` tokens in CSS). Define the colour/typography/radius tokens from CLAUDE.md §6
  as CSS variables with defaults (they will later be overridden from settings).
- Fonts: `resources/css/fonts.css` importing only the needed `@fontsource` files: Cairo 600/700/800 and
  Tajawal 400/500/700 (arabic + latin subsets), `font-display: swap`. Font families exposed as CSS variables
  `--font-head` / `--font-body`.
- Create empty module files listed in CLAUDE.md §8 for `resources/js/site/*` and `resources/js/admin/*` with a short
  header comment (they will be filled by later phases). Swiper must be dynamically imported (code-split).
- `npm run build` must succeed. `@vite` used in placeholder layouts.

### 7. Folder structure
Create the folders from CLAUDE.md §8 (with `.gitkeep` where empty). Create base layouts
`layouts/site.blade.php`, `layouts/admin.blade.php`, `layouts/auth.blade.php` as minimal shells
(`<html lang dir>` from current locale, `@vite`, `@livewireStyles/@livewireScripts`, a `@stack('head')`).

### 8. Quality tooling
- `pint.json` (Laravel preset), `phpstan.neon` (larastan, level 5, paths app/), Pest installed with one smoke test
  (home returns 200 in both locales, dashboard placeholder redirects or returns 200).
- `AppServiceProvider`: `Model::shouldBeStrict(! app()->isProduction())`, `Model::preventLazyLoading(...)`.
- `composer.json` scripts: `lint` (pint), `analyse` (phpstan), `test`.

### 9. Copy brand assets to public
Copy `_design/brand/{logo,logo-white,mark,mark-white}.png` to `public/images/brand/` (defaults until uploaded via dashboard).

## Acceptance checklist
- [ ] `http://class-skilled.test` shows the placeholder in Arabic RTL; `/en` in English LTR
- [ ] All route names from §9 resolve in both locales; `route:trans:cache` works
- [ ] `npm run build` OK; fonts load locally (no Google Fonts requests)
- [ ] Pint, Larastan, Pest pass; `migrate:fresh` works on the empty schema
- [ ] PROGRESS.md: versions, decisions, next phase = 01
- [ ] Commit `phase-00: project setup & tooling`
