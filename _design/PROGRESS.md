# Progress log

Update this file at the end of every phase. Keep entries short and factual.

| Phase | Status | Date | Commit |
|---|---|---|---|
| 00 Setup & tooling | done | 2026-10-23 | phase-00 |
| 01 Data model & infrastructure | not started | | |
| 02 Dashboard shell & component kit | not started | | |
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

## Changes to earlier phases
-

## Known gaps / follow-ups
-

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
