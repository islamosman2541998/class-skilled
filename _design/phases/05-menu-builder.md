# Phase 05 — Menu builder (dashboard) + MenuResolver (backend for the site)

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/dashboard/05-menu-builder.jpg`,
`_design/screens/website/03-menu-services-dropdown.jpg`.
Goal: the user controls the navbar, top bar, footer columns, legal links and mobile menu from the dashboard; can link
any page; can create dropdowns; and a "Services" (or Projects, categories, …) item can render as a dropdown whose
children are chosen manually or filled automatically.

## 1. Screen (`05-menu-builder.jpg`)
- Location tabs: Header, Top bar, Footer column 1, Footer column 2, Footer column 3, Footer legal, Mobile
  (Mobile has "use header menu" toggle — default on).
- Left/main panel: tree of items (max depth 2), drag & drop to reorder and nest (SortableJS nested), each row shows
  label, type badge, short info (e.g. "manual, 8 services", "auto: project categories"), hidden/inactive state,
  edit, delete (confirm).
- "Add item" quick panel: tabs Pages (checkbox list of published custom pages) · System pages (home, services index,
  projects index, articles, careers, quote, contact, FAQ, search) · Specific item (search services/projects/posts/
  categories) · Dynamic group · External link · Heading (no link, dropdown parent only).
- Edit item side panel: label AR/EN (empty = inherit the target's title, shown as placeholder), type, target,
  open in new tab, visibility (all/desktop/mobile), active.
  For **dynamic group**: source (services / projects / project categories / posts / post categories / pages),
  mode (manual pick / all active / featured / latest), limit, manual picks list (search + add + drag order +
  remove), the item's own link (e.g. Services index), "show 'view all' link" toggle.
  Optional per-child short description shown in the desktop dropdown (uses the target's `summary`).
- Save menu → toast, cache flush. Preview button opens the site.

## 2. MenuResolver (`app/Services/MenuResolver.php`)
- `forLocation(MenuLocation $loc, string $locale): Collection<MenuNode>` returns a pure view-model tree:
  `label`, `url`, `active` (matches current route incl. children), `target`, `visibility`, `children[]`,
  `description?`, `isDynamic`.
- Labels inherit from linked model in the requested locale (fallback ar). Items pointing to missing/unpublished
  targets are skipped. Dynamic groups expand to children according to mode/limit, eager loaded (≤ 3 queries per menu).
- Cached per location + locale (`SiteCache`), flushed on menu/item/linked model changes.
- Blade view composer exposes `$menus['header']` etc. to the site layout.

## 3. Defaults (update `EssentialSeeder`)
Header: الرئيسية · من نحن (page) · الخدمات (dynamic: services manual 8, view-all on) · المشاريع (dynamic: project
categories, auto) · الاستوديوهات (model: service "film studio fit-out") · المقالات · تواصل معنا.
Top bar: phone/email come from Contact settings (not a menu); the top-bar menu holds optional small links.
Footer 1 "الشركة": من نحن، المشاريع، الاستوديوهات، المقالات، الوظائف، تواصل معنا. Footer 2 "الخدمات": dynamic services
(all, limit 6). Footer 3: optional. Legal: سياسة الخصوصية، الشروط والأحكام (custom pages — create them in the seeder with
placeholder copy marked for review).

## Tests
Resolver: inheritance of labels per locale, dynamic modes (manual order respected, featured, latest, limit), hidden
targets skipped, active state, caching + flush; nested reorder persistence; permission (editor allowed, sales denied).

## Acceptance checklist
- [ ] All locations editable; drag/nest works; dynamic groups manual/auto both work
- [ ] Resolver returns correct trees in AR and EN with ≤ 3 queries per menu (assert in test)
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-05: menu builder`
