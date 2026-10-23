# Phase 08 — Public forms + Requests management in the dashboard

Read first: `CLAUDE.md`, `_design/PROGRESS.md`. Visual reference: `_design/screens/website/09-quote-step-1.jpg`,
`10-quote-step-3-upload.jpg`, `11-contact.jpg`, `12-careers.jpg`; dashboard `03-quotes-list.jpg`, `04-quote-detail.jpg`.
Goal: quote request, contact and job application forms on the site (Livewire, no reloads) and their complete
management in the dashboard. No relation to any external system.

## 1. Quote request (`quote`) — Livewire `QuoteForm`, 4 steps
1. **Project**: service chips (from active services; `?service=` preselect), facility type, project state (from
   FormSettings), short description (optional).
2. **Location & size**: emirate, area/district, area m² (number) or "not sure", expected start.
3. **Drawings & photos**: drag & drop multi-upload (Livewire `WithFileUploads`) with per-file progress, type/size checks
   from FormSettings (pdf, dwg, jpg, png, webp; max MB), remove file, Notyf toast after uploads finish
   (as in screen 10). Files stored on the **private** disk.
4. **Contact**: name, phone (UAE format helper, LTR), email (optional), preferred contact (call/WhatsApp), consent text.
- Stepper on top (done/current states), "Back/Next" buttons, per-step validation, state kept when moving between steps,
  summary side box from step 2 onward, direct contact box (WhatsApp/phone).
- Spam: honeypot + optional Turnstile; rate limit 5/hour per IP; minimum fill time.
- Submit → `SubmitQuoteRequest` action: create record (number QR-0001…), default status, source (page, referrer, UTM
  from first-touch cookie, device), attach files, log `system` activity, `site_events` quote_submit,
  **mail to recipients + auto-reply in the visitor's locale via `afterResponse()`**, success screen with request number,
  what happens next, WhatsApp button; toast.
- Mobile: one column, sticky step buttons above the bottom bar, keyboard-friendly inputs (`inputmode`).

## 2. Contact (`contact`) — `11-contact.jpg`
Channels row (call, WhatsApp, email, office with map link), Livewire `ContactForm` (name, phone, email, subject
select, message) with inline validation states, map embed (lazy, click-to-load iframe), working hours table.
Same spam/rate-limit/mail/event flow as quotes.

## 3. Job application
`JobApplicationForm` on job detail pages and the general application box: name, phone, email, specialty, message,
CV (PDF ≤ MB setting, private disk). Same flow; closed jobs refuse applications.

## 4. Dashboard: Requests module (three lists sharing one implementation)
- **Quote requests list** (`03-quotes-list.jpg`): status quick-tabs with counts, search (name, phone, email, number),
  filters (service, emirate, status, assignee, source, date range, has attachments), sorting, bulk (change status,
  assign, export selected, delete), Excel export of filtered results, unread rows bold, new-count badge in sidebar.
- **Detail** (`04-quote-detail.jpg`): header with status badge, quick actions (WhatsApp with prefilled message,
  call, email), request details, attachments (preview images/PDF in modal, download via signed route), timeline of
  `request_activities` (notes, status changes, assignment, system events) with "add internal note", side panels:
  client, follow-up (status select, assignee select, follow-up date), source (page, UTM, device). Marks as read on open.
- Statuses management screen (per request type): name AR/EN, colour, order, default, final.
- Contact messages and Job applications: same list/detail pattern (applications show CV preview + job link).
- Permissions: `sales` sees and manages requests; only admin deletes. All actions logged.
- Dashboard notifications dropdown lists latest unread requests (from phase 02 shell).

## 5. Emails
Markdown mailables, branded (logo, navy header, RTL for Arabic), localized: admin notification (all fields + link to
dashboard), visitor auto-reply (from NotificationSettings templates). Test with `MAIL_MAILER=log`.

## Tests
Each form: validation per step, uploads (fake private disk), honeypot/rate limit, success creates record + activity +
event, mails queued afterResponse (assert sent), auto-reply locale; dashboard list filters/export, detail actions
(note, status, assign), signed attachment download authorization, permissions.

## Acceptance checklist
- [ ] Forms work smoothly on mobile and desktop, AR/EN, without reloads; uploads with progress + toasts
- [ ] Requests fully manageable in the dashboard with statuses, notes, assignment, export
- [ ] Quality gates pass; PROGRESS.md updated; commit `phase-08: forms & requests`
