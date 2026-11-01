# Need to Fix and Update

Status of the known issues in [`documentation.md` §13](documentation.md#13-known-issues-and-backend-follow-ups), rechecked against the current code on **October 26, 2026**.

**Summary:** All 12 issues fixed. The agreed next steps (2, 4, 5, 9, 10, 11), account deactivation, the control-number display gap, mobile navigation gap, private local receipt uploads, and the signatory dean name mismatches are fixed below.

| # | Issue | Fixed? | Evidence |
|---|---|---|---|
| 1 | Repair (RM) ticket lookup | ✅ Yes | Fixed on Oct 19 and tested: the landing page tracker shows Notified, Pending, Ongoing, Success and Failed. |
| 2 | Dashboard repair counts | ✅ Yes | Admin, College and Org dashboards now count the saved `ongoing`, `success` and `failed` statuses. College counts use the shared college team IDs. |
| 3 | Deleting an account deletes its history | ✅ Yes | Admin and unit-team account actions deactivate accounts instead of deleting them. Inactive accounts cannot sign in; future facility requests and pending/upcoming bookings are marked cancelled, while past/completed activity and all history records remain intact. |
| 4 | Multiple facilities, multi-day requests, and time conflicts | ✅ Yes | College, Org, Guest, and Public forms accept one or more facilities for one day or a continuous date range. The live availability status shows booked time ranges and blocks overlaps in both the UI and server validation. Back-to-back slots and simultaneous reservations at different facilities are allowed. Approval rechecks and reserves all selected facilities for every date atomically. Requests require 30 days' notice; date ranges remain capped at 14 days. |
| 5 | PDF shows only the start date | ✅ Yes | The generated form includes the full date range and checks each selected facility's venue box. |
| 6 | Guests email their payment receipt | ✅ Yes | A receipt is not required at submission. When GSU requests payment after review, guests can upload a JPG/JPEG, PNG, or PDF receipt up to 10 MiB. Receipts and optional supporting uploads use private local storage, generated filenames, MIME/size checks, and authorized download routes; deployment and backup steps are documented. |
| 7 | Landing header on phones | ✅ Yes | Checked at phone widths (320px and 390px): no sideways scroll, and all header links are available in a keyboard-accessible Menu disclosure. |
| 8 | Wrong data ("CCIS Dean") | ✅ Yes | The CEA dean was incorrectly named "CCIS Dean" and the CMG dean was incorrectly named "CBA Dean" in the signatories table. Both corrected in the seeder and via migration `2026_10_24_000001_fix_signatory_dean_names`. |
| 9 | Wrong dashboard and Monthly Records numbers | ✅ Yes | Monthly Records now reads equipment/requester details from each booking's linked submission and builds the venue list from the facilities table. |
| 10 | Approve / Disapprove in one click | ✅ Yes | Both decisions require confirmation. Disapproval requires a reason, which is saved, shown to guests, and included in their notification. |
| 11 | Reserved requests disappear from My requests | ✅ Yes | College and Org request lists no longer exclude reserved submissions; the existing Reserved filter now returns them. |
| — | "Control No." shows the word "Pending" | ✅ Yes | Requests without a control number now display "Not assigned". |

## Where each fix lives

| # | Needs | Files |
|---|---|---|
| 2, 9 | Backend sign-off | `app/Http/Controllers/Admin/DashboardController.php`, `College/DashboardController.php`, `Org/DashboardController.php`, `Admin/MonthlyRecordsController.php`, `resources/views/admin/monthly-records/index.blade.php` |
| 3 | Soft-deactivation and login enforcement | `app/Http/Controllers/Admin/UserController.php`, `app/Http/Controllers/Traits/ManagesUnitUsers.php`, `app/Http/Middleware/EnsureActiveUser.php`, `database/migrations/2026_10_21_000001_add_is_active_to_users_table.php` |
| 4 | Multi-facility, multi-day requests and blocking time availability | `app/Http/Requests/StoreFacilitiesUtilizationRequest.php`, `app/Http/Controllers/Traits/SubmitsFacilitiesForm.php`, `app/Services/BookingService.php`, `resources/views/partials/facility-time-availability.blade.php`, College/Org/Guest/Public request forms, `tests/Feature/MultiFacilityRequestTest.php` |
| 5 | Date range and all selected facilities in PDF | `app/Http/Controllers/Admin/FacilitiesFormPdfController.php` |
| 6 | Backend + local storage | `app/Http/Requests/StoreFacilitiesUtilizationRequest.php`, `app/Http/Controllers/Traits/SubmitsFacilitiesForm.php`, `app/Http/Controllers/PaymentReceiptController.php`, `config/filesystems.php`, `resources/views/public/request-facility.blade.php` |
| 7 (new gap) | UI only | `resources/views/layouts/partials/header.blade.php`, `resources/css/app.css` |
| 8 | Data only | `database/seeders/SignatorySeeder.php`, `database/migrations/2026_10_24_000001_fix_signatory_dean_names.php` |
| 10 | UI + small backend (to save the reason) | `resources/views/admin/forms/facilities_show.blade.php`, `Admin/FormSubmissionController.php` |
| 11 | Backend sign-off (or a UI-only wording fix) | `College/FormController.php`, `Org/FormController.php`, `college|org/requests/facilities_index.blade.php` |
| — | UI only | `resources/views/admin/forms/facilities_index.blade.php` |

**Current status:** the items listed above are recorded as fixed. This file is a dated resolution record, not an active backlog; see [TODO.md](TODO.md) for current maintenance follow-ups.
