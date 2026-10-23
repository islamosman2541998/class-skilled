# Phase 06 — Website design system, global layout & the Home page

Read first: `CLAUDE.md` (especially §6 Design system), `_design/PROGRESS.md`.
Visual reference: `_design/screens/website/01-home-ar.jpg`, `02-home-en.jpg`, `03-menu-services-dropdown.jpg`,
`14-mobile.jpg`. **Remember: §6 overrides the screens** — smaller type, hero slider, glass card sliders, scroll motion.
Goal: a polished, fast, bilingual site frame (head, top bar, header + dropdowns, mobile menu, bottom bar, footer,
toasts, confirm) and a **complete, animated Home page** fully driven by Home Sections, Menus and Settings.
Other public routes keep rendering the placeholder inside the new layout (built in phases 07–08).

## 1. Design tokens & base CSS (`resources/css/site.css`)
- Tailwind theme tokens referencing CSS variables printed from AppearanceSettings (colours, fonts, base size, line-height).
- Type scale from §6.2 as utility classes/components: `.t-hero`, `.t-page`, `.t-section`, `.t-card`, `.t-lead`, `.t-small`
  using `clamp()` between mobile and desktop values. **Never exceed these sizes.**
- Buttons: `.btn-primary` (sky bg, deep text), `.btn-secondary` (navy outline), `.btn-ghost-light` (on dark), sizes;
  focus-visible ring sky 3px; hover states subtle (darken 6%, no movement except glass cards).
- Glass utilities from §6.5: `.glass-dark`, `.glass-light`, `.glass-btn` (round 44px slider arrow), with
  `@supports not (backdrop-filter: blur(1px))` fallbacks.
- Section wrappers: `.section`, `.section--paper`, `.section--dark`, `.section--dark-photo` (bg image via inline CSS var,
  overlay rgba(27,42,71,.78)).
- Prose styles for rich text (RTL/LTR lists, links underlined with sky, headings small per scale).

## 2. Global layout (`layouts/site.blade.php` + `site/partials/*`)
- `<html lang dir>`; `<head>` partial: meta charset/viewport, SEO partial placeholder (title/description from
  `SeoManager`, finished in phase 09), favicon set, preload chosen fonts, preload hero image when on home,
  CSS variables `<style>`, `@vite`, tracking scripts slot (phase 09).
- **Top bar** (desktop only, deep navy, 13.5px): phones (LTR, tel: links), email, working hours (first row), language
  switch (links to the same page in the other locale via `LaravelLocalization::getLocalizedURL` with translated slugs).
- **Header** (sticky): logo (mark + name text from settings), menu from `MenuResolver('header')`, CTA "اطلب عرض سعر"
  (label from UI strings, link to quote route). Desktop dropdowns: hover + click + keyboard (Esc closes, arrow keys),
  panel with two columns of children (title + short description), "view all" link at the bottom, small chevron only
  as dropdown affordance. Transparent over the hero, then glass/solid + shrink after 24px scroll (`header.js`).
- **Mobile menu** (`mobile-nav.js`, Alpine): burger → off-canvas panel from the start side, focus trap, body scroll lock,
  accordion children, CTA + WhatsApp buttons, language switch, close button; menu from `MenuResolver('mobile')`.
- **Bottom action bar** (mobile only): glass bar with Call / WhatsApp / Request a quote; hides when the mobile menu is open
  and when the on-screen keyboard is visible on form pages.
- **Floating WhatsApp** (desktop only): small pill "واتساب" at the end-bottom corner using settings number + message.
- **CTA band** (before footer on all pages except quote/contact; content from the home `cta` section or settings).
- **Footer** (deep navy): brand + short about (settings tagline), footer menus 1/2/3, contact block (settings), social
  links (text labels or simple brand glyphs), registration line (licence, classification, ADCCI from CompanySettings),
  legal menu, copyright with current year.
- **Toasts** (Notyf) + **confirm** (SweetAlert2) wired globally (§6.8).
- Breadcrumbs component (used from phase 07). 404/500 pages styled with search box and links (texts from UI strings).

## 3. Home page (`site/pages/home.blade.php` → loops `HomeSectionResolver` → `site/sections/{type}.blade.php`)
Each section is its own Blade file, renders nothing when it has no data, and supports its configured background.
- **hero** (§6.5): Swiper fade + Ken Burns, autoplay with progress bars, glass round arrows, swipe/drag, keyboard,
  glass text panel (title `.t-hero`, text `.t-lead`, two CTAs), optional caption chip, optional mobile image / looping
  muted video (`playsinline`, poster), first slide eager & preloaded, others lazy. Height per §6.5. Pause autoplay when
  the tab is hidden. Reduced motion → no Ken Burns, no autoplay.
- **facts**: row of 4 facts (label small muted, value 17px bold) separated by thin lines; 2×2 on mobile.
- **about**: text column (section title, lead, paragraph, meta items, text link) + two-image composition with
  `data-reveal="image"`.
- **services** (default background dark_photo): section header (title, subtitle, "all services" link, glass arrows) +
  **glass card slider**: card = image (4:3, rounded 14px), title, summary (2 lines clamp), "التفاصيل" text link;
  cards without image show a navy glass card with the title only (no placeholder image).
- **feature_band** (studios): split layout (text + bullets + CTA / 3-image mosaic), dark background.
- **projects** (default light/paper): glass-light card slider: image, title, "category، location", hover lift.
  Optional category filter chips above (filter slides client-side, no reload).
- **why_us**: 2×2 grid of statements (title + text) with a top rule, no icons; stagger reveal.
- **process**: 4 numbered steps (sky numbers), the connecting line draws on reveal; vertical timeline on mobile.
- **stats**: count-up numbers when visible.
- **testimonials / clients / certificates**: glass sliders (clients = logo strip slider, grayscale → colour on hover).
- **posts**: card slider (image 16:10, date, title).
- **cta**: band with title, text, primary + WhatsApp buttons.

## 4. JavaScript (`resources/js/site/*`)
- `app.js`: imports toast/confirm/header/mobile-nav/reveal; dynamically imports `sliders.js` only if
  `[data-slider]` exists.
- `sliders.js`: Alpine data components `heroSlider(options)` and `cardSlider(options)`; read `dir` from `<html>`;
  modules Navigation, Pagination, Autoplay, EffectFade, A11y, Keyboard; re-init safe; `aria-label`s from UI strings;
  autoplay progress via `autoplayTimeLeft` event updating a CSS variable on the active bullet.
- `reveal.js`: IntersectionObserver per §6.6 (`fade-up`, `fade`, `image`, `stagger`, `count`, `draw-line`), runs once,
  reduced-motion aware, shorter durations under 640px.
- `header.js`: scroll state with `requestAnimationFrame` throttle.
- No jQuery. Total JS on home ≤ 90 KB gzip (Swiper modules tree-shaken).

## 5. Performance & quality for this phase
- Lighthouse (mobile) on Home ≥ 90 performance, ≥ 95 accessibility/best practices/SEO (document numbers in PROGRESS.md).
- No CLS from fonts/images/sliders (fixed aspect ratios, reserved heights).
- Test at 360, 390, 768, 1024, 1440 widths in AR and EN; no horizontal scroll; tap targets ≥ 44px.
- Every visible string comes from DB (UI strings / sections / settings) — grep the Blade files for Arabic/English
  literals and fix any.

## Tests
Home renders in both locales with seeded data; hidden sections not rendered; menu dropdown children rendered from
resolver; language switch URLs correct for translated slugs; Swiper markup present only when needed.

## Acceptance checklist
- [ ] Layout parts and Home match §6 (small type, glass sliders, hero slider with arrows/swipe/autoplay, scroll motion)
- [ ] Desktop dropdown + mobile off-canvas menu fully driven by the menu builder
- [ ] Changing a section/setting/menu in the dashboard updates the site immediately (cache flush works)
- [ ] Lighthouse numbers recorded; quality gates pass; commit `phase-06: site layout & home`
