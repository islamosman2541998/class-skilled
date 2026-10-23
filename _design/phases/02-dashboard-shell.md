# Phase 02 — Dashboard shell, auth & shared admin components

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/dashboard/01-login.jpg`,
`02-dashboard.jpg`, `03-quotes-list.jpg`, `10-projects-delete-confirm.jpg`.
Goal: the dashboard frame (auth, layout, navigation, permissions) and a **reusable component kit** that every later
module uses: data table, form controls, translatable tabs, asset picker/uploader, sortable lists, rich text editor,
toasts and confirmations. Build Users & Roles and Activity Log as the first real screens to prove the kit.

## Out of scope
Content modules (phase 04), media library screen (phase 03), menus (05), website (06+).

## 1. Authentication (Fortify, custom views)
- Login screen as in `01-login.jpg`: split layout, form at the start side, large real photo on the other side with a
  small navy glass caption; email, password, remember me, "forgot password". Arabic/English toggle.
- Password reset (email), two-factor (TOTP) enable/confirm/recovery codes in "My account"; 2FA challenge screen.
- Throttle login (5/min per email+IP). Inactive users cannot log in. Update `last_login_at`.
- All dashboard routes under `config('admin.path')`, middleware `auth`, `verified-is-active`, `SetAdminLocale`.

## 2. Layout `layouts/admin.blade.php` (match `02-dashboard.jpg`)
- Start-side sidebar (deep navy, 268px): brand mark + "لوحة تحكم الموقع", groups (collapsible, state remembered in
  localStorage via Alpine, wrapped in try/catch), items with permission checks, count badges (new quote requests,
  unread messages, new applications — computed via a cached view composer, 60s).
  Groups & items (create placeholder routes for items built in later phases; hide items whose module is not built yet
  behind a config flag so the menu never shows dead links):
  - الرئيسية
  - الطلبات: عروض الأسعار، رسائل التواصل، طلبات التوظيف
  - المحتوى: الصفحة الرئيسية، الصفحات، الخدمات، المشاريع، تصنيفات المشاريع، المقالات، تصنيفات المقالات،
    الأسئلة الشائعة، آراء العملاء، العملاء والشركاء، فريق العمل، التراخيص والشهادات، الوظائف
  - التنقل والمظهر: القوائم، المظهر والخطوط
  - الوسائط: مكتبة الوسائط
  - SEO: إعدادات SEO، التحويلات
  - الإعدادات: الإعدادات العامة، نصوص الواجهة
  - النظام: المستخدمون والأدوار، سجل النشاط، النسخ الاحتياطي
- Topbar: global search (Ctrl/Cmd+K opens a Livewire command palette searching requests, projects, services, pages,
  posts by title/number/phone — permission-aware), "عرض الموقع" (new tab), language toggle (ar/en, saved on user),
  notifications dropdown (latest unread requests), user menu (my account, 2FA, logout).
- `wire:navigate` on all internal dashboard links; progress bar on navigation; page `<title>` per screen.
- Responsive: below 1024px the sidebar becomes an off-canvas drawer.

## 3. Component kit (`components/admin/*` + `Livewire/Admin/Concerns/*`)
Build each with a short usage doc block at the top of the file.
- **Data table** (`WithDataTable` trait + `<x-admin.table>` parts): search (debounced 300ms, `#[Url]` synced),
  filter chips & a filters popover, status quick-tabs with counts, sortable columns, per-page (10/25/50), pagination,
  row selection + "select all matching", bulk actions bar, column visibility toggle (saved per user in localStorage),
  empty state with a clear call to action, loading skeleton rows (`wire:loading`).
  Excel export action: `spatie/simple-excel` streamed XLSX of the **current filtered query** with translated headers,
  respects permissions, logs activity.
- **Form controls**: text, textarea (with character counter), number, email, tel (LTR), url, select (Tom Select,
  searchable, RTL), multi-select with chips & ordering, toggle, segmented control, radio cards, date/datetime
  (flatpickr, Arabic locale), color input (hex + swatch), slug input with prefix showing the localized URL,
  link picker (the link object from phase 01 §7: choose type → page/model/route/external), repeater (add, remove,
  reorder, collapse), sortable list (SortableJS → `reorder(array $ids)` Livewire method).
  Every control: label, required marker, hint, inline validation error, disabled/readonly states, `aria-*`.
- **Translatable fields**: `WithTranslatableForm` trait + `<x-admin.locale-tabs>` showing العربية / English tabs per
  section with a completeness dot (green complete, amber missing English). Arabic required; English optional but
  listed in a "missing translations" filter on every list.
- **Rich text editor**: TipTap wrapped in an Alpine component synced with `wire:model` (debounced), toolbar:
  H2, H3, bold, italic, bullet/ordered list, link, quote, undo/redo; RTL/LTR aware per locale tab; output purified server-side.
- **Asset picker & uploader** (UI only here, uses `AssetUploader` from phase 01): modal to pick from library
  (folders, search, filter by kind) or upload new (drag & drop, multiple, progress bars, client-side validation,
  video poster capture: play the selected video in the modal, pick a frame → canvas → upload as image asset;
  read video duration from metadata). Single and multiple modes, ordering for multiple. Shows thumbnail + alt status.
- **Toasts**: Notyf in admin layout; `WithToasts` trait → `$this->toast('success', __('admin.saved'))`.
- **Confirmations**: SweetAlert2 via Alpine `$confirm({...})` helper (RTL, brand colors, danger variant) as in
  `10-projects-delete-confirm.jpg`. Also "unsaved changes" guard on forms (`beforeunload` + `wire:navigate` hook).
- **Page header**: breadcrumbs, title, subtitle, actions slot. **Panel**, **stat card**, **badge** (status colors),
  **empty state**, **skeleton**.

## 4. First screens using the kit
- **Users & Roles**: list (search, role filter, active filter, export), create/edit (name, email, role, active,
  dashboard language, reset password link, force 2FA flag), roles with permission matrix (admin only).
  Users cannot delete themselves; last admin cannot be demoted.
- **Activity Log**: table (user, action, subject type + title, changes diff modal, date), filters by user/module/date.
- **My account**: name, email, password change, language, 2FA management.
- **Dashboard home placeholder**: page header + empty stat cards (real widgets come in phase 09).

## 5. Policies & permissions
Policy per model (generated now for all models from phase 01). Gate `before` for `admin`. `editor`: content, media,
menus, SEO; `sales`: requests only (+ export). Sidebar and actions hidden when not permitted; routes enforce it too.

## 6. Tests
Login, throttle, 2FA challenge, inactive user blocked, permission enforcement per role on representative routes,
data table search/filter/sort/export (export returns xlsx with expected rows), users rules (self-delete, last admin).

## Acceptance checklist
- [ ] Login/2FA work; admin from `.env` can sign in; dashboard matches the reference look (navy sidebar, panels)
- [ ] Users, Roles, Activity Log fully working with search, filters, sorting, pagination, bulk, export — no reloads
- [ ] Toasts and SweetAlert confirmations work in RTL and LTR
- [ ] Component kit documented; Pint/Larastan/Pest/build pass
- [ ] PROGRESS.md updated; commit `phase-02: dashboard shell & component kit`
