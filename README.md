# Class Skilled

Bilingual Laravel application and custom Livewire dashboard. Phase 00 provides tooling,
translated route placeholders and asset foundations only. Read `CLAUDE.md` and
`_design/PROGRESS.md` before continuing; phase 01 has not started.

## Local setup

The working directory is `D:\laragon\www\class-skilled`. Laravel 12 and PHP 8.3 were
explicitly chosen for package compatibility. Exact versions are locked in Composer/npm
lockfiles and recorded in the progress log.

1. Use PHP 8.3 with GD, EXIF, mbstring, intl, PDO MySQL, PDO SQLite and ZIP enabled.
2. Run `composer install` and `npm ci`.
3. Copy `.env.example` to `.env`, then run `php artisan key:generate`.
4. Create MySQL database `class_skilled` with `utf8mb4_unicode_ci`; configure the local
   connection in `.env` (Laragon defaults: 127.0.0.1:3306, root, empty password).
5. Run `php artisan migrate --seed` and `npm run build`.
6. Point the Laragon virtual host `class-skilled.test` at this project's `public` directory.
   Map `127.0.0.1 class-skilled.test` in the Windows hosts file using administrator rights,
   then restart/reload Apache through Laragon. Alternatively, run `php artisan serve`.

Arabic lives at `/`, English at `/en`, and the dashboard placeholder at `/cs-panel`.
`ADMIN_PATH` changes the dashboard prefix. Its locale comes from session key `admin_locale`
and defaults to Arabic. Administrator environment values are intentionally empty until
the later authentication/seeding phase.

`composer dev` starts PHP and Vite only. No queue worker or scheduler is required.
Fonts are local Cairo/Tajawal files. Alpine is supplied by Livewire. Swiper is code-split
and downloaded only when a page has a slider marker.

## Quality checks

On a disposable development database only, `php artisan migrate:fresh --seed` verifies
a complete rebuild (it drops existing tables). Then run:

```text
composer lint
composer analyse
php artisan test
npm run build
php artisan route:trans:cache
```

The tests use an isolated in-memory SQLite database. For an explicit formatting check,
run `php vendor/bin/pint --test`. Review `storage/logs/laravel.log` after verification.
Use `php artisan route:trans:clear` to clear translated route caches during development.
Use the package's translated cache command, not plain `route:cache` or `optimize`.

## Scope and hosting

Fortify configuration and default migrations are published, but its routes are disabled
until custom authentication views/actions are implemented in phase 02. Public sitemap
and tracking endpoints are placeholders, not feature implementations. The only seeded
data are four bilingual placeholder UI labels in `language_lines`.

Media uses GD, synchronous conversions and no external optimization binaries. Video,
PDF and SVG thumbnail generators are disabled. Sitemap JavaScript crawling is disabled.
Cache/session drivers are files and the queue driver is sync. Debugbar is available only
locally and opt-in with `DEBUGBAR_ENABLED=true`.

Serve **only** `public/`. Keep `_design/` tracked for project references but exclude it
from deployment. Build Vite assets locally and deploy `public/build`; Node, Redis,
queue workers, cron and FFmpeg are not required on the hosting server.
