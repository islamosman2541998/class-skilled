# QA checklist — Class Skilled website & dashboard

Legend: **✔ auto** = covered by the Pest suite · **✔ seen** = checked with headless Chrome screenshots / HTTP checks ·
**☐ manual** = needs a person on a real device or browser before launch (cannot be automated here).
Last run: 2026-10-24, local Laragon (PHP 8.3, MySQL), 105 tests green.

## 1. Pages, widths and languages
| Item | AR | EN | Status |
|---|---|---|---|
| Home (hero slider, sections, footer) at 390 / 1440 | ✔ | ✔ | ✔ seen (screenshots phase 06) |
| Services index + detail | ✔ | ✔ | ✔ seen 1440 / 390, ✔ auto |
| Projects index (filters, sort, load more) + detail (gallery, facts, related) | ✔ | ✔ | ✔ seen 1440, ✔ auto |
| Articles index (featured, search, pagination) + detail (share, related) | ✔ | ✔ | ✔ seen 390, ✔ auto |
| Careers + job detail + general application, closed job 410 | ✔ | ✔ | ✔ auto |
| Custom page (main media → blocks → files → videos → images), legal pages | ✔ | ✔ | ✔ auto |
| FAQ (filter), search (groups, highlight, min 2 chars) | ✔ | ✔ | ✔ auto |
| Quote (4 steps) and contact | ✔ | ✔ | ✔ seen (quote 1440 AR, contact 390 EN), ✔ auto |
| 404 / 410 / 419 / 429 / 403 styled, 500 self-contained | ✔ | ✔ | ✔ auto (404), ☐ manual (500 with DB down) |
| All public URLs return 200 in both locales (26 URLs) | ✔ | ✔ | ✔ seen (`storage/app/check-urls.php`) |
| 360 / 768 / 1024 widths, no horizontal scroll | | | ☐ manual |

## 2. Navigation & accessibility
- Desktop dropdowns: hover, click, Esc, arrow keys — ☐ manual (keyboard pass in a real browser).
- Mobile off-canvas: focus trap, Esc, backdrop, accordion, scroll lock — ☐ manual.
- Skip link, landmarks (`header`, `nav`, `main`, `footer`), single `h1` per page — ✔ seen (markup review).
- Lighthouse accessibility = **100** on Home, Service, Project, Article, Quote — ✔ seen.
- Screen reader pass (NVDA / VoiceOver) — ☐ manual.
- `prefers-reduced-motion`: no Ken Burns / autoplay / reveal transitions — ✔ seen (forced reduced motion screenshots).

## 3. Forms
- Quote: per-step validation, `?service=` preselect, uploads by content type, success number, mails — ✔ auto.
- Contact and job application (PDF only, closed job refused) — ✔ auto.
- Honeypot, minimum fill time, 5/hour rate limit — ✔ auto. Turnstile (only when keys set) — ☐ manual with real keys.
- Upload progress bar and toast on a slow connection — ☐ manual.
- Real SMTP delivery + RTL rendering in Gmail / Outlook — ☐ manual on the host.

## 4. Dashboard
- Requests lists: status tabs, filters, export, bulk status / assign / read / delete — ✔ auto (sales + admin).
- Request detail: note, status, assignee, follow-up date, signed attachment download / preview — ✔ auto.
- Statuses screen (single default) — ✔ auto. Content modules CRUD, media, menus, settings — ✔ auto (phases 02–05).
- Permissions per role (admin / editor / sales) — ✔ auto.
- Dashboard home widgets with real numbers — ✔ auto. Visual check of the dashboard home — ☐ manual.
- System → Health and `php artisan site:check` — ✔ auto.

## 5. Cache, SEO, analytics
- Guest full-page cache served on repeat visits, bypassed for users / query strings / form pages — ✔ auto + ✔ seen (cache header).
- Cache flushed by dashboard edits (observers) — ✔ auto.
- Translated slug change creates a redirect — ✔ auto (phase 04).
- Canonical, hreflang + x-default, OG/Twitter, JSON-LD per page type, sitemap + robots — ✔ auto.
- Beacon `/t`: page views, clicks, bots ignored, throttled — ✔ auto. GA4 / Meta firing — ☐ manual in production.

## 6. Security
- Security headers (CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy; HSTS on HTTPS production) — ✔ auto.
- Rate limits: login (Fortify), forms, beacon, search — ✔ auto (forms, beacon), configured (login, search).
- Visitor uploads on the private disk, random names, MIME sniffing, signed short links — ✔ auto.
- Public storage: `.htaccess` blocks scripts; fallback route refuses scripts and `..` — ✔ auto.
- Rich text purified on save; no `{!! !!}` on unsanitised data (reviewed) — ✔ seen.
- `APP_DEBUG=false` in production; 500 page leaks nothing — ☐ manual on the host.

## 7. Performance (Lighthouse mobile, local Laragon)
| Page | Perf | A11y | Best pr. | SEO |
|---|---|---|---|---|
| Home | 56–61 | 100 | 79 | 69 |
| Service | 65 | 100 | 79 | 69 |
| Project | 64 | 100 | 79 | 69 |
| Article | 64 | 100 | 79 | 69 |
| Quote | 69–73 | 100 | 79 | 69 |

The local run cannot reach the targets for reasons of the environment, not the code: no HTTPS (best practices),
`robots.txt` disallows everything outside production (SEO "blocked from indexing"), Laragon's Apache has no
`mod_deflate` (≈ 450 KB of JS/CSS travel uncompressed) and no HTTP/2, and Windows/PHP without OPcache tuning answers in
0.4–0.8 s even from the page cache. CLS ≤ 0.04 and TBT ≤ 140 ms everywhere. **☐ Re-run on the production host** (HTTPS,
gzip, OPcache) and record the numbers in PROGRESS.md.

Warm query counts (response cache off, app caches warm): 2–12 queries per page, no duplicates except one on project pages.
