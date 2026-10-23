# Phase 03 — Media library, Settings, Appearance, UI strings, SEO & Redirects (dashboard)

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/dashboard/08-media-library.jpg`,
`09-appearance-fonts.jpg`.
Goal: every global/configuration screen of the dashboard, built with the phase-02 kit.

## 1. Media library (`08-media-library.jpg`)
- Three-column layout: folders tree (create/rename/move/delete empty, counts) · grid/list of assets · details panel.
- Toolbar: search (name, alt, title), filter by kind (image/video/document/embed), "missing alt (ar/en)", date,
  sort (newest, name, size); grid/list toggle; select multiple → bulk move to folder / bulk delete (blocked if used,
  showing where used).
- Upload: drag & drop anywhere on the page or "رفع ملفات" button; multiple files; per-file progress; errors per file
  (type, size from MediaSettings); images processed by `AssetUploader` synchronously (show "processing" state until done).
- Add embed: YouTube/Vimeo URL + required poster (pick/upload image) + title.
- Details panel: preview (image, video player, pdf icon + open), original vs optimized size, dimensions, generated
  sizes list, "used in" list with links to the editing screens (via `assetables`), alt/title/caption with AR/EN tabs,
  focal point (top/center/bottom) with live crop preview, replace file (keeps id and usages, regenerates conversions),
  copy URL, download original, delete (SweetAlert, blocked if in use unless forced by admin).
- Video assets: poster thumbnail (frame capture tool from phase 02), duration, size warning if > MediaSettings limit.
- Infinite scroll or "load more" with Livewire; no reloads.

## 2. Settings screens (one Livewire page per group, sections & locale tabs, save toast, activity logged)
- General: site name/tagline AR/EN, logos (light/dark), favicon (generate 32/180/192/512 PNG variants with GD on save),
  default locale.
- Company & licences: legal name AR/EN, trade licence no + expiry, classification + authority + expiry, ADCCI no,
  founded year (used in trust bar, footer, schema).
- Contact: phones (repeater, LTR), WhatsApp number + default message AR/EN (preview the wa.me link), emails,
  address AR/EN, map (lat/lng + embed URL + preview), working hours repeater (days + hours AR/EN).
- Social links.
- Notifications & email: recipients per request type, sender name, auto-reply on/off + subject/body AR/EN with
  placeholders `{name}`, `{number}`, "send test email" button.
- Forms: emirates list, facility types, project states, start options (repeaters AR/EN, sortable), max upload MB,
  allowed extensions, honeypot on/off, Cloudflare Turnstile keys (optional; if empty only honeypot is used).
- Media: max image MB, max video MB, max image dimension, WebP quality, "regenerate all conversions" action
  (processes in chunks of 20 via Livewire polling so it never times out on shared hosting).
- Tracking: GA4 ID, GTM ID, Meta Pixel ID, custom head/body scripts (admin only, warning text).
- Maintenance: on/off, message AR/EN, allowed IPs (current IP prefilled). Implement a middleware that shows a branded
  maintenance page to guests while admins (logged in) and allowed IPs see the site. (Do not use `artisan down`.)

## 3. Appearance & fonts (`09-appearance-fonts.jpg`)
- Heading font (Cairo / Tajawal) and body font (Cairo / Tajawal) as radio cards with live samples in Arabic and English.
- Base font size (15/15.5/16), Arabic line-height (1.7/1.8/1.85/1.9).
- Brand colours (navy, deep, sky, ink, muted, line, paper) with hex inputs + swatches, "restore defaults", contrast
  warning if sky-vs-deep or ink-vs-white falls below WCAG AA.
- Live preview panel (hero snippet + card + button) updating without reload.
- Output: the site layout prints a small `<style>` with CSS variables from these settings (cached); only the chosen
  font families' @font-face are included and the first used weight is preloaded.

## 4. UI strings (نصوص الواجهة)
- Table of `language_lines` grouped by `group` (site, forms, buttons, messages, seo...), search across key/ar/en,
  filter "missing English", inline edit AR/EN (textarea with variable placeholders highlighted, e.g. `:count`),
  "reset to default" (defaults kept in a seeder source file), import/export JSON.
- Cache cleared on save (translation-loader cache + response cache).

## 5. SEO
- Global SEO settings: title suffix AR/EN, default description AR/EN, default OG image, verification codes,
  robots.txt editor (served dynamically at `/robots.txt`, with sitemap line), organisation schema extras.
- Redirects: table (from, to, code, hits, last hit, auto/manual), create/edit (validate no loops, from path normalized,
  supports Arabic paths percent-decoded), bulk delete, export, import CSV. Middleware applies redirects before routing
  (cached lookup map).
- "SEO health" list: pages/services/projects/posts missing SEO title/description/OG or English translation,
  with links to fix.

## 6. Backup (best effort on shared hosting)
- "Create backup now": export DB via a pure-PHP chunked dumper (no `mysqldump` dependency) to a .sql file + zip of
  `storage/app/public` media (optional checkbox) into `storage/app/backups`; list backups, download (signed), delete,
  keep last N (setting). Show a note that the host's own backups are still recommended.

## Tests
Asset upload & processing (fake disk), delete blocked when used, replace keeps usages, settings save & cache flush,
appearance CSS variables rendered in layout, redirects middleware (incl. Arabic paths, loop prevention), robots.txt,
maintenance mode bypass for admin/allowed IP, backup creates downloadable file.

## Acceptance checklist
- [ ] Media library fully usable without reloads; images get WebP sizes; videos keep poster + duration
- [ ] All settings groups editable AR/EN and reflected via cache flush
- [ ] Switching fonts/colours updates CSS variables (verify in the placeholder site layout)
- [ ] UI strings editable; redirects working; SEO health list
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-03: media, settings, appearance, seo`
