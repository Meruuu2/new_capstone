**Need to Be Fixed – McCall Evaluation** Software Quality Evaluation (McCall Criteria) 

**Overall score: 2.7 / 5 (51 / 95)** 

Evaluation date: 24 October 2026 

> **Historical assessment:** This is a point-in-time audit from October 24, 2026, not a live issue list. Some findings may already be fixed or may have changed; verify each against the current source before acting. See [TODO.md](TODO.md) for current follow-ups and [NEED_TO_FIX_AND_UPDATE.md](NEED_TO_FIX_AND_UPDATE.md) for the documented resolution status of the listed fixes.

Need to Be Fixed – McCall Evaluation 

# **Table of Contents** 

|1. Auditability – 2 / 5............................................................................................................................................4|
|---|
|Coding standards checking...............................................................................................................................4|
|Automated tests............................................................................................................................................... 5|
|Audit trail (who did what).................................................................................................................................5|
|Logging..............................................................................................................................................................5|
|2. Accuracy – 2 / 5................................................................................................................................................ 6|
|Booking control.................................................................................................................................................6|
|Report and dashboard figures..........................................................................................................................6|
|Status automation............................................................................................................................................6|
|3. Completeness – 4 / 5........................................................................................................................................8|
|Missing functions..............................................................................................................................................8|
|Partly implemented.......................................................................................................................................... 8|
|Leftover / outdated items.................................................................................................................................8|
|4. Communication Commonality – 2 / 5............................................................................................................. 10|
|Interfaces to other systems............................................................................................................................ 10|
|HTTP methods and URLs.................................................................................................................................10|
|JSON responses and data formats..................................................................................................................10|
|5. Conciseness – 2 / 5.........................................................................................................................................12|
|Duplicated code.............................................................................................................................................. 12|
|Dead code (never routed or used)..................................................................................................................12|
|Stray files and folders in the repository..........................................................................................................12|
|Styling size......................................................................................................................................................13|
|6. Consistency – 3 / 5..........................................................................................................................................14|
|Navigation (must match across portals – seeCLAUDE.md).........................................................................14|
|Naming conventions....................................................................................................................................... 14|
|Code style.......................................................................................................................................................14|
|Dates, times, and fonts................................................................................................................................... 15|
|Documentation............................................................................................................................................... 15|
|7. Operability – 4 / 5........................................................................................................................................... 16|
|Readability (WCAG 2 AA colour contrast – found by axe-core)......................................................................16|
|Keyboard use.................................................................................................................................................. 16|
|Missing conveniences..................................................................................................................................... 16|
|8. Security – 3 / 5................................................................................................................................................ 17|
|Configuration.................................................................................................................................................. 17|



Page 1 of 37 

Need to Be Fixed – McCall Evaluation 

|Authentication................................................................................................................................................ 17|
|---|
|Secrets and dependencies.............................................................................................................................. 17|
|Monitoring...................................................................................................................................................... 18|
|9. Self-Documentation – 3 / 5.............................................................................................................................19|
|Measurements (app/)....................................................................................................................................19|
|Needs to fix.....................................................................................................................................................19|
|10. Simplicity – 3 / 5........................................................................................................................................... 20|
|Complex methods (cyclomatic complexity > 20)............................................................................................20|
|Logic and JavaScript inside views....................................................................................................................20|
|Hard-to-follow behaviour...............................................................................................................................20|
|11. Traceability – 2 / 5........................................................................................................................................22|
|Requirements cannot be referenced..............................................................................................................22|
|Features with no written requirement...........................................................................................................22|
|No requirement → test matrix.......................................................................................................................22|
|Change history................................................................................................................................................ 22|
|12. Training – 3 / 5.............................................................................................................................................. 24|
|Needs to fix.....................................................................................................................................................24|
|13. Controllability – 3 / 5....................................................................................................................................25|
|Business rules cannot be changed without editing code................................................................................25|
|Lists admins cannot manage...........................................................................................................................25|
|Execution control............................................................................................................................................25|
|14. Decomposability – 3 / 5................................................................................................................................ 27|
|Test isolation...................................................................................................................................................27|
|Coupling between modules............................................................................................................................ 27|
|15. Error Tolerance – 2 / 5.................................................................................................................................. 28|
|Pages crash on bad input (tested live)............................................................................................................28|
|No friendly error pages...................................................................................................................................28|
|Actions that can be left half-done or crash.....................................................................................................28|
|16. Execution Efficiency – 2 / 5........................................................................................................................... 30|
|Measured per page (OPcache on, rendered in-process)................................................................................30|
|Repeated and unnecessary queries................................................................................................................30|
|Missing indexes...............................................................................................................................................30|
|Server configuration.......................................................................................................................................31|
|Page weight....................................................................................................................................................31|
|17. Hardware Independence – 3 / 5...................................................................................................................32|
|Server dependencies......................................................................................................................................32|



Page 2 of 37 

Need to Be Fixed – McCall Evaluation 

|Database dependence.................................................................................................................................... 32|
|---|
|File system and platform files......................................................................................................................... 32|
|18. Instrumentation – 2 / 5.................................................................................................................................33|
|The application does not monitor its own work.............................................................................................33|
|Health checks..................................................................................................................................................33|
|Alerts and log handling................................................................................................................................... 33|
|19. Modularity – 3 / 5......................................................................................................................................... 34|
|Mixed responsibilities.....................................................................................................................................34|
|Features are not self-contained......................................................................................................................34|
|Hidden coupling through shared data............................................................................................................34|
|Dead modules.................................................................................................................................................35|
|Other Issues Found During Testing.....................................................................................................................36|



Page 3 of 37 

Need to Be Fixed – McCall Evaluation 

Items found while evaluating the system against McCall's Software Quality criteria. See rule.md for how this file is maintained. 

|**No.**|**Criterion**|**Score**|**Evaluated**|
|---|---|---|---|
|1|Auditability|2 / 5 (Fair)|2026-10-24|
|2|Accuracy|2 / 5 (Fair)|2026-10-24|
|3|Completeness|4 / 5 (Very Good)|2026-10-24|
|4|Communication Commonality|2 / 5 (Fair)|2026-10-24|
|5|Conciseness|2 / 5 (Fair)|2026-10-24|
|6|Consistency|3 / 5 (Good)|2026-10-24|
|7|Operability|4 / 5 (Very Good)|2026-10-24|
|8|Security|3 / 5 (Good)|2026-10-24|
|9|Self-Documentation|3 / 5 (Good)|2026-10-24|
|10|Simplicity|3 / 5 (Good)|2026-10-24|
|11|Traceability|2 / 5 (Fair)|2026-10-24|
|12|Training|3 / 5 (Good)|2026-10-24|
|13|Controllability|3 / 5 (Good)|2026-10-24|
|14|Decomposability|3 / 5 (Good)|2026-10-24|
|15|Error Tolerance|2 / 5 (Fair)|2026-10-24|
|16|Execution Efficiency|2 / 5 (Fair)|2026-10-24|
|17|Hardware Independence|3 / 5 (Good)|2026-10-24|
|18|Instrumentation|2 / 5 (Fair)|2026-10-24|
|19|Modularity|3 / 5 (Good)|2026-10-24|
||**Overall average**|**2.7 / 5 (51 / 95)**||



## **1. Auditability – 2 / 5** 

**Criterion:** the ease with which conformance to standards can be checked. 

**Summary:** The system keeps timestamps, requires a reason when a request is disapproved, and keeps a full history for maintenance tickets. However, coding-standard conformance cannot currently be checked automatically, and several admin actions do not record _who_ performed them. 

### **Coding standards checking** 

- Laravel Pint (code style checker) cannot run: the installed version (v1.32.1) requires PHP 8.3, but the environment uses PHP 8.2.12. Either upgrade PHP to 8.3 or pin Pint to a PHP 8.2–compatible version. 

- No pint.json file defines the project's coding standard. 

Page 4 of 37 

Need to Be Fixed – McCall Evaluation 

- No lint script in composer.json or package.json to run style checks with one command. 

- No static analysis tool (e.g. PHPStan / Larastan) to detect type and logic errors. 

- No CI pipeline (e.g. GitHub Actions) to run lint and tests automatically on every push. 

### **Automated tests** 

- Only 7 test files cover 65 application PHP files; most controllers and portal pages have no tests. 

- 1 test fails (supporting documents accept jpg png and pdf) because the PHP **GD extension** is disabled. Enable extension=gd in php.ini. 

### **Audit trail (who did what)** 

- **Approve request** – the approving admin is not recorded. Add approved_by. 

- **Disapprove request** – the disapproving admin is not recorded. Add disapproved_by. 

- **Verify payment** – the verifying admin and time are not recorded. Add payment_verified_by and payment_verified_at. 

- **Deactivate user account** – the admin who deactivated the account is not recorded. Add deactivated_by and deactivated_at. 

- **User account changes** (create, edit, role change) – not logged. 

- Approval and disapproval times are stored as display text ("Oct 24, 2026 03:15 PM") inside the JSON payload, not as real date columns, so they are hard to sort, filter, or verify. Move them to dateTime columns. 

### **Logging** 

- Logins and failed login attempts are not logged. 

- Only 2 Log::error calls exist in the whole application; important admin actions write nothing to the log. 

- Log is a single file with no rotation (LOG_STACK=single). Switch to the daily channel so logs are kept per day and old ones are removed automatically. 

Page 5 of 37 

Need to Be Fixed – McCall Evaluation 

## **2. Accuracy – 2 / 5** 

**Criterion:** the precision of computations and control. 

**Summary:** Core scheduling logic is correct (overlap detection, transaction-locked approval, 30-day notice, 14day limit, Asia/Manila timezone). However, rescheduling can double-book a facility, booking codes can collide, and the Monthly Records report shows wrong numbers for past months and rental fees. 

### **Booking control** 

- **Rescheduling can double-book a facility.** Admin\BookingController@update skips the conflict check and does not move clashing bookings back to pending, so two active bookings can hold the same facility and time (confirmed by test). Either block the reschedule on conflict or preempt the clashing booking the same way createDirectBooking does. 

- **Booking codes can collide.** Codes are BKG-<YmdHis>-<facility id>, so two bookings for the same facility in the same second crash with Duplicate entry ... bookings_booking_code_unique (confirmed by test). Add a random or sequential suffix, or generate the code from the booking ID. 

- **Direct bookings and reschedules accept past dates.** Validation in storeDirect and update is only required|date. Add after_or_equal:today. 

- **Direct booking ignores facility availability.** BookingService::createDirectBooking does not check isAvailable(), so a facility under maintenance can be booked. 

- **Direct booking is not atomic.** The booking, preemption of other bookings, and the FormSubmission record are saved without a transaction; if one step fails, the others stay saved. Wrap it in DB::transaction. 

- **Wrong error message for long events.** A request longer than 14 days returns "The end date of the event is before its start date." (BookingService.php:67). Return a separate message such as "Events can span at most 14 days." 

### **Report and dashboard figures** 

- **Monthly Records loses past bookings.** AppServiceProvider changes finished bookings from reserved to completed on every request, but MonthlyRecordsController (table and 12-month trend) counts only reserved. Once an event ends it disappears from the report. Include completed and rescheduled statuses. 

- **Rental fee total is always ₱0.** MonthlyRecordsController sums $facility->rental_fee, but the facilities table has no rental_fee column. Add the column (decimal) or read fees from the venue rates source. 

- **Dashboard "Total" is incomplete.** Admin\DashboardController totals only 5 statuses and leaves out cancelled, completed, and rescheduled requests. Rename it or count all statuses. 

### **Status automation** 

- Auto-status updates in AppServiceProvider::boot() swallow errors silently (catch (\Exception $e) { // ignore }). Log the error instead. 

- These updates run 2 extra UPDATE queries on every page load. Move them to a scheduled command (php artisan schedule). 

- Rescheduled bookings are never auto-marked completed (only reserved ones are) 

Page 6 of 37 

Need to Be Fixed – McCall Evaluation 

## **3. Completeness – 4 / 5** 

**Criterion:** the degree to which full implementation of the required functions has been achieved. 

**Summary:** Checked against the Functional Design Document (fdd.md). Nearly all required functions exist and work: facility CRUD for Admin and College, multi-facility and multi-day requests, cancel-while-pending, request and reservation filters, approval and direct high-priority bookings, reschedule and cancel with reasons, shared calendars with counts and upcoming lists, all FDD notification types, in-app notifications with mark-as-read, and DOCX → PDF form generation. A few functions are missing or only partly built. 

### **Missing functions** 

- **No way to reactivate a deactivated account.** Admin, College, and Org user pages only offer _Deactivate_ ; a deactivated account shows a locked "Deactivated" badge and there is no route, button, or is_active field on the edit form to restore it. Add a PATCH /…/users/{user}/reactivate action for each portal. 

- **Org maintenance messaging:** Historical audit item; organization maintenance-ticket access and its routes were intentionally removed. Repair conversations remain available to College staff and GSU.

- **Rental fees are not stored anywhere.** The public Venue Rates page and Monthly Records show fees, but the facilities table has no fee column, so the system cannot compute or report rental income (see Accuracy). 

### **Partly implemented** 

- **`booking_created` notification does not reach admins.** FDD §3.3.1 says a new reservation notifies the requester _and admins_ ; NotificationService::notifyBookingCreated only calls notifyUser for the requester. Also call notifyAdmins. 

- **Facility analytics are incomplete.** Admin\BookingController@overview (FDD: "monthly reservations per facility"): 

   - counts only the _first_ facility of a multi-facility booking, so other facilities are under-counted; 

   - uses a hard-coded list of 11 facility names, so any facility added later is lumped into "OTHERS"; 

      - excludes completed bookings, so past months show nothing. 

Build the series from the facilities table and count every linked facility. 

- **Approve and "Set reservation" overlap.** FDD §3.2.3 describes pending → approved → reserved, but _Approve_ already creates the reservation directly, so the approved status and the set-reservation action are effectively unused. Either update the FDD to the one-step flow or restore the two-step flow. 

### **Leftover / outdated items** 

- app/Http/Controllers/Guest/FormController.php and resources/views/guest/* remain although the guest portal is retired (all /guest/* links redirect to /request-facility and guest accounts can no longer sign in). Remove them or document why they are kept. 

- TODO.md still lists unchecked tasks (status-normalization smoke tests, welcome page centering). Mark finished items or remove the file. 

Page 7 of 37 

Need to Be Fixed – McCall Evaluation 

- Maintenance / repair tickets are a major feature but are not described in fdd.md at all. Add a section for them. 

Page 8 of 37 

Need to Be Fixed – McCall Evaluation 

## **4. Communication Commonality – 2 / 5** 

**Criterion:** the degree to which standard interfaces and protocols are understood and used. 

**Summary:** Inside the browser the system uses standard web protocols well: HTTP with session login and CSRF tokens, correct status codes (201, 403, 404, 422), UTF-8 (utf8mb4), a /healthz check, and PDF/DOCX documents. However, there is no API for other systems, the Android app points to a route that does not exist, there is no email channel, and HTTP methods, URLs, JSON responses, and date formats are not used consistently. 

### **Interfaces to other systems** 

- **No API.** There is no routes/api.php; all 99 routes are browser pages. Other systems (mobile app, university portal, reports) cannot exchange data with the system. Add a versioned JSON API (/api/v1/...) with token authentication (Laravel Sanctum) for at least facilities, availability, and reservations. 

- **The Android app points to a route that does not exist.** legacy/android/ua-fms-org/app/build.gradle.kts loads http://127.0.0.1:8000/fms-portal-entry in a WebView. There is no fms-portal-entry route, and 127.0.0.1 on a phone means the phone itself, not the server. Point it to the real server URL and an existing route (e.g. /login?portal=org), or remove the app. 

- **No email notifications.** MAIL_MAILER=log and the code has no Mailables or mail notifications, so every notification is in-app only. Guests without accounts and staff who are not logged in never hear about approvals, disapprovals, or cancellations. Configure SMTP and send the key notifications by email too. 

- **No standard data exports.** Monthly Records can only be printed from the browser (window.print()). Add CSV/Excel export for reports and an iCalendar (.ics) feed for reservations so they can be opened in Google Calendar or Outlook. 

### **HTTP methods and URLs** 

- **Booking update uses `POST` instead of `PUT`/`PATCH`.** POST admin/bookings/{booking} updates a booking, while facilities, users, and maintenance tickets correctly use PUT. Change it to PUT (with @method('PUT') in the form). 

- **Logout accepts `GET`.** GET|POST logout lets any link or image on another site sign a user out. Allow POST only. 

- **College and Org portals use different URLs for the same page.** The request list is college/requests but org/requests/facilities/index. This historical URL consistency item remains separate from the intentional removal of Org maintenance access.

- **Maintenance URLs do not match their names.** College uses .../maintenance-requests while route names and Admin use maintenance-tickets. Organization maintenance routes were removed, so this historical mismatch applies only to the College route.

### **JSON responses and data formats** 

- **No common JSON response shape.** Each endpoint returns a different structure: notifications return {success: true}, ticket lookup returns {status: ...}, ticket messages return {messages, closed}. The key message is a _text_ on error (422) but an _object_ on success (201) in HandlesTicketMessages. Use one envelope, e.g. {data, message, errors}. 

Page 9 of 37 

Need to Be Fixed – McCall Evaluation 

- **JSON dates are display text, not ISO 8601.** Ticket lookup and messages return dates like "Mon, Oct 24, 2026" and "Oct 24, 3:15 PM", which other programs cannot parse reliably. Return ISO 8601 (2026-10-24T15:15:00+08:00) and format on the page. 

- Approval and disapproval times in the request payload are stored as display text (M d, Y h:i A) instead of ISO dates (see Auditability). 

Page 10 of 37 

Need to Be Fixed – McCall Evaluation 

## **5. Conciseness – 2 / 5** 

**Criterion:** the compactness of the program in terms of lines of code. 

**Summary:** The backend is reasonably compact (5,967 lines in app/), and some sharing already exists (traits such as SubmitsFacilitiesForm, ManagesUnitUsers, HandlesTicketMessages, one shared calendar partial, thin College/Org PDF wrappers). However, the Blade views (14,349 lines) repeat the same pages per portal, about 1,950 lines belong to the retired guest portal or are never routed, and the repository root holds several stray files and large leftover folders. 

### **Duplicated code** 

- **The facility request form exists 4 times (3,348 lines).** college/requests/facilities_create (858), org/requests/facilities_create (842, **92% identical** to College), guest/requests/facilities_create (798), and public/request-facility (850, 64% identical). Make one shared form partial with small perportal options. 

- **College and Org pages are near copies.** Historical measurements included maintenance pages which are no longer available to Org users; compare only currently available pages before acting on this audit item.

- **College and Org controllers repeat logic.** BookingController (121 / 109 lines, only 35 differing lines), FormController (235 / 193), MaintenanceTicketController (123 / 84). Move the shared parts into traits or a base controller, as already done for users and ticket messages. 

- **The equipment array is built twice.** Admin\BookingController@update builds the 7-item equipment array inline even though BookingService::buildEquipmentArray() does the same. Call the service method. 

- **Facility names are hard-coded.** Admin\BookingController@overview keeps its own list of 11 facility names instead of reading the facilities table. 

### **Dead code (never routed or used)** 

- **Retired guest portal: 212 lines of controllers + 1,563 lines of views.** app/Http/Controllers/Guest/{Booking,Dashboard,Form}Controller.php and resources/views/guest/* (7 views). No route reaches them; /guest/* redirects to /request-facility. Delete them. 

- **Unrouted controllers (171 lines):** Admin/SignatoryController.php (71), College/FacilitiesFormPdfController.php (37), Org/FacilitiesFormPdfController.php (35), College/FacilityRequestController.php (28). No route or class references them. Delete them or add the missing routes. 

### **Stray files and folders in the repository** 

- clipboard.txt (23 KB): a pasted copy of an old public/calendar.blade.php. Delete. 

- setup-views.php (13 KB) and run-migrations.php: one-time scaffolding scripts (run-migrations.php only calls php artisan migrate). Delete. 

- laravel_app and public/Calendar.blade: empty 0-byte files. Delete. 

- ua-fms-main/: an old project skeleton (.env.example, .gitignore, etc.) duplicating root files. Delete. 

Page 11 of 37 

Need to Be Fixed – McCall Evaluation 

- legacy/ (93 MB) and ref/ (51 MB) are tracked in git, including Android Gradle cache/lock files (legacy/android/ua-fms-org/.gradle/*) that change on every build. Move them out of the main repository or add .gradle/ and build outputs to .gitignore. 

### **Styling size** 

- resources/css/app.css is a single 4,582-line file with page-specific sections (75 section banners). Split it into per-area files (admin.css, forms.css, calendar.css, …) imported by app.css, and remove rules for the deleted guest pages. 

- 6 views still contain their own <style> blocks and there are 85 inline style="..." attributes. Move them into the stylesheet. 

Page 12 of 37 

Need to Be Fixed – McCall Evaluation 

## **6. Consistency – 3 / 5** 

**Criterion:** the use of uniform design and documentation techniques throughout the software development project. 

**Summary:** The portal UI is strongly consistent: Admin, College, and Org share one portal-shell and sidebar partial, so the header, sidebar, hero banner, stat cards, and icons look the same (checked with screenshots); one calendar partial serves all calendars; flash messages use only status and error. However, the sidebar links differ between portals, naming and validation styles are mixed, dates, times, and fonts vary between pages, and the documentation is duplicated or outdated in places. 

**Navigation (must match across portals – see CLAUDE.md)** 

- **Org maintenance access:** Intentionally removed. Organization accounts should not have maintenance-ticket routes or reporting pages.

- **College portal has no facilities link.** College facility management (college/facilities) is reachable only from a dashboard card. Add a "My facilities" sidebar link. 

- **Same links, different order and names.** College: "Reservation calendar", then "Reservations". Org: "My Reservations", then "Reservation calendar". Use the same label and order in both. 

- **Mixed capitalization in sidebar labels.** "All Requests", "Create Direct Reservation", "My Reservations" (Title Case) next to "All facilities", "Monthly records", "My requests" (sentence case). Use sentence case everywhere. 

- **Active-link highlighting differs.** College uses wildcards (college.bookings.*) but Org uses exact names (org.bookings.index, org.requests.facilities.index), so on Org detail pages no sidebar item is highlighted. Use wildcards in all portals. 

### **Naming conventions** 

- **View file names mix three styles:** snake_case (facilities_create, create_direct), kebab-case (booking-picker-script, request-detail), and single words. Pick kebab-case (Laravel convention) and rename. 

- **Request URLs differ by portal** (college/requests vs org/requests/facilities/index) and maintenance URLs use maintenance-requests while route names use maintenance-tickets (see Communication Commonality). 

- **Two admin calendar views:** admin/calendar.blade.php (unused) and admin/calendar/index.blade.php (used). Delete the unused one. 

### **Code style** 

- **Validation is done two ways:** 20 inline $request->validate() calls vs 4 Form Request classes. Move validation into Form Request classes, starting with storeDirect and update in Admin\BookingController, which repeat the same rules. 

- **Docblocks are uneven:** 53 docblocks for 110 public controller methods; 15 controller files have none. Agree on a rule (e.g. every public controller method has a one-line docblock). 

- Code formatting cannot be checked automatically because Pint does not run (see Auditability). 

Page 13 of 37 

Need to Be Fixed – McCall Evaluation 

### **Dates, times, and fonts** 

- **Times shown in two formats.** The Admin Reservations list shows 24-hour time (08:00 – 10:00, resources/views/admin/bookings/index.blade.php:147) while every other page shows 12-hour time (8:00 AM). Use g:i A everywhere. 

- **The same date is written several ways:** M d, Y (Oct 04, 2026), M j, Y (Oct 4, 2026), D, M j, Y, l, F j, Y, and M d, Y h:i A. Define 2–3 standard formats (short date, long date, date + time) in one helper and use them everywhere. 

- **Six font families are loaded:** Inter + Merriweather (portals, app.css), Google Sans Flex (layouts/app.blade.php), Bricolage Grotesque (welcome.blade.php), EB Garamond + Source Sans (printable forms). Use the portal fonts on public pages too; keep the form fonts only if they match the official printed form. 

### **Documentation** 

- **Two README files differ only in capitalization** (README.md and ReadMe.md are both tracked in git). Windows can only keep one, so git always shows README.md as modified. Both are the default Laravel README with nothing about this project. Keep one README.md describing the system. 

- **`README_Overview.md` is outdated:** titled "Laravel Form Filler – UA GSU Forms", it describes only the early DOCX form filler. 

- **Overlapping documents:** documentation.md, controller-documentation.md, modeldocumentation.md, views-and-routes-documentation.md, IMPLEMENTATION_SUMMARY.md, QUICKSTART.md, TESTING_GUIDE.md, and two security guides cover overlapping ground with no index. Move them into a docs/ folder with one index page. 

- **Stale route names in docs:** views-and-routes-documentation.md mentions admin.forms.facilities.set-booking (now set-reservation) and college.forms.store (does not exist). 

- **Two "to fix" lists:** NEED_TO_FIX_AND_UPDATE.md (older) and NEED_TO_BE_FIX.md (this file). Merge the one open item (#8 "CCIS Dean" data) into this file and retire the older list. 

Page 14 of 37 

Need to Be Fixed – McCall Evaluation 

## **7. Operability – 4 / 5** 

**Criterion:** the ease of operation of a program. 

**Summary:** Tested end to end in Chrome with Playwright: a College user submitted a request, an Admin approved it, and the College user found the reservation and notification. The flow is smooth: required fields are flagged before submitting, the form shows live availability and helpful hints, a "Request submitted" ticket appears with the ticket code and Print / View / Done buttons, Approve and Disapprove ask for confirmation, success messages explain what happened, every form field has a label, and the mobile menu opens correctly at 390px. The main problems are low colour contrast on almost every page and a few missing conveniences. 

### **Readability (WCAG 2 AA colour contrast – found by axe-core)** 

- **Sidebar section titles** ("OVERVIEW", "REQUESTS", "TEAM") are #94a3b8 on white = **2.56:1** (needs 4.5:1). Shown in every portal. Darken to #64748b or darker. 

- **Equipment quantity boxes** on the request form show their value in #cbd5e1 on #f8fafc = **1.41:1** , nearly invisible. Use the normal text colour. 

- **Request summary card** placeholder ("None selected") is #7b7f8a on #0f172a = 4.45:1. Lighten slightly. 

- **Sidebar role label** ("College Staff · CCIS") is #64748b on #f1f5f9 = 4.34:1. 

- **Dashboard step hints** ("Not approved by GSU") are #94a3b8 on #f8fafc = 2.45:1. 

- **Home page:** facility card numbers ("01") are #afafaf on #fafafa = 2.1:1, and the empty-calendar message is #808080 on white = 3.94:1. 

- Number of low-contrast elements per page: College request form 24, Public request form 9, Admin requests list 6, Admin dashboard 5, Admin request detail 5, Home 4. 

### **Keyboard use** 

- **Home page facility slider cannot be scrolled with the keyboard** (.ua-fac-track, axe rule scrollableregion-focusable). Add tabindex="0" and an aria-label, or add previous/next buttons. 

### **Missing conveniences** 

- **No "Forgot password".** There is no password reset route or link; a user who forgets their password must ask an admin. Add Laravel's password reset (requires email, see Communication Commonality). 

- **Login lockout gives no wait time.** After repeated failures the message is only "Too many login attempts. Please try again later." Say how many seconds or minutes to wait. 

- **Two notifications for one approval.** Approving a request sends the requester both "Facilities utilization request approved" and "booking created". Combine them into one. 

- **Confirmation dialogs look different.** Approve and Disapprove use the browser's plain confirm() box, while Deactivate user uses a styled modal. Use the styled modal everywhere. 

- **Slow page loads** (0.5–2 s per page) because OPcache is off (see Other Issues). 

- Org staff cannot reach maintenance pages from the sidebar (see Consistency). 

Page 15 of 37 

Need to Be Fixed – McCall Evaluation 

## **8. Security – 3 / 5** 

**Criterion:** the availability of mechanisms that control or protect programs and data. 

**Summary:** Access control is solid. Tested live in Chrome: every role is blocked from other portals (403), anonymous users are sent to login, another college or an org user gets 404 on someone else's request, a college user cannot approve (403), and a POST without a CSRF token is rejected (419). Passwords are hashed with bcrypt (min 8 characters), the session is regenerated on login, deactivated accounts cannot sign in, college/org staff can only create accounts with their own role, receipts are stored on a private disk with type and size checks, public endpoints are rate-limited, raw SQL uses bound parameters, .env is git-ignored, and composer audit finds no vulnerable PHP packages. However, no security headers are sent, debug mode exposes stack traces, the login lockout is IP-only, an old app key is committed to git, and npm has 12 known vulnerabilities. 

### **Configuration** 

- **Debug mode exposes internals.** APP_DEBUG=true, and an invalid value such as /admin/overview? month=bad-value crashes with a 500 and shows the full debug page (file paths, code, stack trace). Set APP_DEBUG=false in production (already in DEPLOYMENT_GUIDE.md) and validate the month parameter so it cannot crash. 

- **No security headers.** Pages send no Content-Security-Policy, X-Frame-Options, X-Content-TypeOptions, Referrer-Policy, Strict-Transport-Security, or Permissions-Policy, so the site can be framed (clickjacking). The nginx config in DEPLOYMENT_GUIDE.md only adds nosniff for /storage/, and SECURITY_QUICKSTART.md still lists headers as an unchecked task. Add a middleware (or nginx add_header lines for all locations) that sets them. 

- **PHP version is disclosed.** Responses include X-Powered-By: PHP/8.2.12. Set expose_php = Off in php.ini. 

- **Cookies are not marked `secure`.** laravel-session and XSRF-TOKEN have secure=false, and SESSION_SECURE_COOKIE is not set anywhere or mentioned in the deployment guide. Set SESSION_SECURE_COOKIE=true in production (HTTPS), and consider SESSION_ENCRYPT=true. 

- **Local database uses `root` with no password** (DB_USERNAME=root, DB_PASSWORD=). Use the dedicated facility_app user (already defined earlier in .env) with a strong password, and remove the duplicate DB_* block. 

### **Authentication** 

- **Login lockout is keyed by IP address only** (AuthController::login, key login:<ip>). On campus Wi-Fi many users share one public IP, so 5 wrong passwords from anyone lock everyone out; an attacker who changes IP can keep guessing one account. Use Laravel's RateLimiter keyed by **email + IP** . 

- **Logout accepts `GET`.** GET /logout signs the user out with no CSRF token, so any link or image on another site can log users out. Allow POST only. 

- No password strength rule beyond 8 characters, and no "Forgot password" flow (see Operability). 

### **Secrets and dependencies** 

- **An app key is committed to git.** dump/notes.txt contains an APP_KEY value, and dump/launcher.py contains default secrets (UA-FMS-2025, UA-FMS-ACCESS-2025). The key does not match the current .env, but the repository has a GitHub remote. Remove these files from git, and if that key was ever used on a server, generate a new one there (php artisan key:generate). 

Page 16 of 37 

Need to Be Fixed – McCall Evaluation 

- **npm reports 12 vulnerabilities (2 critical, 8 high, 2 moderate)** in build tools: vite, axios, concurrently, rollup, postcss, shell-quote, form-data, and others. They are development/build dependencies, not served to users, but run npm audit fix (and upgrade vite / axios). 

- **Unused security packages.** web-token/jwt-core (abandoned) and phpseclib/phpseclib are not used anywhere; defuse/php-encryption and hashids/hashids are used only by app/Helpers/SecurityHelper.php, which nothing calls. security-config.js in the root is also unused. Remove them to shrink the attack surface. 

### **Monitoring** 

- Security events (logins, failed logins, role changes, deactivations) are not logged (see Auditability). 

Page 17 of 37 

Need to Be Fixed – McCall Evaluation 

## **9. Self-Documentation – 3 / 5** 

**Criterion:** the degree to which the source code provides meaningful documentation. 

**Summary:** Names are generally clear (BookingService::hasOverlap, AccountDeactivationService, ManagesUnitUsers), many comments explain _why_ rather than _what_ (e.g. "Each portal card on the landing page only lets its own accounts in", "Day 1 keeps the usual code; later days get a -D2 suffix"), 91% of function parameters have type hints, 23 of 29 migrations are commented, and there is no commented-out code or leftover TODO. However, the core service classes have almost no documentation, the JSON payload structure is undocumented, status values are bare strings, and one comment describes a check that does not exist. 

### **Measurements (app/)** 

- Comment lines: 471 of 4,884 non-blank lines = **9.6%** . By folder: Providers 22.4%, Middleware 18.5%, Controllers 9.2%, Models 4.4%, **Services 2.5%** , **Form Requests 1.4%** . 

- Return type declared on **139 of 281 functions (49%)** ; parameter types on 214 of 235 (91%). 

- Blade views with a header comment explaining the page: **18 of 83** . 

### **Needs to fix** 

- **Misleading comment.** Admin\BookingController@update (lines 268–269) says "we still prevent obviously insane overlaps with itself", but the method performs no overlap check at all (see Accuracy). Fix the code or correct the comment. 

- **Core services are undocumented.** BookingService has 0 docblocks for 5 public methods and NotificationService 0 for 12. In particular, createFromSubmission() returns either a Booking or a string error message, a convention explained nowhere. Add docblocks (purpose, parameters, return values, side effects such as notifications), or return a result object / throw an exception instead of a string. 

- **The request `payload` structure is undocumented.** FormSubmission.payload holds about 36 different keys (date_activity, date_end, time_range, facility_ids, control_no, approved_datetime, disapproval_reason, …) but the model only says 'payload', // JSON string, which is also wrong (it is cast to an array). Same for Booking.additional_details. Document the keys in a model docblock, or move the stable ones into real columns. 

- **Status values are bare strings.** There are no constants or enums: 'reserved' appears 84 times, 'pending' 74, 'approved' 44, 'pending_payment' 41, etc., across controllers and views. A typo silently creates a new status. Add PHP enums (e.g. App\Enums\SubmissionStatus, BookingStatus, TicketStatus) and use them everywhere. 

- **Return types missing on about half the functions.** Add return types (: View, : RedirectResponse, : JsonResponse, : bool) so each method's result is clear from its signature. 

- **Silent error handling hides intent.** AppServiceProvider::boot() catches exceptions with only // ignore / // Silent fail. Explain why it is safe to ignore, or log the error. 

- **Few view header comments.** Only 18 of 83 Blade views say which route/controller renders them and what variables they expect. Add a short {{-- Route: … | Variables: … --}} header (the convention already used in some views). 

Page 18 of 37 

Need to Be Fixed – McCall Evaluation 

## **10. Simplicity – 3 / 5** 

**Criterion:** the degree to which the program can be understood without difficulty. 

**Summary:** Most backend code is simple: 270 methods average **14.9 lines** and a cyclomatic complexity of **3.2** , nesting is shallow (only 2 methods reach depth 4), views never query the database directly, and the project follows the standard Laravel layout (routes → controllers → services/models → Blade). The difficulty is concentrated in a few very large methods, in Blade views that carry a third of their size as PHP logic and inline JavaScript, and in a status model split across two overlapping fields. 

### **Complex methods (cyclomatic complexity > 20)** 

- Admin\FacilitiesFormPdfController::generate – complexity **49** , 156 lines, ~50 template fill / fallback steps in one method. Split into small methods per form section (requester, schedule, venues, equipment, signatories). 

- BookingService::createFromSubmission – complexity **36** , 141 lines, nesting depth 4. It validates the payload, checks notice, availability, and conflicts, builds day slots, creates bookings, and sends notifications. Split into validateSubmission(), buildSlots(), assertNoConflicts(), createBookings(). 

- Admin\MonthlyRecordsController::index – complexity **34** , 97 lines; query, record formatting, venue summary, fee totals, and 12-month trend all in one action. Move the calculations into a MonthlyReportService. 

- TicketLookupController::show – complexity **23** , 67 lines, handles two ticket types (FM and RM) in one method. Split per type. 

- Admin\BookingController::update – complexity **21** , 82 lines; validation, equipment merging, saving, and notifications inline. Use a Form Request and BookingService (which already has buildEquipmentArray). 

- 14 methods are longer than 50 lines; 12 have complexity above 10. 

### **Logic and JavaScript inside views** 

- **33% of all Blade lines are logic:** 1,158 lines inside @php blocks and 3,518 lines of inline <script> in 14,331 view lines, while resources/js/ holds only 5 lines. 

- Each facility request form carries ~450–500 lines of inline JavaScript (College 495, Org 487, Public 455, Guest 423), mostly the same code. Move it into one module under resources/js/ (e.g. requestform.js) loaded through Vite. 

- admin/bookings/partials/booking-picker-script.blade.php is a 286-line Blade file that is almost entirely JavaScript (280 lines). Move it to resources/js/. 

- Move @php data preparation (largest: College request form 49 lines, Org 41) into the controller or a view composer. 

### **Hard-to-follow behaviour** 

- **Two overlapping status fields.** A request has status (pending, pending_payment, approved, reserved, disapproved, cancelled, …) **and** payment_status (not_required, pending_payment, payment_uploaded, payment_verified). The value pending_payment exists in both. Document the combined state flow as a diagram, or merge payment into a single status machine. 

Page 19 of 37 

Need to Be Fixed – McCall Evaluation 

- **Hidden work on every request.** AppServiceProvider::boot() runs database updates (legacy booked → reserved, past reserved → completed) on every page load. A reader of a controller cannot see why statuses change. Move this to a scheduled command (see Accuracy). 

- **Errors returned as strings.** createFromSubmission() returns Booking|string, so every caller must check is_string($result). Throw a domain exception or return a result object. 

Page 20 of 37 

Need to Be Fixed – McCall Evaluation 

## **11. Traceability – 2 / 5** 

**Criterion:** the ability to trace a design representation or actual program component back to requirements. 

**Summary:** Some tracing is possible by hand: fdd.md is organised by numbered sections (3.1 Facilities, 3.2 Requests, 3.3 Reservations…), controller-documentation.md and views-and-routes-documentation.md map routes to controllers and views, NEED_TO_FIX_AND_UPDATE.md has a "Where each fix lives" table linking each issue to its files, and test names state the rule they check (e.g. overlapping_reservations_block_submission_but_adjacent_times_remain_available). However, requirements have no IDs, nothing in code, tests, or commits refers back to a requirement, several major features have no written requirement at all, and the git history is a single commit. 

### **Requirements cannot be referenced** 

- **No requirement IDs.** fdd.md and documentation.md contain 0 identifiers such as FR-01. Number every requirement (e.g. FR-3.2.4 Cancel request while pending) so code, tests, and commits can point to it. 

- **No links from code or tests to requirements.** No controller, service, or test mentions the FDD or a requirement number. Add the requirement ID to test names or docblocks (e.g. /** @covers FR-3.2.4 */) and to related controller methods. 

### **Features with no written requirement** 

The following are built but not described in fdd.md (0 mentions), so they cannot be traced to an agreed need: 

- Public / walk-in guest facility requests (/request-facility) and ticket tracking (/track/ticket). 

- Payment receipts and payment verification (payment_status, PaymentReceiptController). 

- Venue rates page (/venue-rates) and rental fees. 

- Account deactivation (AccountDeactivationService). 

- Monthly Records report (/admin/monthly-records). 

- Repair & maintenance tickets, including status flow (pending → notified → ongoing → success/failed) and messaging (only named in passing). 

- The legacy Android app and desktop launchers in legacy/. 

Add an FDD section for each, or a short requirement note in documentation.md. 

### **No requirement → test matrix** 

Tests exist only for parts of FDD §3.2 (requests: overlap, 30-day notice, multi-day), §3.3.1 (reservation from request), account deactivation, and receipts. There are **no tests** for: §3.1 Facilities management, §3.3.2 direct bookings, §3.3.5 reschedule, §3.3.6 cancel, §3.4 Calendars, §3.5 Notifications, §4 role access rules, §5 autocompletion. Add a traceability table (requirement → code files → tests) to fdd.md, and fill the gaps with tests. 

### **Change history** 

- **Git has a single commit** (c587da5 first commit), so no change can be traced to the requirement or issue that caused it. Commit in small steps using Conventional Commits that mention the requirement or issue (e.g. fix(booking): block reschedule conflicts (FR-3.3.5)). 

Page 21 of 37 

Need to Be Fixed – McCall Evaluation 

- **Only one dated changelog** (CHANGELOG_2026-10-13.md), which describes the Guest Portal that has since been retired. Keep a single CHANGELOG.md updated with each change. 

Page 22 of 37 

Need to Be Fixed – McCall Evaluation 

## **12. Training – 3 / 5** 

**Criterion:** the degree to which the software assists in enabling new users to apply the system. 

**Summary:** The interface teaches as it goes: request forms are split into numbered steps (01 Schedule, 02 Venues, …) with hints such as "Requests need at least 30 days' notice" and a live "Request summary"; dashboards explain the request flow as Step 1 Pending → Step 2 Approved → Step 3 Reserved with one-line descriptions; /guest-access explains the process to outsiders; empty lists show messages such as "No facility requests yet"; and the views contain 68 help texts, 83 placeholders, and 25 tooltips. However, there is no help page, FAQ, or user manual, every .md document is written for developers, and nothing guides a first-time user. 

### **Needs to fix** 

- **No user manual.** All documentation (QUICKSTART.md, TESTING_GUIDE.md, documentation.md, …) is for developers (server setup, test accounts, code). Write a short user guide per role (Admin, College staff, Org staff, Guest) with screenshots: how to request a facility, track it, cancel it, file a repair ticket (College staff only), approve or disapprove requests, and create direct reservations.

- **No in-app help.** There is no /help route, FAQ, or "?" link anywhere in the portals. Add a Help item to the shared sidebar (all portals) linking to the user guide and an FAQ (e.g. "Why can't I pick a date within 30 days?", "What does Pending payment mean?"). 

- **No first-login guidance.** New staff land on the dashboard with no welcome or tour. Show a dismissible "Getting started" card on first login with the 3–4 main actions for that role. 

- **Status meanings are not explained in one place.** Request statuses (pending, pending_payment, approved, reserved, rescheduled, disapproved, cancelled, completed) and ticket statuses (pending, notified, ongoing, success, failed) appear as badges, but only the dashboard explains three of them. Add a status legend (tooltip on each badge or a legend on list pages). 

- **Admin pages have less guidance than staff pages.** Direct reservations preempt existing bookings, and rescheduling skips conflict checks (see Accuracy), but the admin forms do not warn about these effects beforehand. Add a short explanation next to these actions. 

- **Training material for the retired guest portal** (CHANGELOG_2026-10-13.md, GUEST_SYSTEM_IMPLEMENTATION.md) describes screens that no longer exist and would mislead new users or staff. Update or archive it. 

Page 23 of 37 

Need to Be Fixed – McCall Evaluation 

## **13. Controllability – 3 / 5** 

**Criterion:** the system can be easily controlled and manipulated in terms of execution, program structure, and design. 

**Summary:** Admins control the main operational data through the UI: facilities (create, edit, delete, availability available / unavailable / maintenance), user accounts (create, edit, deactivate), request decisions, direct highpriority reservations that preempt others, reschedules, and cancellations. Infrastructure is controlled through .env (database, mail, cache, session, queue) and standard Laravel tools (php artisan down for maintenance mode, a payment-receipts:clean-orphans --days --dry-run command). However, business rules are hardcoded, several lists cannot be managed by admins, and background tasks are not scheduled. 

### **Business rules cannot be changed without editing code** 

- **Hard-coded values, some in more than one place:** 

   - 30-day notice: StoreFacilitiesUtilizationRequest and BookingService::createFromSubmission (addDays(30)). 

   - 14-day maximum event length: StoreFacilitiesUtilizationRequest (> 13) and BookingService (> 13). 

   - 10 MB upload limit (max:10240, twice) and rate limits (throttle:5,1, 20,1, 30,1) in routes/web.php. 

Create config/fms.php (e.g. notice_days, max_event_days, max_upload_kb) read from .env, and use it everywhere. 

- **No admin settings page.** There is no screen to change these rules, office details, or which statuses count as active. Add a simple Settings page (or at least the config file above). 

### **Lists admins cannot manage** 

- **Equipment types are hard-coded.** The 7 items (monobloc chairs, tables, fans, rostrum, flag, sound, LED wall) are written out by hand in about 30 places. An Equipment model exists but the code never uses it. Store equipment in the database and give admins a page to manage it (name, quantity available). 

- **Signatories cannot be managed.** Admin\SignatoryController exists but has no route, so the "noted by" signatories can only be changed in the database (this is why the "CCIS Dean" data error is still open). Add routes and a page. 

- **Facility list in analytics is hard-coded** (11 names in Admin\BookingController@overview), so new facilities are not controllable there (see Completeness). 

- **Deactivated accounts cannot be reactivated** (see Completeness). 

### **Execution control** 

- **No scheduler.** routes/console.php defines no Schedule:: entries. The receipt cleanup command must be run by hand, and auto-completing past bookings runs on _every page load_ inside AppServiceProvider::boot() where it cannot be turned off, timed, or monitored. Move it into a scheduled command (e.g. hourly) and schedule payment-receipts:clean-orphans daily. 

- **Queue is configured but unused.** QUEUE_CONNECTION=database, but notifications and PDF conversion (LibreOffice, up to ~17 s in tests) run inside the web request. Queue PDF generation and future email notifications so they can be retried and monitored. 

Page 24 of 37 

Need to Be Fixed – McCall Evaluation 

Page 25 of 37 

Need to Be Fixed – McCall Evaluation 

## **14. Decomposability – 3 / 5** 

**Criterion:** the software is built from a series of modules and can be tested independently. 

**Summary:** The code is split into recognisable modules: per-portal controllers (Admin, College, Org), services (BookingService, NotificationService, AccountDeactivationService), reusable traits (SubmitsFacilitiesForm, ManagesUnitUsers, HandlesTicketMessages, GroupsBookingsByDay), Form Requests, middleware (role, EnsureActiveUser), and shared Blade partials. BookingService receives NotificationService through its constructor, and AccountDeactivationService and the booking rules already have their own feature tests. However, most modules cannot be tested in isolation: there is only one model factory, tests run against the developer's real database, there are no unit tests, and some modules depend directly on external programs. 

### **Test isolation** 

- **Tests use the real development database.** phpunit.xml sets DB_CONNECTION=mysql but no DB_DATABASE, so tests run inside ua_antique_fms (wrapped in transactions). When MySQL was stopped, 8 of 23 tests failed. Add a separate test database (<env name="DB_DATABASE" value="ua_antique_fms_test"/>) or SQLite in memory, and use RefreshDatabase. 

- **Only one factory.** database/factories/ contains just UserFactory, although 9 models (Booking, Facility, FormSubmission, MaintenanceTicket, …) declare HasFactory. Tests build records by hand with ::create([...]) and long payload arrays. Add factories for each model. 

- **No unit tests.** tests/Unit contains only the placeholder ExampleTest (true is true). Pure logic such as overlap checks, slot building, equipment arrays, and ticket-code parsing can be unit-tested without HTTP or a database once split out (see Simplicity). 

- **Tests depend on installed programs.** The PDF test calls the real LibreOffice (soffice, ~17 s) and optionally pdftotext; one receipt test needs the PHP GD extension. Wrap document conversion in a small DocumentConverter class that tests can replace with a fake. 

### **Coupling between modules** 

- **Service locator instead of injection in 3 places:** app(AccountDeactivationService::class) in Admin\UserController and ManagesUnitUsers, and app(NotificationService::class) in SubmitsFacilitiesForm. Inject them through constructors or method parameters so tests can pass fakes. 

- **No interfaces.** Services are concrete classes; nothing can be swapped (e.g. an email or SMS notifier, a different PDF engine). Add interfaces where alternatives are expected (Notifier, DocumentConverter). 

- **Services depend on global state.** BookingService uses now(), Carbon::, and DB:: 17 times, so timebased rules (30-day notice, auto-complete) are hard to test. Pass the date in or use Carbon::setTestNow() in tests. 

- **Controllers contain logic that cannot be tested on its own** (monthly report calculations, overview counts, booking update, PDF generation – see Simplicity). Move it into services so each piece can be tested alone. 

- **The PDF controller is called directly from a test** (app(FacilitiesFormPdfController::class)>generate(...)), which shows the document logic lives in a controller rather than a reusable service. 

Page 26 of 37 

Need to Be Fixed – McCall Evaluation 

## **15. Error Tolerance – 2 / 5** 

**Criterion:** the damage that occurs when the program encounters an error. 

**Summary:** Some failures are handled well: approving a request reserves every facility and day inside one database transaction with row locks, so a conflict leaves nothing half-saved; if LibreOffice fails, the PDF download falls back to the filled DOCX; missing records return 404; validation errors return the user to the form with messages; and the College, Org, and Public request forms disable the submit button after one click. However, simple bad input crashes several pages, there are no friendly error pages, and some multi-step actions can be left half-done. 

### **Pages crash on bad input (tested live)** 

**7 of 22** requests with invalid URL parameters returned **500 Server Error** (with APP_DEBUG=true the visitor sees the full debug page): 

|**URL**|**Error**|
|---|---|
|/?month=abcand/?month=99999-01(**public home page, no login needed**)|InvalidFormatException|
|/admin/calendar?month=abc|InvalidFormatException|
|/admin/overview?month=abc|InvalidFormatException|
|/admin/monthly-records?month=abc&year=xyz|TypeError|
|/college/calendar?month=abc|InvalidFormatException|
|/org/calendar?month=abc|InvalidFormatException|



Cause: Carbon::createFromFormat('Y-m', $month) and Carbon::create($year, $month, 1) are called on raw query input (GroupsBookingsByDay, Admin\BookingController, PublicCalendarController, MonthlyRecordsController). Validate the parameter first ('month' => 'nullable|date_format:Y-m') and fall back to the current month when it is invalid. 

### **No friendly error pages** 

- resources/views/errors/ does not exist and bootstrap/app.php leaves withExceptions() empty. Users get Laravel's plain default pages for 403, 404, 419 (session expired), and 500. Add branded errors/403, 404, 419, 500, and 503 views with a link back to the dashboard, and an explanation for 419 ("Your session expired, please try again"). 

- **When the database is down, every page fails.** Earlier in testing MySQL was stopped and every page returned 500 showing the raw SQLSTATE[HY000] [2002] message. Show a maintenance-style 500/503 page instead, and never expose SQL errors (APP_DEBUG=false). 

### **Actions that can be left half-done or crash** 

- **Direct reservation is not atomic.** BookingService::createDirectBooking moves clashing bookings to pending, sends notifications, creates the booking, and creates a FormSubmission with no transaction. If a later step fails, earlier changes stay (e.g. other people's reservations already pushed back). Wrap it in DB::transaction. 

- **Direct reservation form allows double-submit.** admin/bookings/create_direct.blade.php does not disable its submit button. A double-click sends two requests in the same second, which hits the 

Page 27 of 37 

Need to Be Fixed – McCall Evaluation 

duplicate booking-code crash (Duplicate entry 'BKG-…', see Accuracy). Disable the button on submit and make booking codes unique. 

- **Errors are swallowed silently.** AppServiceProvider::boot() catches every exception from the autostatus updates with // ignore, so failures are invisible; TicketLookupController also catches broadly. Log the exception (report($e)) before continuing. 

- **Business errors are returned as plain strings** from BookingService::createFromSubmission(); a caller that forgets is_string() would treat an error message as a booking (see Simplicity). 

Page 28 of 37 

Need to Be Fixed – McCall Evaluation 

## **16. Execution Efficiency – 2 / 5** 

**Criterion:** the run-time performance of the program. 

**Summary:** Front-end assets are built and minified by Vite (CSS 250 KB, JS 35 KB), and lists are paginated. With OPcache on, most pages render in 0.1–0.35 s. However, OPcache is off in the actual environment (0.5–2 s per page), every page repeats the same queries, two UPDATE statements run on every request, key columns have no index, and large images and libraries are loaded. Measurements below were taken with an **empty** database (0 bookings, 0 requests), so real data will make them worse. 

### **Measured per page (OPcache on, rendered in-process)** 

|**Page**|**Queries**|**Time**|
|---|---|---|
|/(public home)|7 (2 UPDATE)|138 ms|
|/admin/dashboard|24|185 ms|
|/admin/users|22|346 ms|
|/admin/monthly-records|29|179 ms|
|/admin/calendar|29|**1,011 ms**|
|/college/dashboard|26|557 ms|
|/college/requests/facilities|21|359 ms|



### **Repeated and unnecessary queries** 

- **Unread-notification count runs 7–9 times per page.** AppServiceProvider registers View::composer('*', …), which runs for _every_ view and partial rendered, each time counting the user's unread notifications. Run it once per request (cache the value, or attach the composer only to the layout/notification-bell view). 

- **Two `UPDATE` queries on every request** , including public pages and assets served through Laravel: booked → reserved and past reserved → completed in AppServiceProvider::boot(). Move them to a scheduled command (see Controllability). 

- **Monthly Records runs one query per month** (12 queries for the trend chart). Use one GROUP BY query. 

- **Admin calendar runs one count per facility** (11 queries) and took ~1 s even with no bookings. Use one grouped query. 

- **Dashboards count each status separately** (Admin\DashboardController: 5 request counts + 5 ticket counts). Use one GROUP BY status query per table. 

### **Missing indexes** 

- bookings has no index on start_time, end_time, or status, yet every overlap check, calendar, and report filters on them. Add index(['status', 'start_time', 'end_time']). 

- form_submissions has no index on type or status, used by every request list and dashboard. Add index(['type', 'status']). 

Page 29 of 37 

Need to Be Fixed – McCall Evaluation 

- 10 queries filter on JSON fields (JSON_EXTRACT(payload, '$.control_no'), '$.date_activity') in Admin\FormSubmissionController, College\FormController, Org\FormController; these cannot use indexes. Move control_no and date_activity into real indexed columns. 

### **Server configuration** 

- **OPcache is disabled** in php.ini: pages take 0.5–2 s instead of ~0.07 s (measured). Enable it. 

- **Laravel caches are not built:** bootstrap/cache holds only packages.php and services.php. In production run php artisan config:cache, route:cache, and view:cache. 

- **Cache and sessions use the database** (CACHE_STORE=database, SESSION_DRIVER=database), adding queries to every request. Use file or Redis. 

- **PDF generation runs inside the web request** (LibreOffice, up to ~17 s in tests). Queue it (see Controllability). 

### **Page weight** 

- **Facility photos are large:** 10 images over 200 KB, 2.2 MB in total (e.g. busalian-hall.jpg 658 KB, grand-stand.jpg 657 KB). Compress to ~150 KB and serve WebP. 

- **Admin dashboard loads Plotly (~3.5 MB) and Chart.js** from CDNs. Use one chart library. 

- **Three separate Google Fonts stylesheets** block rendering (~0.3–0.5 s) and six font families are used (see Consistency). Combine or self-host. 

- app.css is a single 250 KB file loaded on every page, including rules for unrelated pages (see Conciseness). 

Page 30 of 37 

Need to Be Fixed – McCall Evaluation 

## **17. Hardware Independence – 3 / 5** 

**Criterion:** the degree to which the software is decoupled from the hardware on which it operates. 

**Summary:** Users only need a web browser: the portals work on desktop and phone (all pages tested at 1366px and 390px; the mobile menu works), and nothing needs to be installed on the user's device. On the server side, Laravel/PHP runs on Windows and Linux, the code has no hard-coded drive letters or OS paths, temporary files use sys_get_temp_dir(), uploads go through Laravel storage disks, and shell arguments are escaped. However, document generation depends on an external program found by a fixed name, the SQL is tied to MySQL, required PHP extensions are not declared, and the repository contains Windows-only and device-specific parts. 

### **Server dependencies** 

- **PDF generation depends on `soffice` being on the PATH.** ConvertsToPdf and Admin\FacilitiesFormPdfController run the fixed command soffice --headless … through exec(). On many Linux servers the command is libreoffice, under PHP-FPM the www-data user often has no writable HOME (LibreOffice then fails), and on Windows it works only if LibreOffice's folder is on the PATH. Make the binary path configurable (FMS_SOFFICE_PATH in .env), pass -env:UserInstallation=file:///tmp/lo-profile, and document the requirement for each OS (only Ubuntu is mentioned, in README_Overview.md). 

- **The conversion code exists twice** (Traits/ConvertsToPdf.php and Admin\FacilitiesFormPdfController::convertAndDownload). Keep one implementation. 

- **Required PHP extensions are not declared.** composer.json lists no ext-* requirements, although the system needs zip (PhpWord templates), mbstring, fileinfo (upload checks), and gd (tests – currently missing on this machine). Add them to require so composer install warns on servers that lack them. 

### **Database dependence** 

- **MySQL-specific SQL.** 10 queries use JSON_EXTRACT(payload, …) in Admin\FormSubmissionController, College\FormController, and Org\FormController. These do not run on SQLite or PostgreSQL, which also prevents fast in-memory tests. Use Laravel's portable JSON syntax (where('payload->control_no', …)) or real columns (see Execution Efficiency). 

### **File system and platform files** 

- **README name clash on case-insensitive systems.** README.md and ReadMe.md are both tracked; Windows and macOS can only keep one, so git always shows a change (see Consistency). 

- **Windows-only launchers are tracked:** legacy/launchers/dist/*.exe, *.bat build scripts, and Python launchers (legacy/launchers/*.py, dump/launcher.py). They only run on Windows and duplicate what a browser bookmark does. Remove them or move them to a separate repository. 

- **The Android app is tied to one machine:** it loads http://127.0.0.1:8000/..., which on a phone means the phone itself (see Communication Commonality). Read the server URL from configuration. 

Page 31 of 37 

Need to Be Fixed – McCall Evaluation 

## **18. Instrumentation – 2 / 5** 

**Criterion:** the degree to which the program monitors its own operation and identifies errors that do occur. 

**Summary:** Laravel's built-in exception handler writes every unhandled crash to storage/logs/laravel.log (it recorded all errors triggered during this evaluation: 16 database-connection failures, 7 bad-date crashes, 1 duplicate booking code), there are two health endpoints (/up and /healthz), and laravel/pail is installed for viewing logs during development. However, the application itself records almost nothing about what it does, the health checks do not check anything, and nobody is alerted when errors happen. 

### **The application does not monitor its own work** 

- **No application logging.** The only Log:: calls in app/ are two in SecurityHelper, a class nothing uses. Approvals, disapprovals, preemptions, reschedules, cancellations, PDF failures, upload problems, and login failures write nothing to the log. Add Log::info / Log::warning for these events (see Auditability for the who/when fields). 

- **Failures are hidden instead of reported.** AppServiceProvider::boot() swallows exceptions from the automatic status updates, and TicketLookupController catches errors broadly, without logging. Call report($e) in each catch. 

- **PDF conversion failures are silent.** When soffice fails, ConvertsToPdf / FacilitiesFormPdfController quietly return a DOCX instead of a PDF and record neither the exit code nor LibreOffice's output. Log both so administrators know conversion is broken. 

- **No performance monitoring.** Nothing records slow requests or slow queries (e.g. the ~1 s admin calendar, see Execution Efficiency). Add a slow-query listener (DB::whenQueryingForLongerThan) or enable Laravel Pulse/Telescope in staging. 

### **Health checks** 

- **`/healthz` always says "ok".** It returns {"status":"ok","time":…} without checking anything, so it would still say "ok" while the database is down. Make it check the database connection, storage write access, and the LibreOffice binary, and return HTTP 503 if any fail. 

- **`/healthz` and `/up` overlap.** Keep one (Laravel's /up) and add the checks above to it through the DiagnosingHealth event. 

### **Alerts and log handling** 

- **No error alerts.** No error tracker (e.g. Sentry, Flare) or mail/Slack log channel is configured, and mail is set to log. Crashes are only visible to someone who opens the log file. Add an alert channel for error and above. 

- **One log file with no rotation and debug level.** LOG_STACK=single, LOG_LEVEL=debug; laravel.log is already 686 KB after one day of testing, and test-run errors (testing.ERROR) are mixed into the same file. Use the daily channel with a retention period, LOG_LEVEL=warning in production, and a separate log for tests. 

Page 32 of 37 

Need to Be Fixed – McCall Evaluation 

## **19. Modularity – 3 / 5** 

**Criterion:** the functional independence of program components. 

**Summary:** The portals are independent of each other: Admin, College, and Org controllers live in separate namespaces behind their own role: middleware, and no portal imports another portal's code. Shared behaviour is packaged as reusable units: 5 traits (GroupsBookingsByDay used by 5 controllers, SubmitsFacilitiesForm by 4, ManagesUnitUsers and HandlesTicketMessages by 2 each), 3 services, a FacilityPolicy, Form Requests, and shared Blade partials (portal-shell, sidebar, public.calendar, request-detail, ticket-detail). Models are small (largest 130 lines). However, features are spread across layers instead of being self-contained, some classes mix unrelated jobs, and modules share undocumented data. 

### **Mixed responsibilities** 

- **`AppServiceProvider::boot()` does four unrelated jobs:** registers the facility policy, rewrites legacy booked statuses, auto-completes past bookings, and injects the unread-notification count into every view. Split them: keep the policy here, move the status jobs to a scheduled command (see Controllability), and move the view data to a dedicated view composer class for the layout. 

- **Controllers contain business logic** that should live in services: monthly report calculations (MonthlyRecordsController), facility usage counts (BookingController@overview), booking updates (BookingController@update), and document filling (FacilitiesFormPdfController::generate, which a test calls directly). Move each into a service (MonthlyReportService, BookingService::reschedule, FacilityFormDocument), as in Simplicity. 

### **Features are not self-contained** 

- **Notifications are triggered from 8 controllers plus the services.** Adding a channel (e.g. email) or changing a message means editing many files. Fire domain events (RequestApproved, BookingPreempted, BookingRescheduled, …) and send notifications from listeners. 

- **Maintenance tickets, requests, and bookings are each split across three portal controllers and three sets of views** that are near copies (see Conciseness). Group each feature into one module (shared controller logic / service + shared partials) with thin portal wrappers. 

- **PDF conversion exists in two places** (Traits/ConvertsToPdf and Admin\FacilitiesFormPdfController). Make one DocumentConverter service (see Hardware Independence). 

### **Hidden coupling through shared data** 

- **The `payload` JSON is an undocumented contract between modules.** About 36 keys are written by the request forms and read independently by controllers, services, the PDF generator, ticket lookup, and views; renaming a key silently breaks other modules. Document the structure or use a value object / real columns (see Self-Documentation). 

- **Status strings are shared by convention only** (no enums), so every module must spell them the same way (see Self-Documentation). 

- **Views read the logged-in user directly** (auth()->user()->… in 8 views) instead of receiving data from the controller, which ties templates to the auth system. Pass the needed values in. 

Page 33 of 37 

Need to Be Fixed – McCall Evaluation 

### **Dead modules** 

- Unused modules remain in the codebase: the retired guest portal (3 controllers, 7 views), Admin\SignatoryController and its views, College\FacilityRequestController, the College/Org PDF controllers, SecurityHelper, and the unused Equipment model (see Conciseness). Remove them so the module structure reflects what actually runs. 

Page 34 of 37 

Need to Be Fixed – McCall Evaluation 

## **Other Issues Found During Testing** 

- **Users page overflows on phones** (Admin, College, Org portals): the page scrolls sideways by 112– 234px. Fix: add position: relative; to .pk-table-wrap in resources/css/app.css, then run npm run build. 

- **`TEST_ACCOUNTS.md` is outdated:** the accounts listed in section 1b do not exist in the local ua_antique_fms database. 

- **OPcache is disabled** in php.ini, which makes every page load 0.5–2s slower than necessary. 

- **`.env` has duplicate settings:** DB_* and LOG_* values are defined twice; the later values silently override the earlier ones. 

Page 35 of 37 
