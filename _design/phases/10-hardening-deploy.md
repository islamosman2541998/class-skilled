# Phase 10 — Performance, security, QA & shared-hosting deployment

Read first: `CLAUDE.md`, `_design/PROGRESS.md`.
Goal: production-ready quality and a repeatable deployment to ordinary shared hosting.

## 1. Performance
- Full-page cache (`spatie/laravel-responsecache`) for guest GET requests on public routes; bypass for logged-in users,
  previews, forms with errors, search; cache cleared by `SiteCache::flush()` (verify every observer path).
- Query review with Debugbar on every public page: target ≤ 15 queries uncached, no N+1, no duplicate queries.
- Add missing DB indexes; `php artisan optimize` compatible (config, routes incl. `route:trans:cache`, views, events).
- Images: all `<x-site.picture>`; correct `sizes`; hero preload only on home; lazy elsewhere.
- Fonts: only chosen families/weights; `font-display: swap`; preload one file.
- JS/CSS: code-split Swiper, TipTap/Chart.js only in admin bundle; check bundle sizes and record them.
- Lighthouse mobile ≥ 90 performance and ≥ 95 others on Home, Service, Project, Article, Quote (record results).

## 2. Security
- Security headers middleware: CSP (allow self, GA/GTM/Meta/Turnstile/YouTube/Vimeo/Google Maps only when enabled),
  HSTS (production), X-Frame-Options SAMEORIGIN, Referrer-Policy strict-origin-when-cross-origin, Permissions-Policy.
- Rate limits: login, forms, track, search. Honeypot + Turnstile verified server-side.
- Uploads: MIME sniffing (not extension only), random file names, private disk for visitor files, signed URLs with
  short expiry, no PHP execution in `storage` (add `.htaccess` deny for public storage scripts).
- Purifier on all rich text; escape everything else; mass-assignment guarded.
- Admin: 2FA prompt for admins without 2FA (banner), session timeout setting, `ADMIN_PATH` obscurity, audit log.
- `.env.example` complete; `APP_DEBUG=false` in production; error pages don't leak details.

## 3. QA pass
- Full manual checklist (write it to `_design/QA.md`): every page at 360/390/768/1024/1440 in AR and EN, keyboard-only
  navigation, screen reader landmarks, reduced motion, slow 3G behaviour, forms happy/error paths, dashboard modules CRUD,
  permissions per role, cache flush after edits, translated slug changes create redirects.
- Fix all findings. Ensure test suite covers critical paths (aim ≥ 70% of Actions/Services).

## 4. Deployment to shared hosting (write `DEPLOY.md` in Arabic + English)
- Build locally: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
- Package without `_design/`, `node_modules/`, tests, `.git`.
- Layout on host: app folder outside `public_html` (e.g. `~/class-skilled`), contents of `public/` into `public_html`;
  adjust `public_html/index.php` paths; or point the domain document root to `public` if the host allows.
- `.env` production values (APP_URL https, DB, MAIL SMTP, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`).
- `php artisan migrate --force`, `db:seed --class=EssentialSeeder --force` (first deploy only), `storage:link`
  (fallback: copy-based symlink alternative or a route to serve public storage if symlinks are disabled — implement and
  document the fallback), `optimize`, `route:trans:cache`.
- Permissions for `storage/` and `bootstrap/cache/`. PHP extensions required list (gd, fileinfo, intl, mbstring, zip,
  pdo_mysql, exif). Note: no cron/queue needed.
- Post-deploy checklist: SSL, www/non-www redirect, Google Search Console + sitemap submit, GA4 check, test forms & emails,
  create backup.
- Provide an artisan command `site:check` that verifies the environment (PHP version, extensions, writable dirs, storage
  link or fallback, mail config, cache) and prints a report — useful on the host terminal or via a protected dashboard page
  (System → Health) for hosts without SSH.

## Acceptance checklist
- [ ] Lighthouse and query targets met and recorded
- [ ] Security headers & rate limits verified; QA.md completed with all items passing
- [ ] DEPLOY.md complete; `site:check` command and System → Health page work
- [ ] Final `migrate:fresh --seed`, Pint, Larastan, Pest, build all green
- [ ] PROGRESS.md final summary; commit `phase-10: hardening & deploy`
