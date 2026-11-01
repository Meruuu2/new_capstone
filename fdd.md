# Functional Design Document (FDD) — UA Facility Management System

## 1. Overview

The UA Facility Management System is a role-based web application that centralizes facility reservations, utilization requests, and maintenance coordination for the University of Antique. It supports:

- Public users (reservation calendar, no-sign-in guest facility requests, and request/booking/maintenance status lookup).
- GSU admins (full control over facilities, reservations, and users).
- College staff (manage college facilities and submit reservation requests).
- Organization staff (submit and track facility utilization requests; maintenance reporting is not available in the Organization portal).

This document describes the functional behavior of the system from a user and business perspective, independent of implementation details.

---

## 2. Actors & Roles

### 2.1 Public User (Unauthenticated)

- View the public reservation calendar.
- See upcoming reservations per facility.
- Check a facility request (`FM`), booking (`BKG`), or maintenance ticket (`RM`) status without signing in, using the tracking code and the submitter's explicitly recorded tracking last name.
- For new records, the tracking last name is separately entered/stored; the system does not infer it from a full name. Legacy records without an explicit value retain legacy matching.
- View matching status and relevant schedule/request details without exposing the requester's identity. A code or surname mismatch does not disclose the record.
- Public tracking is read-only for status. Approved guest facility requests may additionally expose temporary signed PDF and payment-receipt actions when applicable.

### 2.2 Admin (GSU Staff)

- Manage all facilities (GSU + college + org owned).
- Review and approve/disapprove utilization requests.
- Convert approved requests into reservations.
- Create high‑priority "direct" reservations that can preempt existing ones.
- View and manage all reservations (reschedule, cancel).
- Deactivate staff accounts without deleting their request and reservation history.
- Generate official PDF forms (Facilities Utilization, Repair & Maintenance).
- Review and update repair/maintenance tickets submitted by staff.
- Manage portal user accounts (admins, college staff, org staff).
- Review the read-only audit trail for application changes and sign-in/sign-out activity.
- View system‑wide analytics (monthly reservations per facility, daily counts).

### 2.3 College Staff

- Manage facilities owned by their college (CRUD, availability).
- Submit facilities utilization requests to GSU.
- Submit repair/maintenance tickets and view tickets submitted by members of their college team.
- Exchange messages with GSU on college repair tickets; closed tickets are read-only.
- View and filter:
  - Their own facilities utilization requests.
  - Their reservations (active / rescheduled).
- View read‑only reservation calendars:
  - Public calendar (home page).
  - College calendar (all active reservations, with facility counts).

### 2.4 Organization Staff

- Submit facilities utilization requests (for events/activities).
- View and filter their own utilization requests.
- View their reservations (active / rescheduled).
- View the read-only reservation calendar of all active reservations; the reservations list remains requester-scoped.

---

## 3. Core Functional Areas

### 3.1 Facilities Management

**Owner:** Admin (GSU), College staff (for college‑owned facilities).

**Functional Requirements:**

1. Admin can:
   - Create facilities with:
     - Name, location.
     - Owner type: `gsu`, `college`, or `org`.
     - Owner college (optional).
     - Description, active flag, availability status (`available`, `unavailable`, `maintenance`).
   - Edit any facility.
   - Delete facilities.

2. College staff can:
   - Create facilities where:
     - Owner type is implicitly `college`.
     - Owner college is the staff member's college.
   - Edit facilities belonging to their college.
   - Delete their college facilities.
   - Cannot change core facility ownership or global GSU facilities.

3. Facility availability:
   - `available`: can be reserved.
   - `unavailable` or `maintenance`:
     - Cannot be reserved by the automated request→reservation flow.
     - Public calendar still shows existing reservations for historical transparency.

---

### 3.2 Facilities Utilization Requests

**Owner:** College staff, Org staff; reviewed by Admin.

**Functional Requirements:**

1. **Form fields (College & Org)**:
   - Date of activity (must be at least 30 days from today).
   - Optional end date for a continuous event range (maximum 14 days including the start date).
   - Start time and end time (end > start; both must be between 6:00 AM and 7:00 PM, inclusive).
   - One or more facilities from the active facility list; all share the same dates and times.
   - Public guest venue selection displays the published rental rate for each venue.
   - Purpose of activity.
   - Equipment quantities:
     - Monobloc chairs, tables, fans, rostrum, flag & school color, sound, LED wall.
   - Noted‑by signatory (predefined or custom).
   - System captures requester identity (user, unit), date of request, and the account's separately recorded tracking last name.
   - New staff accounts require an explicit tracking last name. Public guests enter their tracking last name on each facility request.

2. **Reservation conflict handling:**
   - The form shows booked time ranges for selected facilities and dates and blocks submission when a requested time overlaps an active reservation.
   - Back-to-back reservations are allowed when one ends at the exact time the next begins.
   - Approval rechecks and reserves every selected facility on every requested day atomically. If availability changes after submission, no bookings are created and the request remains pending.

3. **Status lifecycle (FormSubmission):**
   - `pending`: newly submitted requests awaiting GSU review. A receipt is not required at submission.
   - `reserved`: admin approval successfully created the reservation; the submission is linked to the booking.
   - `disapproved`: request denied by admin.
   - `cancelled`: requester has cancelled a pending request (college or org).
   - `approved` and `pending_payment`: retained for legacy submissions. Current guest payment progress is tracked separately in `payment_status`; approving a guest request creates its reservation and sets the submission to `reserved`.
   - Guest payment states (`payment_status`) are `not_required`, `pending_payment`, `payment_uploaded`, and `payment_verified`. Receipt upload is available after approval when payment is applicable; no receipt is required at submission.

4. **Cancel request (College & Org):**
   - A requester may cancel **only while status is `pending`**.
   - After cancellation:
     - Status becomes `cancelled`.
     - Request is hidden from "pending approvals" on admin side but visible historically.

5. **College/Org views:**
   - List of own requests, including reserved requests, filterable by:
     - Control number.
     - Status.
     - Activity date.
   - Detail page shows:
     - Request metadata.
     - All selected facilities, date range, and time.
     - Purpose.
     - Equipment breakdown.
     - Signatory information.
     - Cancel button when status is `pending`.

---

### 3.3 Reservations (Bookings)

**Owner:** System (via approved requests), Admin (direct/high‑priority).

**Terminology Note:** The system uses `reserved` as the canonical active status. Older `booked` values are normalized to `reserved`.

**Functional Requirements:**

1. **Creation from approved requests:**
   - Admin approves an eligible pending utilization request.
   - System validates:
     - Request status allows approval and any uploaded receipt has been verified.
     - Every selected facility is `available`.
     - No requested day/time overlaps an active reservation on any selected facility.
   - On success:
     - Creates one `Booking` with status `reserved` for each event day.
     - Links all selected facilities to each booking (many‑to‑many).
     - Updates the request's status to `reserved` directly; if later scheduling work is required, the request remains pending and no booking is created.
     - Sends notifications to requester and admins.

2. **Direct high‑priority reservations (admin):**
   - Admin can bypass the request flow to create direct reservations.
   - Direct reservations:
     - Can span multiple facilities.
     - Convert any overlapping `reserved` reservations for those facilities to:
       - `pending` (conflict is pushed back for renegotiation).
     - Notify affected requesters that their reservation has been preempted.

3. **Statuses (Booking):**
   - `reserved`: active reservation.
   - `rescheduled`: reserved but modified by admin (time/facilities changed).
   - `completed`: end time is in the past (auto‑set).
   - `cancelled`: cancelled by admin.
   - Old `booked` values are mapped to `reserved` on migration/boot.

4. **Auto-completion:**
   - The hourly `bookings:normalize-and-complete` scheduled command:
     - Converts leftover `booked` bookings to `reserved`.
     - Marks `reserved` bookings with an elapsed `end_time` as `completed`.

5. **Modification (Admin):**
   - Admin can:
     - Change facilities.
     - Change date/time.
     - Update purpose.
     - Adjust equipment block.
     - Provide a reschedule reason.
   - System sets status to `rescheduled`, stores reschedule note, notifies requester and co‑admins.

6. **Cancellation (Admin):**
   - Admin can cancel an existing reservation.
   - Requires a cancellation reason.
   - Status becomes `cancelled`.
   - Reason is stored and sent via notification.

7. **College/Org reservation views:**
   - "My Reservations" pages show only `reserved` / `rescheduled` reservations for the requesting user.
   - Filters: search by facility/purpose, status, facility, month.

---

### 3.4 Repair and Maintenance Tickets

**Owners:** College staff and organization staff submit tickets; GSU administrators coordinate and update them.

1. **Ticket submission**
   - Authenticated college and organization staff submit a ticket with a subject, issue description, and listed facility.
   - College staff may choose “Others” and enter an unlisted location.
   - The submitter's account must have a separately recorded tracking last name.
   - Each ticket receives an `RM-…` tracking code and starts with status `notified`.
2. **Administration**
   - GSU admins view active and completed tickets, update status, and add remarks.
   - Supported ticket statuses are `notified`, `pending`, `ongoing`, `success`, and `failed`.
   - Status and remark changes notify the requester; college tickets notify the requester's college team.
3. **College conversation**
   - A college staff member can view tickets submitted by members of their own college and exchange messages with GSU.
   - Messages are shared with the college team. Conversations are read-only after status becomes `success` or `failed`.
4. **Organization access**
   - Organization staff can submit and view their own tickets. The college-wide conversation feature is not part of the organization ticket workflow.
5. **Public status check**
   - Anyone with the ticket code and the requester's exact explicit tracking last name can view ticket status, location, subject, and submission date without signing in.
   - Lookup is read-only and does not expose requester identity.

### 3.5 Calendars

**Owner:** Public, Admin, College, Org.

**Common behavior:**

- Calendar shows days in a month with dots or styling for days containing reservations.
- Hover or title text indicates number of reservations.
- A right‑hand "Upcoming Reservations" list highlights the next few reservations.

**Views:**

1. **Public home calendar (`/`):**
   - Route: `home` → `PublicCalendarController@index`.
   - Shows all `reserved` + `rescheduled` reservations within the month.
   - Navigation stays on `/` with `?month=YYYY-MM#calendar`.
   - The homepage also provides a public request tracker for `FM`, `BK` (booking reference `BKG-…`), and `RM` records. Each lookup requires the tracking code and the submitter's last name; login is not required.
   - Lookup results return status and relevant record details only. FM requests may include temporary signed PDF and receipt-upload links for approved guest requests; these actions do not grant access to internal record editing.

2. **Admin reservation calendar (`/admin/calendar`):**
   - Same visual calendar partial, but route name is `admin.calendar`.
   - Shows all system reservations (reserved/rescheduled).
   - Admin context (within admin layout).

3. **College calendar (`/college/calendar`):**
   - Same shared partial; route name `college.calendar`.
   - Shows:
     - Reservations created by the college staff member, plus
     - Reservations for facilities owned by that college.

4. **Org calendar (`/org/calendar`):**
   - Read-only calendar showing all active reservations. `/org/bookings` is the signed-in user's reservation list.

---

### 3.6 Notifications

**Owner:** All authenticated roles.

**Functional Requirements:**

- Each substantive event produces one or more in‑app notifications:
  - `form_pending`: request submitted.
  - `form_approved`: request approved.
  - `form_disapproved`: request disapproved.
  - `booking_created`: reservation created from request.
  - `booking_rescheduled`: reservation changed by GSU.
  - `booking_preempted`: reservation preempted by a direct booking.
  - `booking_cancelled`: reservation cancelled by GSU.
  - `form_pending_admin`: new request submitted (to all admins).
  - `booking_rescheduled_admin` / `booking_cancelled_admin`: admin‑side alerts.

- Users can view all notifications, see unread vs read, and mark individual ones as read.

---

## 4. Security & Access Rules (Functional)

- Only authenticated users may access dashboards or modify data.
- Role middleware enforces:
  - `/admin/*`: admins only.
  - `/college/*`: college_staff only.
  - `/org/*`: org_staff only.
- College and org users can only:
  - View and modify their own requests and reservations.
  - College facility edits are constrained to the user's college.
- Public users have no access to staff editing or management capabilities. The homepage tracker is read-only for status; an approved guest facility request may expose only its applicable signed PDF and payment-receipt actions.

---

## 5. Data Normalization Rules

- On migration/boot:
  - `bookings.status = 'booked'` → `reserved`.
  - `form_submissions.status = 'booked'` → `reserved`.
- Auto‑completion:
  - `reserved` bookings with `end_time` earlier than now become `completed`.
- Overlapping checks treat `reserved`, `rescheduled`, and legacy `booked` reservations as active. Requests are rejected when `existing_start < requested_end && existing_end > requested_start`; exact back-to-back boundaries do not overlap.

---

## 6. Non‑Functional Points (High‑level)

- Monochrome admin UI with consistent black/white styling across dashboards.
- Shared calendar partial for public and all roles to minimize UI drift.
- Document generation uses official DOCX templates and produces PDF output for GSU forms.

This FDD should be the primary reference for how the system is expected to behave from the user perspective, especially around reservations, statuses (`reserved` vs `completed`), and role‑based actions.
