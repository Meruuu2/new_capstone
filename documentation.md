# UI Redesign Documentation — UA Facility Management System

**Last updated:** October 26, 2026
**Scope:** UI and workflow reference for the public site and staff portals, including backend features needed for request submission, ticket lookup, team accounts, and user management. Implementation details are linked in [§15 — Backend changes](#backend-changes-approved-exceptions).
**Status:** UI and workflow reference, updated through October 26, 2026. Dated change notes below are historical; use the current routes and implementation as the source of truth. For code-level details, see the controller, routes/views, and model documentation.

---

## What's new (October 16–26, 2026)

| Area | Change | Details |
|---|---|---|
| **Guests** | The guest portal is retired. Guests now request a facility **without an account** using a public form, then follow up with a ticket code. Guest accounts can no longer sign in. | [§10](#10-guest-access-no-account) |
| **Public ticket tracker (Oct 25)** | The homepage tracker checks facility requests (`FM-…`), bookings (`BKG-…`) and maintenance tickets (`RM-…`) without sign-in. Every type requires the submitter's tracking last name; results are read-only, with existing guest receipt/PDF actions retained where allowed. The form uses a two-row desktop layout and stacks on narrow screens. | [§6.1](#61-landing-page-welcomebladephp) |
| **Repair tickets** | College staff can type an **Others** place when the facility isn't listed. Each college repair ticket has a **live conversation** with the GSU admins, and tickets are **shared within the college** (everyone sees the team's tickets, updates and replies). | [§8](#repair-ticket-conversation-admin--college-staff) |
| **"Noted by"** | College: the choices come from the college's **Users** team (Deans, Program Heads). Org: **required**, and must be a college **Dean** or **Program Head** (type their name), or **Someone else** (type position and name). | [§8](#new-request--purpose-signatory-and-equipment-steps-03-and-04), [§9](#9-organization-unit-portal) |
| **All Requests order** | Current pending requests are listed first, followed by legacy payment-pending and approved/reserved records, then disapproved and cancelled requests. Guest payment progress is stored separately from request status. | [§7](#7-admin-portal) |
| **Request tickets** | Every facility request has a ticket code `FM-<year>-<request no.>`. A ticket pop-up opens right after submitting (College, Org and guests) and from each request's detail page. | [§8](#request-ticket-and-detail-pages) |
| **Team accounts** | College and Org staff get a **Users** page. The Dean (College) or Governor (Org) adds, edits and deactivates accounts for their own unit; deactivation preserves history. | [§8](#team-accounts-users-page) |
| **Users** | New `position`, `contact_number`, and explicit tracking `last_name` fields. The admin Users page shows the **Admin team**; accounts can be edited or deactivated without deleting their history. | [§7](#7-admin-portal) |
| **Account rules** | Names allow letters, spaces, hyphens, and apostrophes. Passwords require 8–94 characters, mixed case, and a special character; Argon2id supports the full limit while legacy bcrypt hashes remain verifiable. | [§7](#7-admin-portal) |
| **Audit trail** | Admin-only search and filters for application changes and login/logout events; sensitive values are redacted. | [controller-documentation.md](controller-documentation.md) |
| **Reservation hours and WebSockets** | Reservations are restricted to 6:00 AM–7:00 PM. Repair conversations between College and GSU use Reverb/Echo real-time broadcasting with polling as fallback. | [fdd.md](fdd.md), [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) |
| **Sign-in** | Each landing page card only lets its own accounts in (for example, a college account can't use the Admin card). | [§6.2](#62-login-authloginbladephp) |
| **Admin booking forms** | Create Direct Reservation and View / Modify now use the same date picker, venue board and equipment tiles as the College/Org request form. The direct reservation page's spacing was polished on Oct 19. | [§7](#7-admin-portal) |
| **Detail pages** | New request detail and repair ticket detail pages for College, Org and Admin, with a progress tracker. | [§7](#7-admin-portal), [§8](#8-college-staff-portal) |
| **Landing page** | Redesigned in black, white and greys with a triangle hero, a "Next on the calendar" card, staff sign-in cards and a Guest Access link. New public pages: Guest Access and Venue rates. | [§6](#6-public-pages) |
| **Styles** | All inline `<style>` blocks moved into `resources/css/app.css`. Run `npm run build` after changing styles. | [§4](#4-shared-building-blocks-new-partials) |
| **Explicit tracking last names (Oct 26)** | New accounts and public guest requests store a separate last name for status lookup. The tracker does not infer a surname from the full name; older rows without an explicit value retain legacy matching. | [§6.1](#61-landing-page-welcomebladephp), [model-documentation.md](model-documentation.md) |

---

## Table of Contents

1. [Summary](#1-summary)
2. [Project rules added to CLAUDE.md](#2-project-rules-added-to-claudemd)
3. [Design system](#3-design-system)
4. [Shared building blocks (new partials)](#4-shared-building-blocks-new-partials)
5. [Navigation — same in every portal](#5-navigation--same-in-every-portal)
6. [Public pages](#6-public-pages)
7. [Admin portal](#7-admin-portal)
8. [College Staff portal](#8-college-staff-portal)
9. [Organization Unit portal](#9-organization-unit-portal)
10. [Guest access (no account)](#10-guest-access-no-account)
11. [Shared pages](#11-shared-pages)
12. [Fixes made along the way](#12-fixes-made-along-the-way)
13. [Historical audit notes](#13-historical-audit-notes)
14. [How to work with the new UI](#14-how-to-work-with-the-new-ui)
15. [Files changed](#15-files-changed)
16. [Verification](#16-verification)

---

## 1. Summary

Every page in the three staff portals (Admin, College Staff, Organization Unit), the public guest pages, and the landing and login pages now share one visual language:

- A **slate monochrome palette** with grayscale campus photos under a dark gradient ("Slate Metallic / Monochrome Depth").
- **Merriweather** for headings and **Inter** for body text.
- One shared portal shell for the three staff portals, with a collapsible rail on desktop and a slide-in drawer on phones. The public guest request flow does not use a guest sign-in portal.
- Each page opens with a **photo banner**, followed by **number tiles**, then the page's content as tables, cards or timelines, with color-coded **status labels**.
- Long forms are split into **numbered steps** with a **live summary card** beside them.
- A **public ticket tracker** on the landing page. It looks up facility requests (FM), bookings (BKG) and maintenance tickets (RM) using the tracking code and the submitter's separately recorded last name, without requiring sign-in; new last names are not inferred from full names. Older rows without an explicit tracking value retain legacy matching; see [§6.1](#61-landing-page-welcomebladephp).
- Guests request facilities **without an account**; see [§10](#10-guest-access-no-account).

Form field names, form actions, links, filters and pagination are unchanged. Where an input type changed (for example a dropdown became clickable cards), it still sends exactly the same name and values.

---

## 2. Project rules added to CLAUDE.md

Two rules were added under **UI Conventions** in `CLAUDE.md`:

1. *When creating or fixing any UI, always also fix the margins, padding, font size, and font styles of the affected elements.*
2. *Navigation must look and behave the same across all user portals (Admin, College Staff, Organization Unit, Guest Access): same colors, sidebar/header treatment, icons, and unique styling. When the navigation changes for one portal, apply the same change to the others.*

Rule 2 is applied to the three current staff portals, which render the shared `layouts/partials/portal-shell.blade.php`. The former guest portal layout is retired.

---

## 3. Design system

### Colors

| Token | Hex | Use |
|---|---|---|
| Slate 900 | `#0f172a` | Primary dark: sidebar header, buttons, table headers, active nav item |
| Slate 700 | `#334155` | Secondary text, bars |
| Slate 500 | `#64748B` | Muted text, labels |
| Slate 400 | `#94A3B8` | Hints, placeholders |
| Slate 200 | `#E2E8F0` | Borders |
| Slate 100 | `#F1F5F9` | Hover and soft fills |
| Slate 50 | `#F8FAFC` | Zebra rows, filter panels |

### Status colors

Every status label shows a dot and text, so meaning never relies on color alone.

| Tone | Text / background | Used for |
|---|---|---|
| Good | `#047857` / `#ECFDF5` | Available, Approved, Completed, Success |
| Warning | `#B45309` / `#FFFBEB` | Pending, Pending payment, Rescheduled, Maintenance |
| Info | `#1D4ED8` / `#EFF6FF` | Reserved, Ongoing |
| Bad | `#B91C1C` / `#FEF2F2` | Disapproved, Cancelled, Failed, Unavailable |
| Neutral | `#475569` / `#F1F5F9` | Notified, other states |

### Typography

- Headings and big numbers: **Merriweather 900**.
- Body, labels, tables: **Inter** 400–700.
- Ticket and booking codes: system monospace.
- **Landing page only:** headings use **Bricolage Grotesque** (falls back to Inter), in a black, white and grey palette scoped to the `.ul` wrapper so other pages are not affected.

### Photos

Banners use the campus photos in `public/img/facilities/` (Busalian Hall, Paghiusa Hall, Tiripunan Hall, Grand Stand, New AVR). They are shown in grayscale under a dark slate gradient so white text stays readable.

### Charts

- Bars use a single slate hue, because the categories are already named on the axis.
- The admin donut uses a color-blind-checked palette (`#2a78d6`, `#eb6834`, `#1baf7a`, `#eda100`, `#e87ba4`), keeps visible labels, and puts its legend beside the chart.

---

## 4. Shared building blocks (new partials)

| Partial | Purpose |
|---|---|
| `layouts/partials/portal-shell.blade.php` | The whole portal frame for every role: styles, sidebar, top bar, notification bell, collapse/drawer script. Parameters: `sections`, `roleLabel`, `portalHeading`, `dashboardRoute`, `contentSection`. |
| `layouts/partials/page-kit.blade.php` | Now an empty placeholder. The `pk-*` styles it used to hold live in `resources/css/app.css` (see below). Pages still `@include` it, which is harmless. |
| `layouts/partials/notification-bell.blade.php` | Bell icon with an unread-count badge (shows "99+" above 99), highlighted on notification pages, with an accessible label such as "Notifications, 3 unread". |
| `layouts/partials/request-ticket.blade.php` | Facility request **ticket pop-up**: ticket code, status, venue, date and time. Opens by itself right after submitting (`autoOpen`), or from any `[data-ticket-open]` button. |
| `layouts/partials/request-detail.blade.php` | Request detail page body for College and Org "My requests": banner, progress tracker, event details, equipment, Cancel (pending only). |
| `layouts/partials/ticket-detail.blade.php` | Repair & maintenance ticket detail for College, in the same layout as the request detail. |
| `layouts/partials/unit-users-index.blade.php`, `unit-users-create.blade.php` | Team list and add/edit form shared by the College and Org **Users** pages. |
| `layouts/partials/contact-number-input.blade.php` | Contact number field for every add/edit user form: digits only, formatted as `0909-340-9211` while typing. |
| `admin/bookings/partials/booking-picker-script.blade.php` | Date picker, venue board and equipment tiles script for the admin booking forms. Display only; it never changes what the form sends. |

### Where the styles live

Every page's inline `<style>` block was moved into **`resources/css/app.css`**, in sections labelled by file (for example `/* ── admin/bookings/create_direct.blade.php ── */`). Fixed `style="…"` attributes became small utility classes (`u-minw-0`, `u-mt-8`, …) that use `!important` so they keep the strength inline styles had. Per-page banner images and data-driven bar widths are still inline on purpose.

- **After changing CSS, run `npm run build`** (or keep `npm run dev` running). Otherwise the browser keeps using the old compiled file in `public/build/`.
- Exception: the printable forms (`forms/facilities`, `forms/repair`) don't load Vite, so their styles stay inline.

### Pop-up flash messages (`layouts/partials/flash-toast.blade.php`)

Flash messages (`session('status')` and `session('error')`) now appear as a **pop-up toast** in the top-right corner (full width at the top on phones) instead of a plain box above the page. Example: "Facilities utilization request submitted to GSU." after a college request is submitted.

- **Success** (`status`): a green check icon. It **closes by itself after 5 seconds**, with a green countdown bar along the bottom; hovering pauses it.
- **Error** (`error`): a red icon. It **stays until closed**, so problems aren't missed.
- Both have a × button, slide in and out, and are announced to screen readers (`role="status"` / `role="alert"`).
- The toast is included once in `layouts/app.blade.php`, so it works for every portal and every existing flash message without changing controllers.

### Main page-kit classes

| Class | What it is |
|---|---|
| `pk-hero` (+ `style="--pk-img: url(...)"`) | Banner with a grayscale photo and slate gradient |
| `pk-eyebrow`, `pk-hero-sub`, `pk-hero-actions` | Banner label, subtitle and buttons |
| `pk-stats` / `pk-stats is-3`, `pk-stat` / `pk-stat is-dark` | Number tiles (4 or 3 per row) |
| `pk-table-wrap`, `pk-table`, `pk-code`, `pk-person`, `pk-avatar`, `pk-row-link` | Tables with dark headers, zebra rows, code text, initials circles and action buttons |
| `pk-pill is-good / is-warn / is-info / is-bad / is-neutral` | Status labels |
| `pk-btn`, `pk-btn-outline`, `pk-btn-ghost` | Primary, outline and on-banner buttons |
| `pk-field`, `pk-label`, `pk-input`, `pk-help`, `pk-error`, `pk-req` | Form fields |
| `pk-form-layout`, `pk-form-card`, `pk-step`, `pk-step-num` | Numbered step-by-step forms |
| `pk-equip`, `pk-stepper` | −10 / −1 / +1 / +10 quantity steppers |
| `pk-ticket` | Dark live-summary card with a perforated edge |
| `pk-drop` | File upload drop zone that shows the chosen file names |
| `pk-empty`, `pk-errors`, `pk-sr` | Empty state, error list, screen-reader-only text |

---

## 5. Navigation — same in every portal

The portal layouts (`layouts/admin`, `layouts/college`, `layouts/org`, and the retired `layouts/guest`) just pass their menu items to `portal-shell`.

- **Sidebar header:** UA seal, "University of Antique / Facility Management System", and a menu button.
- **Menu:** an icon for every item, quiet section headings, and a dark highlighted pill for the current page.
- **Footer:** a profile card (initials, name, role and unit, e.g. "College Staff · CCIS") and a Logout button.
- **Collapse (desktop):** the menu button morphs into an ✕ and shrinks the sidebar to a 72px rail showing only the seal; clicking the seal (it spins) expands it again. The choice is remembered in the browser (`localStorage` key `adminSidebarCollapsed`).
- **Phones (≤ 900px):** a menu button in the top bar opens the sidebar as a drawer. Tapping outside it or pressing Esc closes it.
- **Top bar:** the portal name ("GSU Admin Portal", "College Staff Portal", "Organization Staff Portal", "Guest Portal") shows on the dashboard only, alongside the notification bell. The Logout link was removed from the top bar; it lives in the sidebar.
- **Menus today:**

  | Portal | Menu |
  |---|---|
  | Admin | Dashboard, Monthly records · All facilities, Add facility · **All Requests** (was "Approve Requests"), Repair & maintenance, Reservations, Create Direct Reservation, Reservation calendar, Monthly overview · Users, Create user |
  | College Staff | Dashboard, Reservation calendar, Reservations · My requests, New request, Repair & maintenance · **Users** (new) |
  | Org Unit | Dashboard, My Reservations, Reservation calendar · My requests, New request · **Users** (new) |

- **Guest portal:** retired. Every `/guest/…` address now redirects to the public request form ([§10](#10-guest-access-no-account)).

---

## 6. Public pages

### 6.1 Landing page (`welcome.blade.php`)

Redesigned on Oct 17 in black, white and greys (styles scoped to `.ul`, section "LANDING (welcome.blade.php) — monochrome" in `app.css`).

- **Hero:**
  - Headline "Reserve a venue at the University of Antique." with a short lead that says guests don't need an account.
  - Buttons: **Request a facility** (opens the Guest Access page), **View schedule** (jumps to the calendar) and a **Track a request** link.
  - On the right, a campus photo slideshow sits inside the seal's triangle, with a **"Next on the calendar"** card showing the next reservation.
- **Kruhay Bambuhay + Staff sign in:**
  - Welcome text with a "See venue rates" link.
  - Three sign-in cards (01 Admin, 02 College Staff, 03 Org Unit). The Guest card was replaced by a wide **Guest Access** link: "Guests & partner organizations can book a venue, then track it with a ticket code. No account needed."
- **Ticket tracker:** a dark band (`#track`) with a responsive search card. On desktop, the request type and tracking code share the first row, with the submitter's last name and **Check status** button on the second row; on narrow screens the fields stack.
  - No sign-in is needed. The type dropdown offers **Facility (FM)**, **Booking (BK)** and **Maintenance (RM)**. Every lookup requires the tracking code and the last name of the person who submitted the request.
  - **What the type controls:**

    | | Facility (FM) | Booking (BK) | Maintenance (RM) |
    |---|---|---|---|
    | Example code | `FM-2026-00123` | `BKG-…` | `RM-2026-00045` |
    | Pop-up details | Status, venue, date and time | Status, venue, purpose, date and time | Status, facility, subject and sent date |

  - **Lookup endpoint:** the form calls `GET /track/ticket` (`ticket.lookup`, limited to 20 requests per minute), with the selected type, code and last name.
  - **Name check:** new accounts and guest requests store the tracking last name separately; the user enters that exact value when looking up a record. The app does not guess a surname from a full name. Existing records without an explicit tracking last name retain legacy full-name matching until the account/requester is updated. A missing or non-matching value does not reveal the record; a wrong code and a wrong name return the same not-found result.
  - **Privacy and actions:** results show status and relevant schedule/request details, not the requester's identity. Lookups are read-only. Approved guest facility requests retain their existing signed receipt/PDF actions where applicable; internal facility requests, bookings and maintenance tickets do not expose editing actions.
  - **Rescheduled facility requests:** if GSU rescheduled the booking, the tracker shows its updated schedule.
  - Too many tries show a busy message (HTTP 429).
- **Key Facilities:**
  - A band with a centered title and description.
  - Photo cards, four at a time. Photos are grayscale and turn to color on hover.
  - Semi-transparent ‹ › arrows, vertically centered on each side, scroll through more facilities.
- **Reservation calendar:** the shared compact calendar ([§11](#11-shared-pages)).
- **Footer:**
  - Main footer: circular 80px UA seal, bold "University of Antique", contact and system information.
  - Sub-footer: left-aligned "© {year} **University of Antique**".

### 6.2 Login (`auth/login.blade.php`)

- A grayscale Grand Stand photo behind a slate gradient.
- A rounded card (20px corners) with the portal name taken from `?portal=` (e.g. "Admin").
- A show/hide password toggle and a "back to home" link.
- Autocomplete hints on the email and password fields.
- **Portal check (new):** the card used on the landing page is sent as a hidden `portal` field (`admin`, `college` or `org`). An account that belongs to another portal is signed out again with a message such as "This account can't sign in to the Admin Portal. Please use the College Staff Portal instead."
- **Guest accounts** can no longer sign in. They see "Guest accounts no longer sign in. You can request a facility from the home page — no account needed."

### 6.3 Public header (`layouts/partials/header.blade.php`)

Links: Facilities, Schedule, **Guest access**, **Venue rates**, About. Signed-in staff also see Dashboard and Sign out.

### 6.4 Guest Access (`public/guest-access.blade.php`, `/guest-access`)

An invitation page for outsiders: a hero with venue photos fanned like polaroids, then **Who can request**, **How it works**, **Venues & rates**, **What to prepare**, and a closing button to the request form.

### 6.5 Venue rates (`public/rates.blade.php`, `/venue-rates`)

The official GSU rate cards (12 venues: rate, capacity, package). The figures come from **`resources/views/public/venue-rates.php`**, which the request form's fee calculator also reads, so the two always agree. Update rates in that one file.

---

## 7. Admin portal

| Page | Highlights |
|---|---|
| **Dashboard** | Greeting banner with the date and buttons. Four number tiles (requests, awaiting approval, reserved, open maintenance). A request donut with its legend beside it and counts in the labels. Slate bar chart of maintenance tickets. Quick-access icon cards that invert on hover. |
| **Monthly records** | Banner with the month name and ‹ › month arrows, plus the month/year filter and Print inside it. Tiles: bookings, rental fees, venues used, busiest venue. Trend line and usage bars. A zebra-striped booking ledger (dates, times and contact numbers kept on one line). Ranked "Venues utilized" bars. Print style hides the navigation and controls. |
| **All facilities** | Photo card grid (grayscale, color on hover), owner tag, availability label, location, description, Edit / Delete (confirmation kept). |
| **Add facility** | Four steps (Basic details, Ownership, Description, Availability). Availability is three choice cards; Active is a toggle switch. Live preview card. |
| **All Requests** (was Approve Requests) | Banner with a total. Status tabs that use the existing `?status=` filter. Restyled filter bar. Table with code text, requester initials and status labels. **Order (Oct 19):** Pending first, then Payment being verified, Approved, Reserved, Disapproved and Cancelled; newest first within each group. Tabs, filters and search keep this order. |
| **Request detail** (new layout) | Banner with the venue, `FM-…` code and status; a progress tracker (Submitted → In review → Approved → Reserved); Event details (multi-day ranges included); Requester & signatories (guest requests show the guest's name); **Decision** (Approve / Disapprove) or **Documents**; Equipment; Payment. The Facilities & Utilization Form PDF link appears for approved and reserved requests. "Back" returns to the page you came from. |
| **Repair & maintenance** | Tiles (Open, Completed, All tickets). Open requests list pending tickets first. "Facility removed" is shown in gray. |
| **Repair ticket detail** (new layout) | Same layout as the College/Org ticket detail (issue details, sender card, progress), plus the admin **Update ticket** form. |
| **Reservations** | Status tabs, filter bar, and a date tile in each row. Venue chips, status label, View / Modify, and Generate PDF when available. |
| **Create Direct Reservation** | Amber priority notice and four steps, built like the College/Org request form: **01 Schedule** (date picker calendar with booked days and an "On this day" panel, plus start and end time), **02 Venues** (venue availability board; pick one or more, still sent as `facility_ids[]`; a card warns "Overlaps an existing booking — it will be set to Pending"), **03 Requester & purpose**, **04 Equipment** (tiles with Quick start sets). A live booking-summary ticket sits beside the form. **Oct 19 polish:** requester and purpose are plain aligned fields instead of nested boxes; spacing between steps and fields is more even; equipment shows 4 tiles per row (2 on phones); on phones the step number sits beside the heading. |
| **View / Modify booking** | Same building blocks as Create Direct Reservation (schedule, venues, purpose + reason, equipment, summary). The booking being edited is not flagged as a clash with itself. A separate **Cancel (GSU override)** form sits below. |
| **Reservation calendar** | Banner and tiles (reservations, facilities booked, busiest facility), the shared calendar (its arrows stay on `/admin/calendar`), then a ranked facility list with bars. |
| **Monthly overview** | Banner with month arrows and tiles. **The chart now renders** (see [§12](#12-fixes-made-along-the-way)): horizontal slate bars with hover tooltips and a "Show as table" option. |
| **Users** | **Admin team** cards at the top: everyone with the Admin role, with their position, email and contact number, an edit button, and an "Add admin staff" card. Below, **All users** rows with initials, role labels, **Edit** and **Deactivate**. Deactivation prevents sign-in, cancels future facility requests and pending/upcoming bookings, and preserves all records; past/completed activity is unchanged. You can't deactivate your own account or the walk-in guest system account. |
| **Create / Edit user** | Three steps (Profile, Password, Role). Names allow letters, spaces, hyphens, and apostrophes; passwords require 8–94 characters, uppercase and lowercase letters, and a special character. Profile includes a **contact number** (`09xx-xxx-xxxx`). Role is four icon cards. **Admin** shows a "Position in the admin team" field with suggestion chips. When editing, leave the password empty to keep it; you can't change your own role. Hidden role fields are disabled, so only the visible College or Organization name is sent. |
| **Audit trail** | Admin-only searchable history at `/admin/audit-logs`, including model changes and authentication events. Sensitive values are redacted. |

---

## 8. College Staff portal

| Page | Highlights |
|---|---|
| **Dashboard** | Greeting banner with a college chip and totals. **Request tracker**: Pending → Pending payment → Approved → Reserved, with Disapproved shown separately; steps with requests turn dark. Repair-status bars and six quick-access tiles. |
| **Reservation calendar** | Tiles (campus reservations, facilities booked, your college's bookings), the calendar, and an **availability board** where your college's facilities are labelled "Your college" and listed first. |
| **Reservations** | **Timeline grouped by month**: day/date tile, time, purpose, venue chips, status label, and "in 5 days" or "Done". Past bookings are grayed. |
| **My requests** | A card per request with a **4-step progress tracker** (Submitted → In review → Approved → Reserved). Disapproved and cancelled requests read "no further steps". Cancel (pending only) and View. |
| **Request detail** | Shared `request-detail` partial (see below). |
| **New request** | Five steps. **Date picker calendar** and **venue availability board** (see below). Signatory cards with a custom-name option. Equipment tiles, optional documents, and a live summary ticket. The ticket pop-up opens after submitting. |
| **Repair & maintenance** | Tickets shown as **ticket stubs**: the code on a dark stub with a perforated edge, gray when closed. **Shared within the college (Oct 19):** the list is titled "College tickets" and shows every ticket from your college, each marked "Sent by you" or "Sent by *name*". |
| **Repair ticket detail** | Shared `ticket-detail` partial: issue details (now with a **Sent by** row), the administrator's update, and a progress tracker (Admins notified → Waiting to start → In progress → Completed / Not completed). **Conversation (Oct 19, College only):** a live chat with the GSU admins under the issue details; see below. |
| **New repair request** | Laid out like an email: From / To / Facility / Subject rows and the message, with a character counter. A "What happens next" panel beside it. **Others (Oct 19, College only):** the Facility list ends with "Others (not on the list)", which reveals an **Others:** row to type the place (e.g. "CCIS Faculty Room"). The typed place is saved on the ticket and shown as its facility on every ticket list and detail page, Admin included. |
| **Users** (new) | Team accounts for the college; see below. |

### Request ticket and detail pages

- **Ticket code:** every facility request has a code `FM-<year submitted>-<request no.>` (for example `FM-2026-00012`). It is the code the landing page tracker accepts.
- **Ticket pop-up** (`request-ticket` partial): code, status with a short explanation, venue, date and time. It opens by itself right after a College or Org request is submitted (`portal-shell` looks up the user's newest request when the "submitted to GSU" message is shown). It can also be opened from the detail page with **View ticket**.
- **Detail page** (`request-detail` partial, used by `college|org/requests/facilities_show`): a banner with what, when and where; the progress tracker; event details; equipment; and Cancel while the request is still pending.

### Repair ticket conversation (Admin ↔ College staff)

Each College repair ticket has a **Conversation** card on its page, and the same card appears on the Admin ticket page (`layouts/partials/ticket-conversation.blade.php`).

- **Chat layout:** your messages on the right in dark bubbles, the other side's on the left, each with name, role (GSU position or college) and time.
- **Live:** sending doesn't reload the page. While the page is open it checks for new messages every 10 seconds (and right away when you come back to the tab). A green **Live** badge shows it's active.
- **Sending:** Enter sends; Shift + Enter adds a new line; up to 2,000 characters; at most 30 messages a minute.
- **Who can take part:** any admin, and **anyone in the sender's college**. College repair tickets are shared within the college (Oct 19), so a colleague can open the ticket, read the admin's update and reply. Org tickets don't get a conversation and stay with their sender.
- **Notifications:**
  - A message from college staff notifies all admins and the rest of that college.
  - An admin reply notifies everyone in the sender's college.
  - The admin's **Update ticket** form notifies everyone in that college when the status changes **or** when only the "Update for sender" text changes. Before, only a status change sent a notification, and only to the sender.
- **Why it changed:** an admin's reply and update went to a colleague's ticket with the same subject ("Aircon"), so the staff member who was waiting never saw them.
- **Closed tickets:** once the status is Success or Failed, the chat becomes read-only ("This ticket is Completed, so the conversation is read-only.") and the badge reads **Closed**. An open page locks by itself on its next check. If an admin moves the ticket back to Pending or Ongoing, the chat opens again.
- The existing "Administrator update" box and status form are unchanged.

### Team accounts (Users page)

College and Org staff can manage accounts for **their own** college or organization (`College\UserController`, `Org\UserController`, shared trait `ManagesUnitUsers`).

- **Who is the head:** the oldest account in the unit, plus anyone whose position is **Dean** (College) or **Governor** (Org).
- **The head can:** add accounts, edit anyone in the unit, set positions (and so appoint another head), and deactivate accounts. Deactivation cancels future facility requests and pending/upcoming bookings; all records are preserved and past/completed activity is unchanged.
- **Everyone else can:** see the list, and edit their own name, contact number, email and password, but not their position.
- **New accounts** get the creator's role and the same college or organization automatically.
- **Position suggestions:** College: Dean, Program Head, Staff. Org: Governor, Vice Governor, Secretary, Treasurer, Auditor, Public Information Officer, Adviser.
- Accounts are **deactivated**, not deleted. The action preserves the account's history and prevents sign-in; eligible future requests and bookings are cancelled. Users cannot deactivate themselves or the walk-in guest system account.
- Accounts outside your unit return "not found".

---

### New request — date picker calendar

Step 01 (Schedule) of `college/requests/facilities_create.blade.php` has a month calendar for choosing the date of activity:

- **Gray days have reservations**, with a small count under the date (e.g. "3").
- **Selected** day is dark; **today** has an outline.
- **Days before today + 30 are grayed out and can't be picked.** This matches the server rule in `StoreFacilitiesUtilizationRequest`. The date field also gets `min` set to that day.
- **‹ › arrows** change month; going back before the earliest allowed month is disabled.
- **"On this day" panel:** lists what's already booked on the chosen date (facility and time), or "No reservations yet — this date is open". A selected venue's overlapping time is highlighted and blocks submission; non-overlapping times on the same date remain available.
- The original **Date of Activity** field is still there and still submits `date_activity`. Typing a date moves the calendar to it, and clicking a day fills the field (and the summary card).

**Where the booked days come from:** no new route was added. The picker loads the existing college calendar page (`route('college.calendar')?month=YYYY-MM`) in the background, once per month, and reads the booked days from it. To support this, the shared calendar partial now marks each day cell with `data-date` / `data-count` and each reservation row with `data-date` / `data-time` (see [§11](#11-shared-pages)). If loading fails, the calendar still works and shows "Couldn't load booked dates. You can still pick a date."

Like the Reservation calendar, the picker counts only **reserved and rescheduled** bookings; requests that are still pending don't show up.

### New request — venue availability board

Step 02 (Venue) shows every facility as a card, in the same style as the Reservation calendar's availability board:

- **Header pill:** "Availability on Wed, November 18, 2026" once a date is chosen in step 01, otherwise "Pick a date in step 1 to see booked times".
- **Each card shows:**
  - the facility name and owner (GSU / College (…));
  - a big number of bookings that day;
  - each booked time slot (e.g. "10:00 AM – 2:00 PM"), or **"Free all day"**;
  - a status label: Available, Partly booked, Under Maintenance or Unavailable;
  - **"Your college"** on your own college's facilities, which are listed first.
- **Clicking a card selects it** (dark outline and "Selected"). Checkboxes allow one or more facilities to share the same date and time schedule. Unavailable or under-maintenance facilities can't be selected.
- **Overlap blocking:** once start and end times are entered, cards with overlapping bookings are highlighted and the time-availability status identifies the facility, date and reserved time. Submission is blocked until the requester chooses a non-overlapping range. Adjacent reservations are allowed.
- **Data source:** the same calendar data the date picker loads. The picker fires a `dp:day` browser event with the chosen date and its bookings, and the board redraws from it.

With a multi-day range selected, the header reads "Availability Nov 17 → Nov 19, 2026 (3 days)". Each card then lists every booked slot with its date ("Nov 18 · 10:00 AM – 2:00 PM"), says "Free all 3 days" or "Booked 1 of 3 days", and flags any overlap. A conflict on any selected day blocks the entire request.

**Reservation conflicts block submission.** Existing bookings and their times are shown on the availability board. The server validates every facility/day slot as well as the browser check; approval rechecks availability to handle reservations made after submission.

### New request — booking details (status, event, who, until when, whole/half day)

Every existing booking shown in the form is a small entry laid out as: time and **Booked** on the first line, then the facility (in the "On this day" panel only), the event, who booked it, and a footer line with whole/half day and the end time. Each entry displays:
- its **time**;
- a red **Booked** label (reserved and rescheduled bookings are both shown simply as "Booked");
- **whole or half day** (Whole day for 6+ hours; otherwise Half day, with "· Morning" if it ends by 1 PM or "· Afternoon" if it starts at noon or later);
- the **event** (purpose);
- **who** booked it, as type and unit (e.g. "College · CBA", "Org Unit · Red Cross Youth", "Guest · ABC Company");
- **until when** ("Until 5:00 PM (9 hrs)").

These appear in three places: on each venue card, in the "On this day / On these days" panel, and in the hover tooltip of a calendar day. A venue card booked for a whole day is labelled **Booked whole day** in red.

**How the data gets there:**
- Each reservation row in the shared calendar partial now carries `data-start`, `data-end` (24-hour) and `data-status`.
- `data-event` and `data-who` are written **only when the including page passes `exposeEventDetails => true`**. Only `college/bookings/calendar.blade.php` does, and the college form reads its data from that page.
- The landing page, guest, org and admin calendars don't include these attributes, and the visible calendar table still shows event names to admins only.
- "Who" shows the requesting unit, not the person's name.

**Layout fix:** booking lines inside venue cards wrap instead of overflowing (previously "Until 12:00 PM · 4 hrs" ran past the card at about 1024px). Checked at widths from 360 to 1536px: nothing crosses a card's or tile's padding.

**Limit:** the calendar data contains only `reserved` and `rescheduled` bookings. Requests still waiting for approval are not bookings and do not appear.

### New request — purpose, signatory and equipment (steps 03 and 04)

**Step 03 · Purpose & signatory**
- **"What's the event?"**
  - Event-type chips (Seminar, Meeting, Workshop, Ceremony, Thesis defense, Sports event, Program) start the purpose text as "Seminar — …".
  - Tapping another chip swaps the type; tapping the same chip again removes it.
  - A meter under the text box counts characters up to **500**, the server's `max:500`, and turns amber from 450.
- **"Who will note it?"** (College)
  - The dropdown is replaced by person cards: initials, name, and a role tag such as "Dean".
  - **From the Users team (Oct 19):** people on the college's Users page who have a position are listed first (Deans, then Program Heads, then other positions), value `user:ID`, and their real name is saved as "Noted by". Matching rows from the old signatories table still follow (`dean:ID`, `program_head:ID`).
  - **Why:** the old list matched signatories to the college by name, and the current accounts didn't match ("CCIS Dean" vs "CCIS"), so no people showed. Nothing in the app lets anyone add signatories, but heads can set positions on the Users page.
  - A person appears once their position is set on the Users page.
  - Plus **Someone else**, which reveals the name box, and **Skip for now** (the default).

**Step 04 · Facilities / Equipment**
- One **compact tile per item** (about 124px tall): the icon and name on one row, then a **− [count] +** row whose middle stretches so it always fits inside the tile, then small −10 / +10 chips. A tile turns dark once its count is above 0. Four tiles per row on desktop, two on phones, one below 360px.
- A dark bar above the tiles shows the **running total** ("107 items · 5 types").
- **Quick start** sets fill common amounts in one tap; every amount can still be changed afterwards:

  | Set | Fills in |
  |---|---|
  | Small meeting | 20 chairs, 2 tables, 2 fans |
  | Seminar | 100 chairs, 4 tables, rostrum, flag, sound |
  | Big program | 300 chairs, 10 tables, 4 fans, rostrum, flag, sound, LED wall |
  | Clear | Resets everything to 0 |

- The inputs keep the same names (`qty_monobloc` … `qty_led`), and the summary card lists the chosen items.
- **Checked:** the form's submitted values (`purpose`, `noted_signatory_id`, `noted_signatory_custom`, `qty_*`) match what the controller expects.

### New request — multi-facility and multi-day events

Requests support all combinations of one or multiple facilities and one day or a continuous multi-day date range. Every selected facility uses the same date range and times. Overlapping active reservations block submission; distinct time ranges on the same facility and date are allowed.

**In the form (schedule and venues):**
- A **One day / Several days** switch sits above the calendar. One day works as before.
- In **Several days**:
  - The first click sets the start and the second click sets the last day. Days in between are shaded, and hovering previews the range.
  - Clicking before the start, or clicking again after a full range, starts over. Clicking the start day twice means a single day.
  - Ranges are capped at **14 days**; going further shows "A multi-day event can last at most 14 days — pick an earlier last day."
- **Fields:** the **Start date** (`date_activity`) and **End date** (`date_end`) fields fill in and can also be typed. The end date is disabled in One day mode, so it isn't sent.
- **Range display:**
  - A dark chip shows the range ("Tue, Nov 17 → Thu, Nov 19 · 3 days").
  - The summary card shows "Nov 17 → Nov 19 (3 days)".
  - "On these days" lists bookings per date, with how many days are free.
- **Same time each day:** the start and end time apply to every day of the event.

**Backend changes**

| File | Change |
|---|---|
| `app/Http/Requests/StoreFacilitiesUtilizationRequest.php` | Validates a non-empty `facility_ids[]` list (while accepting legacy `facility_id` posts), a start date at least **30 days** away, and an optional end date on or after the start, capped at 14 days total. Rejects time overlaps with active reservations across every selected facility and event day. |
| `app/Http/Controllers/Traits/SubmitsFacilitiesForm.php` | Saves the selected facility IDs and names (retaining the first-facility legacy keys) and `date_end` (`null` for single-day requests). |
| `app/Services/BookingService.php` | On admin approval, creates **one booking per day** and attaches all selected facilities to each booking in one transaction. All facilities and days are checked for conflicts immediately before reservation; a conflict leaves the request pending. |
| `app/Http/Controllers/Admin/FacilitiesFormPdfController.php` | Prints the full date range and checks every selected venue in the generated form. |

**Display:** request summaries and detail pages show every selected venue and the full date range. Availability status identifies overlapping facilities, dates and booked times, and blocks conflicting submissions.

**Unchanged:** a single-day, single-facility request still works as before. Public/guest estimates sum the per-day rates for every selected venue.

**Verified:** a script ran inside a rolled-back database transaction. It confirmed that:
- single-day and 3-day requests pass;
- an end date before the start fails, and so does a 15-day range;
- the payload stores `date_end`;
- approval creates three bookings (`…-1`, `…-D2`, `…-D3`);
- a later request that overlaps an existing reservation fails submission validation, including when the conflict occurs on a later day of the date range;
- a back-to-back time slot and a same-time slot on another facility pass validation.

Nothing was kept in the database.

### Update — blocking time availability (October 21, 2026)

College, Org, Guest, and Public request forms now show a live availability state while the requester selects dates, facilities, and times. The venue cards list reservations on each selected date; overlapping selected slots are highlighted, and the status panel reports the facility, date, and reserved interval. The browser blocks conflicting submissions, and server validation independently rejects them across every selected facility and event day.

The overlap rule is `existing_start < requested_end && existing_end > requested_start`. This permits back-to-back reservations when one slot ends exactly as the next begins, and permits simultaneous bookings at different facilities. Admin approval still rechecks all slots transactionally in case availability changes after submission.

**Verified:** targeted feature coverage includes conflicts within a single day and on later days of a multi-day request, both back-to-back boundaries, and simultaneous reservations at another facility. The full suite passed (**23 tests, 90 assertions**); Blade compilation, PHP lint, Vite build, and Playwright conflict/available-state checks passed.

## 9. Organization Unit portal

The same designs as College Staff, with org-specific details:

- **Dashboard:** organization chip, facility-request totals, and quick access to facility requests and reservations. Organization accounts have no maintenance-ticket access.
- **New request:** Cancel goes to the org dashboard, as before.
- **"Who will note it?" (Oct 19): required, and must come from the college side.** Organization requests are noted by a college **Dean** or **Program Head**, whatever the requester's position in the organization (Governor or any other officer). Three cards:

  | Card | They type | Saved as |
  |---|---|---|
  | **Dean** | the Dean's name | name + position "Dean" |
  | **Program Head** | the Program Head's name | name + position "Program Head" |
  | **Someone else** | their position (e.g. "OIC Dean") and name | name + typed position |

  - Only the picked card's boxes are sent, and they are required. There is no "Skip" option and nothing is preselected.
  - Server messages: "Choose who will note your request…", "Type the name of the person…", "Type the position of the person…".
  - The org officer options (President, Adviser, Governor) were removed from this step.
  - The position is saved in the request as `noted_signatory_position` and shown next to the name ("Jose Rizal · OIC Dean") on the request detail page, the ticket pop-up and the admin request page. The printed PDF still shows the name only.
- **Reservation calendar:** the controller provides bookings grouped by day only, so this page shows reservations, booked days, the busiest day, and a "Booked days" list with a bar per day.
- **My Reservations, My requests, request detail, and ticket pop-up:** shared with College Staff. Repair reporting is available only in the College portal.
- **Users:** the same team accounts page as College, with **Governor** as the head position ([§8](#team-accounts-users-page)).

---

## 10. Guest access (no account)

The guest portal and guest sign-in were **retired**. Outsiders now request a facility from public pages and follow up with a ticket code.

**The flow**

1. **Guest Access** (`/guest-access`) explains who can request and how ([§6.4](#64-guest-access-publicguest-accessbladephp-guest-access)).
2. **Request form** (`/request-facility`, `public/request-facility.blade.php`): the "Application for Facilities Rental" with the official UA header (GSU-FM-004). No top bar, just a way back. Steps:
   - **Applicant:** name, contact no., email (GSU uses it to reach them), organization/agency, address.
   - **Date & time:** the same date picker as the College form, with One day / Several days.
   - **Venue & purpose:** GSU (non-college) facilities only. Each card shows its **rate** and what's already booked on the chosen date.
   - **Equipment:** the venue fee is filled in from the venue and number of days (rates from `venue-rates.php`); equipment quantities are added below. An estimated total sits beside the form (and above Submit on phones).
   - **Certification & approval:** certification statement and the guest's immediate head ("Noted by").
   - **Documents:** optional receipt upload after GSU requests payment; JPG/JPEG, PNG, or PDF up to 10 MiB. Supporting attachments are optional.
3. **Submitted page** (`/request-facility/submitted/{id}`): "Thank you, *name*!", the ticket pop-up opens by itself, and three next steps (save the code, receipt review, track it on the home page with the code and last name). Only the browser that submitted the request can open this page.
4. **Tracking:** the landing page tracker ([§6.1](#61-landing-page-welcomebladephp)).

**How it is stored:** every guest request belongs to one shared system account, **`walk-in-guest@ua-fms.local`** ("Walk-in Guest"), created automatically and never used to sign in. The guest's own details (name, contact, email, address, organization, "Noted by") are saved in the request. Admins see the guest's name on the request. This account can't be deleted from Admin → Users.

**Protection:** submitting is limited to 5 requests a minute, and a hidden "website" field catches spam bots.

**Old guest pages:** `resources/views/guest/*` and `layouts/guest.blade.php` are still in the codebase but no longer reachable: every `/guest/…` address redirects to `/request-facility`.

---

## 11. Shared pages

- **Reservation calendar partial** (`public/calendar.blade.php`), used by the landing page and all portals:
  - A compact 360px month calendar beside one scrollable reservations table (date, requester-type icon, event, facility).
  - The event name stays visible to admins only, as before.
  - New opt-in parameter `hideCalendarHeading`: portal pages that already have a banner pass `true` to hide the "Reservation Schedule" heading.
  - Day cells carry `data-date` (and `data-count` when booked); reservation rows carry `data-date` and `data-time`. These are invisible and are read by the college request date picker ([§8](#8-college-staff-portal)).
- **Notifications** (`notifications/index.blade.php`):
  - Uses the viewer's own portal layout.
  - A feed grouped by day: type icons, an accent on unread items, All / Unread tabs, and "Mark as read" (same endpoint).
  - Counters update without reloading.

---

## 12. Fixes made along the way

| Fix | Where |
|---|---|
| The **Monthly overview chart never rendered**: its script was pushed to a `scripts` stack that no layout outputs. It is now inline and uses Chart.js. | `admin/overview/index.blade.php` |
| The **Org calendar's ‹ › arrows opened the Reservations page**; they now stay on the calendar. | `org/bookings/calendar.blade.php` |
| The **"Submitting…" guard attached to the wrong form** (the sidebar's logout form comes first on the page). It now targets its own form by id. | college/org/guest new request, new repair request |
| **Add facility:** after a validation error with "College" selected, the College Name field stayed hidden. | `admin/facilities/create.blade.php` |
| **Duplicate success messages** on Approve Requests and Facilities (the layout already shows them). | admin pages |
| The **admin layout was not full width** (it used the wrong section name `main-class` instead of `main-content-class`). | `layouts/admin.blade.php` |
| **Monthly records lost the sidebar** (it wrote into `content` instead of `admin-content`). | `admin/monthly-records/index.blade.php` |
| **Footer padding, invisible back link and black hovers** caused by catch-all rules in `app.css` were worked around with scoped class names. | landing, login, layouts |
| **Create user sent an empty organization name**: the Org and External organization inputs shared one name. Hidden role fields are now disabled, so only the visible one is sent. | `admin/users/create.blade.php` |
| **Migrations failed on a fresh database**: the guest-role migration added `organization_name` again although an earlier migration already creates it. It now skips the column when it exists, and its rollback no longer drops it. | `database/migrations/2026_10_08_183233_…` |
| **Create Direct Reservation spacing (Oct 19)**: the requester and purpose inputs didn't line up with their labels, step spacing was uneven, and on phones each step number took a row of its own. | `admin/bookings/create_direct.blade.php`, `app.css` (`.drv-page` rules) |
| **"Who will note it?" had no people (Oct 19)**: the College and Org forms showed only "Someone else" and "Skip", because the signatories table didn't match the units' names. | College and Org `FormController`, both `facilities_create` views |
| **Admin replies and updates didn't reach the waiting staff member (Oct 19)**: they were on a colleague's ticket with the same subject, and editing only the update text sent no notification. | Admin and College `MaintenanceTicketController` |
| **All Requests mixed cancelled requests in with pending ones (Oct 19)**: the list was sorted by control number, so every request without one rose to the top whatever its status. | `Admin/FormSubmissionController` |

---

## 13. Historical audit notes

Earlier UI critiques and issue snapshots are preserved in [`NEED_TO_BE_FIX.md`](NEED_TO_BE_FIX.md), [`NEED_TO_FIX_AND_UPDATE.md`](NEED_TO_FIX_AND_UPDATE.md), and `.impeccable/critique/`. Their findings and scores reflect the dates recorded in those files and are not a live issue tracker. Verify any item against the current code before treating it as open.

Current verification guidance is in [§16](#16-verification) and [TESTING_GUIDE.md](TESTING_GUIDE.md).

---

## 14. How to work with the new UI

**Add a page to a portal**

```blade
@extends('layouts.college')            {{-- or admin / org --}}

@section('college-content')             {{-- admin-content / org-content --}}
@include('layouts.partials.page-kit')

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">Section</p>
            <h1>Page title</h1>
            <p class="pk-hero-sub">One-line description.</p>
        </div>
    </section>
    {{-- pk-stats, pk-table-wrap, pk-empty, ... --}}
</div>
@endsection
```

**Add a menu item:** edit the `sections` array in the portal's layout file (`resources/views/layouts/<portal>.blade.php`).
- `icon` takes a key from `$navIcons` in `portal-shell.blade.php`: `dashboard`, `records`, `building`, `plus`, `approve`, `wrench`, `bookmark`, `edit`, `calendar`, `chart`, `users`, `user-plus`, `tag`, `inbox` or `file-plus`.
- Per the navigation rule, check whether the other portals need the same item.

**Add styles for a page:** put them in `resources/css/app.css` under a `/* ── <view path> ── */` heading, not in a `<style>` block. Scope page-only tweaks to a class on the page wrapper (for example `.drv-page` on Create Direct Reservation) so shared forms aren't affected. Then run `npm run build`.

**Things to watch out for**

- `resources/css/app.css` contains broad rules marked `!important` inside a CSS layer. Class names containing `back` or `refresh` on links and buttons are hidden or restyled by them, so avoid those words in class names.
- Tailwind's `.hidden` loses to the page-kit `.pk-field` rule; `page-kit` already includes `.pk-field.hidden { display: none; }`.
- The landing page resets `* { margin: 0 }`, so `<dialog>` elements need `margin: auto` to stay centered.

---

## 15. Files changed

**New views and partials**
- `layouts/partials/`: `portal-shell`, `page-kit` (now empty), `notification-bell`, `flash-toast`, `request-ticket`, `request-detail`, `ticket-detail`, `ticket-conversation` (repair chat), `unit-users-index`, `unit-users-create`, `contact-number-input`
- `public/`: `request-facility`, `request-submitted`, `guest-access`, `rates`, `venue-rates.php` (rates data)
- `admin/bookings/partials/booking-picker-script`
- `college|org/users/index`, `college|org/users/create`

**Layouts:** `layouts/app`, `layouts/admin`, `layouts/college`, `layouts/org`, `layouts/guest` (retired), `layouts/partials/sidebar`, `layouts/partials/header`

**Public / shared:** `welcome`, `auth/login`, `public/calendar`, `notifications/index`

**Admin:** `dashboard`, `monthly-records/index`, `facilities/index`, `facilities/create`, `forms/facilities_index`, `forms/facilities_show`, `maintenance-tickets/index`, `maintenance-tickets/show`, `bookings/index`, `bookings/create_direct`, `bookings/edit`, `calendar/index`, `overview/index`, `users/index`, `users/create`

**College:** `dashboard`, `bookings/calendar`, `bookings/index`, `requests/facilities_index`, `requests/facilities_create`, `requests/facilities_show`, `maintenance-tickets/index`, `maintenance-tickets/create`

**Org:** `dashboard`, `bookings/calendar`, `bookings/index`, `requests/facilities_index`, `requests/facilities_create`, `requests/facilities_show`

**Guest (retired, kept in the codebase):** `dashboard`, `rates`, `bookings/calendar`, `bookings/index`, `requests/facilities_index`, `requests/facilities_create`, `requests/facilities_show`

All view paths above are under `resources/views/`.

**Styles:** `resources/css/app.css` now holds every page's styles (see [§4](#where-the-styles-live)).

**Other:** `documentation.md` (this file), `CLAUDE.md` (project rules), `TEST_ACCOUNTS.md` (development seeder guidance).

### Backend changes (approved exceptions)

| Feature | Files |
|---|---|
| Multi-facility, multi-day, and blocking time availability | `app/Http/Requests/StoreFacilitiesUtilizationRequest.php`, `app/Http/Controllers/Traits/SubmitsFacilitiesForm.php`, `app/Services/BookingService.php`, `resources/views/partials/facility-time-availability.blade.php`, College/Org/Guest/Public facility request forms, `app/Http/Controllers/Admin/FacilitiesFormPdfController.php`, `tests/Feature/MultiFacilityRequestTest.php` |
| Public guest requests | `app/Http/Controllers/PublicFacilityRequestController.php` (new); routes `/request-facility` (GET, POST throttled 5/min), `/request-facility/submitted/{submission}`, `/venue-rates`, `/guest-access`; `/guest/*` redirects to `/request-facility` |
| Ticket lookup | `app/Http/Controllers/TicketLookupController.php` (new); route `GET /track/ticket` (`ticket.lookup`, throttled 20/min) |
| Portal-only sign-in, guests blocked | `app/Http/Controllers/AuthController.php` |
| Team accounts | `app/Http/Controllers/Traits/ManagesUnitUsers.php`, `app/Http/Controllers/College/UserController.php`, `app/Http/Controllers/Org/UserController.php` (all new); routes `college.users.*`, `org.users.*` |
| Admin users (edit, deactivate, admin team, position, contact number, tracking last name) | `app/Http/Controllers/Admin/UserController.php`, `app/Http/Requests/StoreUserRequest.php`, `app/Models/User.php`; routes `admin.users.edit`, `update`, `deactivate` |
| New user columns | `database/migrations/2026_10_17_000001_add_position_to_users_table.php` (`position`, 120 chars), `database/migrations/2026_10_18_000001_add_contact_number_to_users_table.php` (`contact_number`, 30 chars). Run `php artisan migrate`. |
| Repair "Others" place (College) | `database/migrations/2026_10_19_000001_add_facility_other_to_maintenance_tickets_table.php` (`facility_id` becomes optional; new `facility_other`, 160 chars), `app/Models/MaintenanceTicket.php`, `app/Http/Controllers/College/MaintenanceTicketController.php` (choosing `other` requires the typed place) |
| Repair ticket conversation | `database/migrations/2026_10_19_000002_create_maintenance_ticket_messages_table.php` (new `maintenance_ticket_messages`: ticket, user, body; deleted with the ticket), `app/Models/MaintenanceTicketMessage.php` (new), `app/Models/MaintenanceTicket.php` (`messages()`, `isClosed()`), `app/Http/Controllers/Traits/HandlesTicketMessages.php` (new), `Admin\MaintenanceTicketController`, `College\MaintenanceTicketController`; routes `admin.maintenance-tickets.messages(.store)` and `college.maintenance-tickets.messages(.store)` (GET returns JSON, POST throttled 30/min) |
| Repair (RM) ticket lookup | `app/Http/Controllers/TicketLookupController.php` (code + last name → status, facility, subject, sent) |
| "Noted by" (College) | `app/Http/Controllers/College/FormController.php` (`teamSignatories()`: college Users with a position; `user:ID` saves the real name) |
| "Noted by" (Org, required) | `app/Http/Controllers/Org/FormController.php` (validates `noted_signatory_id` = `dean` / `program_head` / `custom`, `noted_signatory_name`, `noted_signatory_position`; saves name and position in the payload) |
| Shared college repair tickets | `app/Models/User.php` (`collegeTeamIds()`), `College\MaintenanceTicketController` (list, view and conversation for the whole college; notifies colleagues), `Admin\MaintenanceTicketController` (replies and updates notify the whole college; a changed update text also notifies) |
| All Requests order | `app/Http/Controllers/Admin/FormSubmissionController.php` (`index`: sort by status, newest first) |
| Migration fix | `database/migrations/2026_10_08_183233_add_guest_role_and_organization_field_to_users_table.php` |
| PDF form | `app/Http/Controllers/Admin/FacilitiesFormPdfController.php`: available for approved and reserved requests |

---

## 16. Verification

Run `php artisan test` from the current checkout for the authoritative automated result. The latest recorded run for the tracking-name and booking-flow changes passed **38 tests with 174 assertions**; results can change as code changes.

The public tracker and guest request form were also visually checked with Playwright during the current tracking-name change. See [TESTING_GUIDE.md](TESTING_GUIDE.md) for reproducible feature checks and the UI smoke-test checklist.
