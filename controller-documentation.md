# Controller Method Documentation — UA Facility Management System

**Validated:** October 26, 2026. Routes and behavior below refer to the current Laravel web application; `routes/web.php` is authoritative when route details change.

---

## 1. `AuthController`
**File:** `app/Http/Controllers/AuthController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `showLogin()` | GET | `/login` | If already authenticated, redirects to the role-appropriate dashboard (`admin.dashboard`, `college.dashboard`, `org.dashboard`, or `home`). Otherwise renders the login view. |
| `login(Request $request)` | POST | `/login` | Rate-limited login (max 5 attempts per IP per minute). Validates email + password, attempts authentication via `Auth::attempt`. On success: clears rate-limit cache, regenerates session, redirects by role. On failure: increments attempt counter, returns error. |
| `logout(Request $request)` | GET or POST | `/logout` | Logs out the user, invalidates the session, regenerates the CSRF token, and redirects to the home page. |

---

## 2. `Admin\DashboardController`
**File:** `app/Http/Controllers/Admin/DashboardController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/admin/dashboard` | Renders the admin dashboard view (`admin.dashboard`). |

---

## 3. `Admin\FacilityController`
**File:** `app/Http/Controllers/Admin/FacilityController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/admin/facilities` | Lists all facilities ordered by name, paginated (10 per page). |
| `create()` | GET | `/admin/facilities/create` | Renders the facility creation form. |
| `store(StoreFacilityRequest $request)` | POST | `/admin/facilities` | Validates and creates a new facility. Fields: `name`, `location`, `owner_type` (gsu/college/org), `owner_college`, `description`, `is_active`, `availability_status` (available/unavailable/maintenance). Redirects with success message. |
| `edit(Facility $facility)` | GET | `/admin/facilities/{facility}/edit` | Renders the edit form for a specific facility. |
| `update(StoreFacilityRequest $request, Facility $facility)` | PUT | `/admin/facilities/{facility}` | Validates and updates a facility. Protects core seeded facilities from having their `owner_type`/`owner_college` changed. Prevents admin from changing `availability_status` on college-owned facilities. |
| `deactivate(Facility $facility)` | PATCH | `/admin/facilities/{facility}/deactivate` | Sets `is_active=false`, preserving the facility and its reservation history. |
| `activate(Facility $facility)` | PATCH | `/admin/facilities/{facility}/activate` | Sets `is_active=true` to restore a previously deactivated facility. |

The facilities list hides edit and activation controls until **Manage facilities** is selected. Actions use icons with accessible labels.

---

## 4. `Admin\BookingController`
**File:** `app/Http/Controllers/Admin/BookingController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/admin/bookings` | Lists bookings with requester and facilities, paginated (20 per page), with status, facility, month, and search filters. |
| `createDirect()` | GET | `/admin/bookings/create-direct` | Renders the GSU high-priority reservation form. |
| `storeDirect(Request $request)` | POST | `/admin/bookings/store-direct` | Requires the admin's explicit account last name and creates a high-priority reservation through `BookingService`; conflicting reservations are moved back to pending and affected requesters are notified. |
| `edit(Booking $booking)` | GET | `/admin/bookings/{booking}/edit` | Renders the booking edit form, loading all active facilities for the dropdown. |
| `calendar(Request $request)` | GET | `/admin/calendar` | Month calendar of `reserved` and `rescheduled` bookings. Accepts month navigation and an optional selected day; includes monthly facility counts. |
| `overview(Request $request)` | GET | `/admin/overview` | Monthly facility-use chart data based on `reserved`, `rescheduled`, and `completed` bookings. |
| `update(Request $request, Booking $booking)` | PUT | `/admin/bookings/{booking}` | Admin reschedules/modifies an existing booking. Validates facility, date, start/end time, purpose, reason, and optional equipment quantities (monobloc, table, fan, rostrum, flag, sound, LED). Merges equipment into `additional_details` JSON, sets status to `rescheduled`. Sends a notification to the requester and to other admin accounts for audit. |
| `cancel(Request $request, Booking $booking)` | POST | `/admin/bookings/{booking}/cancel` | Cancels a booking. Requires a `reason`. Sets status to `cancelled`, stores cancel reason/by/timestamp in `additional_details`. Notifies the requester. |

---

## 5. `Admin\UserController`
**File:** `app/Http/Controllers/Admin/UserController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/admin/users` | Lists all users ordered by name, paginated (20 per page). |
| `create()` | GET | `/admin/users/create` | Renders the user creation form. |
| `store(StoreUserRequest $request)` | POST | `/admin/users` | Creates a role account with validated profile fields, including the separately stored last name. |
| `edit(User $user)` | GET | `/admin/users/{user}/edit` | Renders the account edit form. |
| `update(Request $request, User $user)` | PUT | `/admin/users/{user}` | Updates an account's profile and role-specific unit fields. |
| `deactivate(User $user)` | PATCH | `/admin/users/{user}/deactivate` | Deactivates the account without deleting its history; prevents self-deactivation and delegates cancellation of eligible future work to the account-deactivation service. |

---

## 6. `Admin\FormSubmissionController`
**File:** `app/Http/Controllers/Admin/FormSubmissionController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/admin/forms/facilities` | Lists and filters `facilities_utilization` submissions by status, facility, activity month, or control number; paginated 15 per page and sorted by workflow status, newest first within each status. |
| `show(FormSubmission $submission)` | GET | `/admin/forms/facilities/{submission}` | Shows a single facilities utilization submission. Aborts 404 if type mismatch. Loads every requested facility, falling back to legacy `facility_id` payloads. |
| `approve(FormSubmission $submission)` | POST | `/admin/forms/facilities/{submission}/approve` | Creates the reservation for an eligible request after transactional availability/overlap checks and sets the submission status to `reserved`. For guest requests, payment tracking is separate: an initially `not_required` payment state becomes `pending_payment` after approval. |
| `disapprove(Request $request, FormSubmission $submission)` | POST | `/admin/forms/facilities/{submission}/disapprove` | Requires a disapproval reason, stores it on the request, and notifies the requester. |
| `verifyPayment(FormSubmission $submission)` | POST | `/admin/forms/facilities/{submission}/verify-payment` | Marks an uploaded guest receipt as verified. |
| `setReservation(FormSubmission $submission)` | POST | `/admin/forms/facilities/{submission}/set-reservation` | Supports the separate reservation action for eligible legacy approved/payment-pending submissions, rechecking availability and conflicts transactionally. |

---

## 7. Retired controller reference
The former `Admin\FormReviewController` reference is retired: it is not part of the current controller set or route table. Facility-request review is handled by `Admin\FormSubmissionController` above.

---

## 8. `Admin\FacilitiesFormPdfController`
**File:** `app/Http/Controllers/Admin/FacilitiesFormPdfController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `generate(FormSubmission $submission)` | GET | `/admin/forms/facilities/{submission}/pdf` | Generates a PDF (via DOCX template + LibreOffice) for an approved `facilities_utilization` submission. Maps the submission's JSON payload (control number, requester, date, time, purpose, venue, equipment quantities) onto a DOCX template using `PhpWord\TemplateProcessor`. Checks venue against a predefined facility-to-checkbox-key map to mark the correct venue checkbox. Falls back to returning the DOCX if LibreOffice PDF conversion fails. |
| `convertAndDownload(string $docxPath, string $baseName)` | — (private) | — | Converts a filled DOCX file to PDF using LibreOffice headless. Returns the PDF download response, or falls back to DOCX if conversion fails. Cleans up temp files. |

---

## 9. `GsuFormController`
**File:** `app/Http/Controllers/GsuFormController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `showFacilities()` | GET | `/forms/facilities` | Renders the Facilities and Utilization Form UI (for manual/admin filling). |
| `downloadFacilities(Request $request)` | POST | `/forms/facilities/download` | Validates form inputs (date, requester, contact, activity date/time, purpose). Maps selected venue checkboxes to labels. Loads the DOCX template, fills all placeholders (control number, requester info, venues, equipment quantities, signature blocks), saves to temp DOCX, and converts to PDF for download. Auto-generates a control number if not provided. |
| `showRepair()` | GET | `/forms/repair` | Renders the Repair and Maintenance Form UI. |
| `downloadRepair(Request $request)` | POST | `/forms/repair/download` | Validates repair form inputs (date, requester, department, contact, location, problem description). Fills the Repair and Maintenance DOCX template with checkboxes for submission method (in-person/phone) and service type (maintenance/repair/other). Auto-generates control number. Converts to PDF for download. |
| `convertAndDownload(string $docxPath, string $baseName)` | — (private) | — | Shared DOCX-to-PDF conversion via LibreOffice headless. Falls back to DOCX download if conversion fails. |
| `generateControlNumber(string $formType)` | — (private) | — | Generates a sequential control number in format `GSU-YYYYMMDD-XXXX`. Looks up the last control number for today from the `FormControl` model and increments. Stores the new control number in the database. |

---

## 10. `PublicCalendarController`
**File:** `app/Http/Controllers/PublicCalendarController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/` (home) | Public-facing month calendar. Accepts optional `?month=YYYY-MM`. Shows all approved/rescheduled bookings grouped by day. No authentication required. |
| `calendarData(Request $request, ReservationCalendarService $calendar)` | GET | `/calendar/data` (`calendar.data`) | Rate-limited shared JSON month data for reservation pickers and portal calendars. Event and requester-unit details are included only for staff roles. |

---

## 11. `NotificationController`
**File:** `app/Http/Controllers/NotificationController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/notifications` | Lists all notifications for the authenticated user, ordered newest first, paginated (20 per page). |
| `markAsRead(Notification $notification)` | POST | `/notifications/{notification}/read` | Marks a notification as read. Aborts 403 if the notification doesn't belong to the current user. |

---

## 12. `College\DashboardController`
**File:** `app/Http/Controllers/College/DashboardController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/college/dashboard` | Renders the college staff dashboard view (`college.dashboard`). |

---

## 13. `College\FacilityController`
**File:** `app/Http/Controllers/College/FacilityController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/college/facilities` | Lists facilities owned by the logged-in user's college (`owner_type=college`, `owner_college` matches user's `college_name`), paginated (10 per page). |
| `create()` | GET | `/college/facilities/create` | Renders the facility creation form, pre-filling the college name. |
| `store(Request $request)` | POST | `/college/facilities` | Creates a new college-owned facility. Validates name, location, description, is_active, availability_status. Forces `owner_type=college`, sets `is_active=false` and `availability_status=unavailable` (new college facilities require GSU verification before activation). |
| `edit(Facility $facility)` | GET | `/college/facilities/{facility}/edit` | Renders the edit form. Aborts 403 if the facility doesn't belong to the user's college. |
| `update(Request $request, Facility $facility)` | PUT | `/college/facilities/{facility}` | Updates a college-owned facility. Aborts 403 if ownership doesn't match. |
| `deactivate(Facility $facility)` | PATCH | `/college/facilities/{facility}/deactivate` | Deactivates a college-owned facility without deleting it. Aborts 403 if ownership doesn't match. |

---

## 14. `College\BookingController`
**File:** `app/Http/Controllers/College/BookingController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/college/bookings` | Lists the signed-in user's bookings with the available status/date filters. |
| `calendar(Request $request)` | GET | `/college/calendar` | Read-only month calendar of all `reserved` and `rescheduled` bookings, with monthly facility counts. |

---

## 15. `College\FormController`
**File:** `app/Http/Controllers/College/FormController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `createFacilities()` | GET | `/college/requests/facilities` | Renders the Facilities Utilization request form. Loads all active GSU- and college-owned facilities for selection, with the signed-in user's college facilities also provided as a hint. |
| `storeFacilities(Request $request)` | POST | `/college/requests/facilities` | Submits a facilities utilization request. The form request uses the signed-in account's separately recorded last name as `requester_last_name`; it is not inferred from the full name. Validates: `date_activity` (at least 30 days in the future), optional continuous `date_end` (up to 14 days total), `start_time`, `end_time`, one or more `facility_ids`, `purpose`, and optional equipment quantities. Rejects overlapping active reservations for any selected facility/date while allowing adjacent times. Creates a pending `FormSubmission` and notifies the requester and admins. |
| `indexFacilities()` | GET | `/college/requests` | Lists the current college staff user's own facilities utilization submissions, paginated (10 per page). |
| `showFacilities(FormSubmission $submission)` | GET | `/college/requests/facilities/{submission}` | Shows a single request's details. Aborts 404 if the submission type doesn't match or it doesn't belong to the current user. Loads the associated facility from the payload. |

---

## 16. `Org\DashboardController`
**File:** `app/Http/Controllers/Org/DashboardController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/org/dashboard` | Renders the organization staff dashboard view (`org.dashboard`). |

---

## 17. `Org\BookingController`
**File:** `app/Http/Controllers/Org/BookingController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/org/bookings` | Lists the signed-in user's bookings with the available status/date filters. |
| `calendar(Request $request)` | GET | `/org/calendar` | Read-only month calendar of all `reserved` and `rescheduled` bookings. |

---

## 18. `Org\FormController`
**File:** `app/Http/Controllers/Org/FormController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `createFacilities()` | GET | `/org/requests/facilities` | Renders the Facilities Utilization request form for org staff. Loads all active facilities, including college-owned facilities, for selection. |
| `storeFacilities(Request $request)` | POST | `/org/requests/facilities` | Submits a facilities utilization request. Uses the account's separately recorded tracking last name (not a surname parsed from the full name), the same 30-day minimum notice, 14-day maximum range, multi-facility selection, and blocking overlap validation as College requests. Builds a JSON payload with `requester_unit` set to the user's `organization_name`. Creates a pending `FormSubmission` with `requester_type=org` and notifies the requester and admins. |
| `indexFacilities()` | GET | `/org/requests/facilities/index` | Lists the current org staff user's own facilities utilization submissions, paginated (10 per page). |

---

## 19. `TicketLookupController`
**File:** `app/Http/Controllers/TicketLookupController.php`

Public controller used by the homepage tracker. The status lookup does not require authentication and is limited to 20 requests per minute.

| Method | HTTP | Route | Description |
|---|---|---|---|
| `show(Request $request)` | GET | `/track/ticket` (`ticket.lookup`) | Accepts `type` (`FM`, `BK`, or `RM`), `code`, and `last_name`. New FM records use the separately stored `requester_last_name` or the account's explicit `last_name`; guest bookings resolve the linked FM submission's tracking name. Maintenance tickets use the requester's explicit account last name. Explicit values are compared as whole values after case/whitespace normalization and treating commas/periods as spaces; partial/suffix-only matches are rejected. Records without an explicit value retain legacy matching against the full name. Returns status and relevant details without requester identity. Missing names return `needs_last_name`; unmatched codes or names return the same not-found response. |
| `uploadPayment(Request $request, FormSubmission $submission)` | POST | `/track/ticket/{submission}/payment` (`ticket.payment.upload`) | Signed, rate-limited endpoint for approved guest facility requests only. Validates a JPG/JPEG, PNG, or PDF receipt up to 10 MiB, stores it on the private payment-receipts disk, and changes payment status to `payment_uploaded`. |

For an approved guest facility request, `show` may return a temporary signed PDF URL (30-minute expiry) and, when payment is still due, a temporary signed receipt-upload URL (15-minute expiry). Booking and maintenance lookups return status/details only; none of the status lookup results permit editing the underlying record.

## 23. `PublicFacilityRequestController`
**File:** `app/Http/Controllers/PublicFacilityRequestController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `create()` | GET | `/request-facility` | Renders the public guest request form with active public facilities. No account or sign-in is required. |
| `store(StoreFacilitiesUtilizationRequest $request)` | POST | `/request-facility` | Validates visitor details and facility-request fields, files the request under the shared non-interactive walk-in account, stores the visitor's full name and explicit tracking last name in the submission payload, and emails the tracking code. The receipt is not required at initial submission; payment may be requested after GSU review. |
| `submitted(FormSubmission $submission)` | GET | `/request-facility/submitted/{submission}` | Shows the confirmation only when the submission ID is present in the current visitor session. |

## 24. `PaymentReceiptController`
**File:** `app/Http/Controllers/PaymentReceiptController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `show(FormSubmission $submission)` | GET | `/forms/facilities/{submission}/payment-receipt` | Streams the receipt only to an admin, the authenticated requester, or the public session that submitted the request. |
| `attachment(FormSubmission $submission, int $index)` | GET | `/forms/facilities/{submission}/attachments/{index}` | Streams a supporting upload under the same authorization checks as receipt downloads. |

Downloads are private and non-cacheable. New uploads use the private `payment_receipts` disk; legacy paths are accepted only from their expected storage prefixes.

## 25. `Admin\AuditLogController`
**File:** `app/Http/Controllers/Admin/AuditLogController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index(Request $request)` | GET | `/admin/audit-logs` (`admin.audit-logs.index`) | Admin-only, read-only audit history with search, action/type filters, and pagination. Shows actors, subjects, request metadata, and redacted before/after diffs. |

`AuditObserver` records create, update, and delete events for application models; login/logout events are recorded separately. Sensitive values (including passwords, contact information, submitted payloads, and message/file contents) are redacted.

## Other current controllers

- `Admin\MonthlyRecordsController` serves `/admin/monthly-records` and its chart export route.
- `Admin\SignatoryController` serves administrator CRUD for signatories.
- `College\UserController` and `Org\UserController` (via `ManagesUnitUsers`) manage accounts within their own team; account removal is deactivation, not deletion.
- `PublicCalendarController@index` serves the public calendar at `/`.

---

## 20. `Admin\MaintenanceTicketController`
**File:** `app/Http/Controllers/Admin/MaintenanceTicketController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/admin/maintenance-tickets` | Lists active (`notified`, `pending`, `ongoing`) and completed (`success`, `failed`) maintenance tickets in separate paginated groups. |
| `show(MaintenanceTicket $ticket)` | GET | `/admin/maintenance-tickets/{ticket}` | Shows ticket, requester, facility/location, and college conversation history. |
| `update(Request $request, MaintenanceTicket $ticket)` | PUT | `/admin/maintenance-tickets/{ticket}` | Validates status (`pending`, `ongoing`, `success`, `failed`) and optional admin remarks. Sets or clears `completed_at`; notifies the requester and college team when applicable. |
| `messages(Request $request, MaintenanceTicket $ticket)` | GET | `/admin/maintenance-tickets/{ticket}/messages` | Returns new conversation messages as JSON for an authorized admin on a college ticket. |
| `sendMessage(Request $request, MaintenanceTicket $ticket)` | POST | `/admin/maintenance-tickets/{ticket}/messages` | Adds a validated message, notifies the college team, and is limited to 30 requests per minute. Closed-ticket conversations are read-only. |

## 21. `College\MaintenanceTicketController`
**File:** `app/Http/Controllers/College/MaintenanceTicketController.php`

| Method | HTTP | Route | Description |
|---|---|---|---|
| `index()` | GET | `/college/maintenance-requests` | Lists the requester's college team's tickets, not only tickets created by the current account. |
| `create()` | GET | `/college/maintenance-requests/create` | Displays active facilities and the option to enter an unlisted location. |
| `store(Request $request)` | POST | `/college/maintenance-requests` | Requires the authenticated account's explicit tracking last name, validates the facility or custom place, subject, and issue description, then creates an `RM-…` ticket and notifies the requester and admins. |
| `show(MaintenanceTicket $ticket)` | GET | `/college/maintenance-requests/{ticket}` | Allows any member of the ticket requester's college team to view the ticket and conversation. |
| `messages(Request $request, MaintenanceTicket $ticket)` | GET | `/college/maintenance-requests/{ticket}/messages` | Returns new college-team conversation messages as JSON. |
| `sendMessage(Request $request, MaintenanceTicket $ticket)` | POST | `/college/maintenance-requests/{ticket}/messages` | Adds a message and notifies GSU and other college-team members; limited to 30 requests per minute. Closed-ticket conversations are read-only. |

Organization accounts do not have maintenance-ticket routes or a maintenance-reporting interface.
