# Views & Routes Documentation — UA Facility Management System

**Validated:** October 26, 2026. The route tables describe the current web routes; verify `routes/web.php` or run `php artisan route:list` for the authoritative live list. Retired guest portal templates may remain in the repository, but the current guest workflow is public and does not use guest sign-in.

---

## Table of Contents

1. [Routes](#routes)
2. [Layout Views](#layout-views)
3. [Public Views](#public-views)
4. [Auth Views](#auth-views)
5. [Admin Views](#admin-views)
6. [College Staff Views](#college-staff-views)
7. [Organization Staff Views](#organization-staff-views)
8. [Repair and Maintenance Views](#repair-and-maintenance-views)
9. [GSU PDF Form Views](#gsu-pdf-form-views)
10. [Notification Views](#notification-views)

---

## Routes

**File:** `routes/web.php`

Routes are defined in `routes/web.php`. Staff routes use session authentication and role middleware; public routes are listed separately.

### Public Routes (No Authentication)

| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/` | `home` | `PublicCalendarController@index` | Public facility schedule calendar (homepage) |
| GET | `/calendar/data` | `calendar.data` | `PublicCalendarController@calendarData` | Rate-limited JSON month data shared by reservation pickers and portal calendars; event details are included only for staff roles |
| GET | `/track/ticket` | `ticket.lookup` | `TicketLookupController@show` | Rate-limited public status lookup for FM, BK/BKG and RM records; requires tracking code and submitter last name |
| GET | `/request-facility` | `public.request.create` | `PublicFacilityRequestController@create` | Public guest facility request form; no sign-in |
| POST | `/request-facility` | `public.request.store` | `PublicFacilityRequestController@store` | Validates and stores a guest request with a separately entered tracking last name; rate-limited |
| GET | `/request-facility/submitted/{submission}` | `public.request.submitted` | `PublicFacilityRequestController@submitted` | Shows the confirmation only to the visitor session that submitted the request |
| GET | `/forms/facilities/{submission}/payment-receipt` | `payment-receipts.show` | `PaymentReceiptController@show` | Authorized receipt download for the submitting public session or authorized account |
| GET | `/forms/facilities/{submission}/attachments/{index}` | `payment-attachments.show` | `PaymentReceiptController@attachment` | Authorized download of a supporting attachment |
| POST | `/track/ticket/{submission}/payment` | `ticket.payment.upload` | `TicketLookupController@uploadPayment` | Signed, rate-limited receipt upload for an approved guest facility request |
| GET | `/guest/forms/facilities/{submission}/pdf` | `guest.forms.facilities.pdf` | `Guest\FacilitiesFormPdfController@generate` | Signed PDF download for an approved guest facility request |
| GET | `/login` | `login` | `AuthController@showLogin` | Show login form |
| POST | `/login` | `login.post` | `AuthController@login` | Process login |
| GET, POST | `/logout` | `logout` | `AuthController@logout` | Logout user |
| GET | `/healthz` | — | Closure | JSON health check endpoint |

### Authenticated Routes (All Roles)

| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/notifications` | `notifications.index` | `NotificationController@index` | View all notifications |
| POST | `/notifications/{notification}/read` | `notifications.read` | `NotificationController@markAsRead` | Mark a notification as read |

### Repair and maintenance ticket routes

Staff ticket routes are protected by `auth` and the corresponding role middleware. Public status lookup uses `GET /track/ticket?type=RM&code=...&last_name=...` and does not require sign-in.

| Method | URI | Route name | Access and behavior |
|---|---|---|---|
| GET | `/admin/maintenance-tickets` | `admin.maintenance-tickets.index` | Admin queue, active and completed tickets |
| GET | `/admin/maintenance-tickets/{ticket}` | `admin.maintenance-tickets.show` | Admin ticket detail |
| PUT | `/admin/maintenance-tickets/{ticket}` | `admin.maintenance-tickets.update` | Update status and remarks |
| GET | `/admin/maintenance-tickets/{ticket}/messages` | `admin.maintenance-tickets.messages` | Fetch college-ticket conversation updates |
| POST | `/admin/maintenance-tickets/{ticket}/messages` | `admin.maintenance-tickets.messages.store` | Send an admin reply; throttled |
| GET | `/college/maintenance-requests` | `college.maintenance-tickets.index` | List tickets filed by the college team |
| GET | `/college/maintenance-requests/create` | `college.maintenance-tickets.create` | New college repair request form |
| POST | `/college/maintenance-requests` | `college.maintenance-tickets.store` | Submit a ticket; requires account tracking last name |
| GET | `/college/maintenance-requests/{ticket}` | `college.maintenance-tickets.show` | View a ticket from the user's college team |
| GET | `/college/maintenance-requests/{ticket}/messages` | `college.maintenance-tickets.messages` | Fetch conversation updates |
| POST | `/college/maintenance-requests/{ticket}/messages` | `college.maintenance-tickets.messages.store` | Send a message; throttled |

College tickets are shared within the college and have a conversation with GSU. Organization accounts do not have maintenance-ticket routes or portal access.

### Admin Routes (`auth` + `role:admin`)

#### Dashboard
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | `Admin\DashboardController@index` | Admin dashboard with quick links |

#### Facilities Management
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/facilities` | `admin.facilities.index` | `Admin\FacilityController@index` | List all facilities |
| GET | `/admin/facilities/create` | `admin.facilities.create` | `Admin\FacilityController@create` | Show create facility form |
| POST | `/admin/facilities` | `admin.facilities.store` | `Admin\FacilityController@store` | Store new facility |
| GET | `/admin/facilities/{facility}/edit` | `admin.facilities.edit` | `Admin\FacilityController@edit` | Show edit facility form |
| PUT | `/admin/facilities/{facility}` | `admin.facilities.update` | `Admin\FacilityController@update` | Update facility |
| PATCH | `/admin/facilities/{facility}/deactivate` | `admin.facilities.deactivate` | `Admin\FacilityController@deactivate` | Deactivate facility without deleting it |
| PATCH | `/admin/facilities/{facility}/activate` | `admin.facilities.activate` | `Admin\FacilityController@activate` | Reactivate a facility |

#### GSU PDF Forms
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/forms/facilities` | `forms.facilities.show` | `GsuFormController@showFacilities` | Show facilities utilization form (fillable HTML) |
| POST | `/forms/facilities/download` | `forms.facilities.download` | `GsuFormController@downloadFacilities` | Generate & download PDF |
| GET | `/forms/repair` | `forms.repair.show` | `GsuFormController@showRepair` | Show repair & maintenance form (fillable HTML) |
| POST | `/forms/repair/download` | `forms.repair.download` | `GsuFormController@downloadRepair` | Generate & download PDF |

#### Form Submission Review
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/forms/facilities` | `admin.forms.facilities.index` | `Admin\FormSubmissionController@index` | List facility utilization requests for review |
| GET | `/admin/forms/facilities/{submission}` | `admin.forms.facilities.show` | `Admin\FormSubmissionController@show` | View a single request |
| POST | `/admin/forms/facilities/{submission}/approve` | `admin.forms.facilities.approve` | `Admin\FormSubmissionController@approve` | Approve request |
| POST | `/admin/forms/facilities/{submission}/disapprove` | `admin.forms.facilities.disapprove` | `Admin\FormSubmissionController@disapprove` | Disapprove request |
| POST | `/admin/forms/facilities/{submission}/verify-payment` | `admin.forms.facilities.verify-payment` | `Admin\FormSubmissionController@verifyPayment` | Mark an uploaded guest receipt as verified |
| POST | `/admin/forms/facilities/{submission}/set-reservation` | `admin.forms.facilities.set-reservation` | `Admin\FormSubmissionController@setReservation` | Create a reservation after approval and required payment verification |
| GET | `/admin/forms/facilities/{submission}/pdf` | `admin.forms.facilities.pdf` | `Admin\FacilitiesFormPdfController@generate` | Generate PDF for approved request |

#### User Management
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/users` | `admin.users.index` | `Admin\UserController@index` | List all users |
| GET | `/admin/users/create` | `admin.users.create` | `Admin\UserController@create` | Show create user form |
| POST | `/admin/users` | `admin.users.store` | `Admin\UserController@store` | Store new user |
| GET | `/admin/users/{user}/edit` | `admin.users.edit` | `Admin\UserController@edit` | Edit an account |
| PUT | `/admin/users/{user}` | `admin.users.update` | `Admin\UserController@update` | Update an account |
| PATCH | `/admin/users/{user}/deactivate` | `admin.users.deactivate` | `Admin\UserController@deactivate` | Deactivate an account while preserving its history |
| GET | `/admin/audit-logs` | `admin.audit-logs.index` | `Admin\AuditLogController@index` | Search the read-only audit trail (admins only) |
| GET | `/admin/monthly-records` | `admin.monthly-records.index` | `Admin\MonthlyRecordsController@index` | View monthly utilization records |
| GET | `/admin/monthly-records/export` | `admin.monthly-records.export` | `Admin\MonthlyRecordsController@exportCharts` | Export monthly chart data |

#### Booking / Reservation Management
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/bookings` | `admin.bookings.index` | `Admin\BookingController@index` | List all reservations (reserved / rescheduled / cancelled / completed) |
| GET | `/admin/bookings/{booking}/edit` | `admin.bookings.edit` | `Admin\BookingController@edit` | View/edit booking |
| PUT | `/admin/bookings/{booking}` | `admin.bookings.update` | `Admin\BookingController@update` | Update booking (reschedule) |
| POST | `/admin/bookings/{booking}/cancel` | `admin.bookings.cancel` | `Admin\BookingController@cancel` | Cancel booking |
| GET | `/admin/calendar` | `admin.calendar` | `Admin\BookingController@calendar` | Monthly reservation calendar (reserved / rescheduled) |
| GET | `/admin/overview` | `admin.overview` | `Admin\BookingController@overview` | Monthly reservations per facility (bar graph) |

### College Staff Routes (`auth` + `role:college_staff`)

#### Dashboard & Calendar
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/college/dashboard` | `college.dashboard` | `College\DashboardController@index` | College staff dashboard |
| GET | `/college/calendar` | `college.calendar` | `College\BookingController@calendar` | Read-only booking calendar |

#### Facilities
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/college/facilities` | `college.facilities.index` | `College\FacilityController@index` | List college facilities |
| GET | `/college/facilities/create` | `college.facilities.create` | `College\FacilityController@create` | Show create facility form |
| POST | `/college/facilities` | `college.facilities.store` | `College\FacilityController@store` | Store new college facility |
| GET | `/college/facilities/{facility}/edit` | `college.facilities.edit` | `College\FacilityController@edit` | Show edit facility form |
| PUT | `/college/facilities/{facility}` | `college.facilities.update` | `College\FacilityController@update` | Update college facility |
| PATCH | `/college/facilities/{facility}/deactivate` | `college.facilities.deactivate` | `College\FacilityController@deactivate` | Deactivate an owned facility without deleting it |

#### Bookings
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/college/bookings` | `college.bookings.index` | `College\BookingController@index` | List the user's reservations with filters |
| GET | `/college/calendar` | `college.calendar` | `College\BookingController@calendar` | View the college reservation calendar |

#### Facility Utilization Requests
| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/college/requests` | `college.requests.index` | `College\FormController@indexFacilities` | List the user's facility utilization requests |
| GET | `/college/requests/facilities` | `college.requests.facilities.create` | `College\FormController@createFacilities` | Show new request form |
| POST | `/college/requests/facilities` | `college.requests.facilities.store` | `College\FormController@storeFacilities` | Submit new request |
| GET | `/college/requests/facilities/{submission}` | `college.requests.facilities.show` | `College\FormController@showFacilities` | View a single request |
| POST | `/college/requests/facilities/{submission}/cancel` | `college.requests.facilities.cancel` | `College\FormController@cancelFacilities` | Cancel an eligible request |

### Organization Staff Routes (`auth` + `role:org_staff`)

| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/org/dashboard` | `org.dashboard` | `Org\DashboardController@index` | Organization staff dashboard |
| GET | `/org/bookings` | `org.bookings.index` | `Org\BookingController@index` | List own reservations with filters |
| GET | `/org/calendar` | `org.bookings.calendar` | `Org\BookingController@calendar` | View the organization reservation calendar |
| GET | `/org/requests/facilities` | `org.requests.facilities.create` | `Org\FormController@createFacilities` | Show new request form |
| POST | `/org/requests/facilities` | `org.requests.facilities.store` | `Org\FormController@storeFacilities` | Submit new request |
| GET | `/org/requests/facilities/index` | `org.requests.facilities.index` | `Org\FormController@indexFacilities` | List the user's facility utilization requests |
| GET | `/org/requests/facilities/{submission}` | `org.requests.facilities.show` | `Org\FormController@showFacilities` | View a single request |
| POST | `/org/requests/facilities/{submission}/cancel` | `org.requests.facilities.cancel` | `Org\FormController@cancelFacilities` | Cancel an eligible request |

College and organization staff also have role-scoped team user routes at `/college/users...` and `/org/users...`, including create, edit, update, and deactivate actions. Deactivation preserves account history. Organization users do not have maintenance-ticket routes or portal access.

### GSU High-priority Direct Booking Routes (Admin)

| Method | URI | Route Name | Controller | Description |
|---|---|---|---|---|
| GET | `/admin/bookings/create-direct` | `admin.bookings.create-direct` | `Admin\BookingController@createDirect` | Show direct booking form (GSU override) |
| POST | `/admin/bookings/store-direct` | `admin.bookings.store-direct` | `Admin\BookingController@storeDirect` | Persist direct booking |

### Development account creation

There are no `/make-*` account-creation routes in the current `routes/web.php`. Create local sample accounts with the database seeders; do not expect the retired helper URLs to work.

---

## Layout Views

### `layouts/app.blade.php`
**Path:** `resources/views/layouts/app.blade.php`

The root layout for the entire application. All other layouts extend this.

**Structure:**
- `<head>`: meta tags, page title (`@yield('title')`), Google Fonts (Google Sans Flex), Vite CSS/JS
- `<header>`: top navigation bar with:
  - Logo link ("UA Facility Management") — dynamically links to the user's dashboard based on role
  - Notification link with unread badge count
  - User name and role display
  - Logout button (POST form)
- `<main>`: flash message display (`status` and `error`), navigation buttons (Back + Refresh), content area (`@yield('content')`)
- `<footer>`: university branding text

**Sections:**
- `@yield('title')` — page title (defaults to app name)
- `@yield('main-class')` — custom main container CSS classes
- `@yield('content')` — page body content

---

### `layouts/admin.blade.php`
**Path:** `resources/views/layouts/admin.blade.php`

Extends `layouts.app`. Provides the admin portal shell with sidebar navigation.

**Sidebar Sections:**
| Section | Links |
|---|---|
| Overview | Dashboard |
| Facilities | All facilities, Add facility |
| Requests | Facility utilization requests, Bookings, Booking calendar, Monthly records |
| Administration | Users, Create user |

**Content Section:** `@yield('admin-content')`

---

### `layouts/college.blade.php`
**Path:** `resources/views/layouts/college.blade.php`

Extends `layouts.app`. Provides the college staff portal shell with sidebar navigation.

**Sidebar Header:** "College Staff" + college name from user profile

**Sidebar Sections:**
| Section | Links |
|---|---|
| Overview | Dashboard, Booking calendar |
| Facilities | My facilities, Add facility |
| Bookings | Bookings |
| Requests | My requests, New request |

**Content Section:** `@yield('college-content')`

**Footer:** Displays signed-in user name

---

### `layouts/org.blade.php`
**Path:** `resources/views/layouts/org.blade.php`

Extends `layouts.app`. Provides the organization staff portal shell with sidebar navigation.

**Sidebar Header:** "Organization Staff" + organization name from user profile

**Sidebar Sections:**
| Section | Links |
|---|---|
| Overview | Dashboard |
| Bookings | Booking calendar |
| Requests | My requests, New request |

**Content Section:** `@yield('org-content')`

**Footer:** Displays organization name

---

### `layouts/partials/sidebar.blade.php`
**Path:** `resources/views/layouts/partials/sidebar.blade.php`

Reusable sidebar partial included by all role-specific layouts. Renders navigation dynamically from a `$sections` array.

**Parameters:**
| Parameter | Type | Description |
|---|---|---|
| `$portalTitle` | string | Sidebar header title (e.g., "GSU Admin") |
| `$portalSubtitle` | string/null | Subtitle (e.g., user name or college name) |
| `$sections` | array | Array of nav groups, each with `heading` and `links` |
| `$footer` | string/null | Optional HTML footer content |

**Active Link Detection:** Automatically highlights the current route by comparing `request()->routeIs()` against the link's `route` or `routes` pattern.

---

## Public Views

### `welcome.blade.php`
**Path:** `resources/views/welcome.blade.php`
**Route:** `GET /` → `home`
**Layout:** `layouts.app`

The public landing page and reservation calendar. It includes a no-sign-in tracker for facility requests, bookings, and maintenance tickets.

**Features:**
- Public tracker accepts Facility (FM), Booking (BK; code prefix `BKG-`), or Maintenance (RM).
- Requires the tracking code and the submitter's tracking last name. New accounts and guest facility requests store this value separately; the application does not infer it from the full name. Explicit values are compared as whole values after case/spacing normalization (commas and periods are treated as spaces); records without one retain legacy matching. A mismatch does not reveal whether the record exists.
- Shows status and relevant request/schedule details without exposing requester identity. Lookup results are read-only for internal records.
- Approved guest facility requests may show temporary signed PDF and payment-receipt actions when applicable; receipt upload is handled by the signed `ticket.payment.upload` route.
- Rate-limited JSON lookup: `GET /track/ticket` (`ticket.lookup`, 20 requests/minute).
- The tracker uses a two-row desktop search card and stacks its fields on narrow screens.
- Month navigation (Prev / Next links)
- 7-column calendar grid (Mon–Sun) displaying bookings per day
- Each booking card shows: facility name, time range, purpose (truncated to 60 chars)
- Only `reserved` and `rescheduled` bookings are shown
- Displays unavailable/maintenance facilities and selected-day booking details.
- Calendar data: `$currentMonth` (Carbon), `$days` (array keyed by date string → bookings), `$unavailableFacilities`, `$selectedDate`, and `$selectedDateBookings`.

---

## Auth Views

### `auth/login.blade.php`
**Path:** `resources/views/auth/login.blade.php`
**Route:** `GET /login` → `login`
**Layout:** `layouts.app`

Simple login form with email and password fields.

**Form Details:**
- Method: POST to `login.post` route
- Fields: email (required, autofocus), password (required)
- Error display: shows first validation error
- Submit button: "Login"

---

## Admin Views

### `admin/dashboard.blade.php`
**Path:** `resources/views/admin/dashboard.blade.php`
**Route:** `GET /admin/dashboard` → `admin.dashboard`
**Layout:** `layouts.admin`

Admin welcome dashboard with quick-access card links.

**Quick Access Cards:**
| Card | Links To | Description |
|---|---|---|
| Facilities | `admin.facilities.index` | Manage campus facilities and equipment |
| Facility utilization requests | `admin.forms.facilities.index` | Review and approve facility utilization submissions |
| Bookings | `admin.bookings.index` | View and adjust confirmed bookings |
| User management | `admin.users.index` | Create and manage portal accounts |

**GSU Forms Cards:**
| Card | Links To | Description |
|---|---|---|
| Facilities & utilization form | `forms.facilities.show` | Fill and download the official facilities form |
| Repair & maintenance form | `forms.repair.show` | Generate PDF for repair and maintenance requests |

---

### `admin/facilities/index.blade.php`
**Path:** `resources/views/admin/facilities/index.blade.php`
**Route:** `GET /admin/facilities` → `admin.facilities.index`
**Layout:** `layouts.admin`

Paginated table listing all facilities with CRUD actions.

**Table Columns:** Name (with description), Location, Owner (GSU/College/Organization badge + college name), Availability (badge), Actions (Edit, Delete)

**Features:**
- "Add New Facility" button linking to create page
- Delete confirmation dialog
- Pagination links
- Empty state with CTA

---

### `admin/facilities/create.blade.php`
**Path:** `resources/views/admin/facilities/create.blade.php`
**Route:** `GET /admin/facilities/create` → `admin.facilities.create`
**Layout:** `layouts.admin`

Form to create a new facility.

**Form Fields:**
| Field | Type | Required | Notes |
|---|---|---|---|
| Facility Name | text | Yes | |
| Location | text | Yes | |
| Owner Type | select | Yes | Options: GSU, College, Organization |
| College Name | text | Conditional | Shown via JS when owner_type = "college" |
| Description | textarea | No | |
| Active | checkbox | — | Checked by default |
| Availability Status | select | — | Options: Available, Unavailable, Maintenance |

**Submits to:** POST `admin.facilities.store`

---

### `admin/facilities/edit.blade.php`
**Path:** `resources/views/admin/facilities/edit.blade.php`
**Route:** `GET /admin/facilities/{facility}/edit` → `admin.facilities.edit`
**Layout:** `layouts.admin`

Edit form for an existing facility. Pre-fills all fields from `$facility`. Same fields as create form.

**Additional Logic:**
- For college-owned facilities, the availability status field is read-only (controlled by the college).
- Uses PUT method via `@method('PUT')`

**Submits to:** PUT `admin.facilities.update`

**JavaScript:** Toggles college name field visibility based on owner_type selection.

---

### `admin/bookings/index.blade.php`
**Path:** `resources/views/admin/bookings/index.blade.php`
**Route:** `GET /admin/bookings` → `admin.bookings.index`
**Layout:** `layouts.admin`

Paginated table of all bookings in the system.

**Table Columns:** Code (booking_code), Requester (name + type + unit), Facility, When (date range), Status (badge), Action link ("View / Modify")

---

### `admin/bookings/edit.blade.php`
**Path:** `resources/views/admin/bookings/edit.blade.php`
**Route:** `GET /admin/bookings/{booking}/edit` → `admin.bookings.edit`
**Layout:** `layouts.admin`

Detailed view and edit form for a single booking.

**Displays:** Requester info (name, type, unit), current booking details (facility, times, status)

**Editable Fields:**
| Field | Type | Description |
|---|---|---|
| Facility | select | All facilities listed |
| Date of activity | date | Booking date |
| Start time | time | Start time |
| End time | time | End time |
| Purpose | textarea | Booking purpose |
| Equipment quantities | number inputs | Monobloc Chair, Table, Electric Fan, Rostrum, Flag, Sound, LED Wall |

**Actions:**
- Reschedule (submit form) → PUT `admin.bookings.update`
- Cancel booking → POST `admin.bookings.cancel`

---

### `admin/calendar/index.blade.php`
**Path:** `resources/views/admin/calendar/index.blade.php`
**Route:** `GET /admin/calendar` → `admin.calendar`
**Layout:** `layouts.admin`

Full monthly calendar view of all `reserved` and `rescheduled` bookings.

**Features:**
- Month navigation (Prev / Next)
- 7-column calendar grid (Mon–Sun)
- Each booking card shows: facility name, time range, purpose
- Below the calendar: "Facility booking overview" table showing each facility with its owner, availability status, and booking count for the month

---

### `admin/overview/index.blade.php`
**Path:** `resources/views/admin/overview/index.blade.php`
**Route:** `GET /admin/overview` → `admin.overview`
**Layout:** `layouts.admin`

Monthly booking analytics chart.

**Features:**
- Month navigation (Prev / Next)
- Canvas-based line chart (drawn with vanilla JS, no chart library)
- Shows bookings per day for `reserved`, `rescheduled`, and `completed` bookings
- X-axis: day numbers, Y-axis: booking count
- Blue line with dot markers

---

### `admin/forms/facilities_index.blade.php`
**Path:** `resources/views/admin/forms/facilities_index.blade.php`
**Route:** `GET /admin/forms/facilities` → `admin.forms.facilities.index`
**Layout:** `layouts.admin`

Paginated table of facility utilization requests from staff and public guests.

**Table Columns:** Request code, Requester, Unit, Status, Submitted date, Actions ("View" link). Requests are grouped in decision order; payment state is tracked separately from the request status.

---

### `admin/forms/facilities_show.blade.php`
**Path:** `resources/views/admin/forms/facilities_show.blade.php`
**Route:** `GET /admin/forms/facilities/{submission}` → `admin.forms.facilities.show`
**Layout:** `layouts.admin`

Detailed view of a single utilization request with action buttons.

**Displays:** Requester info, status, date of request, date of activity, time range, facility, purpose, equipment table (item + quantity for non-zero items)

**Actions (conditional):**
| Condition | Actions |
|---|---|
| Eligible pending request | Approve button, Disapprove button |
| Approved or reserved request | Generate PDF when available |
| Guest request with applicable payment state | View receipt or verify an uploaded receipt |

---

### `admin/forms/index.blade.php`
**Path:** `resources/views/admin/forms/index.blade.php`
**Route:** — (legacy/alternate form submissions review page)
**Layout:** `layouts.app`

Legacy, unrouted form review view retained in the repository. The current review workflow uses `admin/forms/facilities_index.blade.php` and `admin/forms/facilities_show.blade.php`.

---

### `admin/forms/show.blade.php`
**Path:** `resources/views/admin/forms/show.blade.php`
**Route:** — (legacy/alternate single submission review page)
**Layout:** `layouts.app`

Detailed view of a form submission with Bootstrap card styling. Shows all payload fields, status badge, equipment list, disapproval reason (if applicable), and action buttons.

---

### `admin/forms/_table.blade.php`
**Path:** `resources/views/admin/forms/_table.blade.php`

Reusable partial for rendering a table of form submissions. Used by `admin/forms/index.blade.php`.

**Table Columns:** Legacy only; this partial is not used by the current routed review workflow.

---

### `admin/users/index.blade.php`
**Path:** `resources/views/admin/users/index.blade.php`
**Route:** `GET /admin/users` → `admin.users.index`
**Layout:** `layouts.admin`

Paginated table of system users with account status and role actions.

**Table Columns:** Name, email, role, unit, contact details, active state, and account actions. Accounts are deactivated instead of deleted so their history is preserved.

---

### `admin/users/create.blade.php`
**Path:** `resources/views/admin/users/create.blade.php`
**Route:** `GET /admin/users/create` → `admin.users.create`
**Layout:** `layouts.admin`

Form to create a new user.

**Form Fields:**
| Field | Type | Required | Notes |
|---|---|---|---|
| Name | text | Yes | |
| Email | email | Yes | |
| Password | password | Yes | 8–94 characters, uppercase and lowercase letters, and a special character |
| Confirm Password | password | Yes | Must match password |
| Role | select | Yes | Options: Admin, College Staff, Organization Staff |
| College Name | text | Conditional | Shown when role = "college_staff" |
| Organization Name | text | Conditional | Shown when role = "org_staff" |

**JavaScript:** Toggles college/org name fields based on selected role.

**Submits to:** POST `admin.users.store`

Names allow letters, spaces, hyphens, and apostrophes. The same account rules apply when editing accounts and in the college/organization team-user forms.

---

### `admin/audit-logs/index.blade.php`
**Path:** `resources/views/admin/audit-logs/index.blade.php`
**Route:** `GET /admin/audit-logs` → `admin.audit-logs.index`
**Layout:** `layouts.admin`

Read-only, searchable and filterable audit history with timestamp, actor, action, subject, redacted field diffs, route, and IP address.

---

## College Staff Views

### `college/dashboard.blade.php`
**Path:** `resources/views/college/dashboard.blade.php`
**Route:** `GET /college/dashboard` → `college.dashboard`
**Layout:** `layouts.college`

College staff welcome dashboard with quick-access cards.

**Quick Access Cards:**
| Card | Links To | Description |
|---|---|---|
| My facilities | `college.facilities.index` | View and manage facilities owned by your college |
| Reservations | `college.bookings.index` | View the signed-in user's reservations |
| Utilization request | `college.requests.facilities.create` | Submit a facilities utilization request to GSU |

---

### `college/facilities/index.blade.php`
**Path:** `resources/views/college/facilities/index.blade.php`
**Route:** `GET /college/facilities` → `college.facilities.index`
**Layout:** `layouts.college`

Paginated table of facilities belonging to the user's college.

**Table Columns:** Name (+ description), Location, Availability (color-coded: green=Available, yellow=Maintenance, red=Unavailable + "Pending GSU verification" if inactive), Actions (Edit, Delete)

---

### `college/facilities/create.blade.php`
**Path:** `resources/views/college/facilities/create.blade.php`
**Route:** `GET /college/facilities/create` → `college.facilities.create`
**Layout:** `layouts.college`

Form to add a new facility for the college. Shows "Adding facility for: [college name]".

**Form Fields:** Facility Name, Location, Description, Active (checkbox), Availability Status (select)

**Note:** Owner type is automatically set to `college` and `owner_college` is set to the user's college — these fields are not shown.

**Submits to:** POST `college.facilities.store`

---

### `college/facilities/edit.blade.php`
**Path:** `resources/views/college/facilities/edit.blade.php`
**Route:** `GET /college/facilities/{facility}/edit` → `college.facilities.edit`
**Layout:** `layouts.college`

Edit form for a college facility. Same fields as create. Pre-fills values from `$facility`.

**Submits to:** PUT `college.facilities.update`

---

### `college/bookings/index.blade.php`
**Path:** `resources/views/college/bookings/index.blade.php`
**Route:** `GET /college/bookings` → `college.bookings.index`
**Layout:** `layouts.college`

Placeholder page. Displays a message that booking management will be implemented here.

---

### `college/bookings/calendar.blade.php`
**Path:** `resources/views/college/bookings/calendar.blade.php`
**Route:** `GET /college/calendar` → `college.calendar`
**Layout:** `layouts.college`

Read-only monthly calendar showing bookings for the college's facilities.

**Features:**
- Month navigation (Prev / Next)
- 7-column calendar grid (Mon–Sun)
- Each booking card shows: facility name, time range, purpose
- "Facility booking overview" table below: facility name, availability status, booking count for the month

**Data Required:** `$currentMonth`, `$days`, `$collegeName`, `$facilityCounts`

---

### `college/requests/facilities_create.blade.php`
**Path:** `resources/views/college/requests/facilities_create.blade.php`
**Route:** `GET /college/requests/facilities` → `college.requests.facilities.create`
**Layout:** `layouts.college`

Form to submit a facilities utilization request to GSU.

**Form Fields:**
| Field | Type | Required | Notes |
|---|---|---|---|
| Date of Activity | date | Yes | |
| Start Time | time | Yes | |
| End Time | time | Yes | |
| Facility | select | Yes | Grouped: GSU Facilities, My College Facilities |
| Purpose | textarea | Yes | |
| Equipment (7 items) | number with ±1/±10 buttons | No | Monobloc Chair, Table, Electric Fan, Rostrum, Flag & School Color, Sound, LED Wall |

**JavaScript:** `adjustQty()` function for incrementing/decrementing equipment quantities with buttons.

**Submits to:** POST `college.requests.facilities.store`

---

### `college/requests/facilities_index.blade.php`
**Path:** `resources/views/college/requests/facilities_index.blade.php`
**Route:** `GET /college/requests` → `college.requests.index`
**Layout:** `layouts.college`

Paginated table of the college staff user's own facility utilization requests, including reserved requests.

**Table Columns:** Date Requested, Activity Date, Time, Purpose (truncated 50 chars), Status (badge), Actions ("View" link)

---

### `college/requests/facilities_show.blade.php`
**Path:** `resources/views/college/requests/facilities_show.blade.php`
**Route:** `GET /college/requests/facilities/{submission}` → `college.requests.facilities.show`
**Layout:** `layouts.college`

Read-only detail view of a single utilization request.

**Displays:** Requested on date, Status, Date of Activity, Time range, Facility (name + location), Purpose, Equipment table (non-zero quantities only)

---

### `college/forms/facilities.blade.php`
**Path:** `resources/views/college/forms/facilities.blade.php`
**Route:** — (legacy form submission page)
**Layout:** `layouts.app`

Older version of the facility utilization form using Bootstrap card/form styling. Includes facility dropdown, "Other" facility text field, equipment quantity inputs, and JavaScript for show/hide logic.

**Submits to:** POST `college.requests.facilities.store`

---

### `college/forms/index.blade.php`
**Path:** `resources/views/college/forms/index.blade.php`
**Route:** — (legacy submissions list)
**Layout:** `layouts.app`

Bootstrap-styled table listing the user's form submissions with control number, dates, facility, status badge, and view link.

---

### `college/forms/show.blade.php`
**Path:** `resources/views/college/forms/show.blade.php`
**Route:** — (legacy submission detail)
**Layout:** `layouts.app`

Bootstrap card showing detailed submission info. Includes approval/disapproval alerts.

---

## Organization Staff Views

### `org/dashboard.blade.php`
**Path:** `resources/views/org/dashboard.blade.php`
**Route:** `GET /org/dashboard` → `org.dashboard`
**Layout:** `layouts.org`

Simple organization staff welcome dashboard with placeholder text.

---

### `org/bookings/calendar.blade.php`
**Path:** `resources/views/org/bookings/calendar.blade.php`
**Route:** `GET /org/bookings` → `org.bookings.index`
**Layout:** `layouts.org`

Read-only monthly calendar showing bookings requested by the organization.

**Features:**
- Month navigation (Prev / Next)
- 7-column calendar grid (Mon–Sun)
- Each booking card: facility name, time range, purpose

**Data Required:** `$currentMonth`, `$days`

---

### `org/requests/facilities_index.blade.php`
**Path:** `resources/views/org/requests/facilities_index.blade.php`
**Route:** `GET /org/requests/facilities/index` → `org.requests.facilities.index`
**Layout:** `layouts.org`

Paginated table of the organization staff user's own facility utilization requests, including reserved requests.

**Table Columns:** Date Requested, Activity Date, Time, Purpose, Status (badge), Actions ("View" link)

---

## Repair and Maintenance Views

| View | Purpose |
|---|---|
| `admin/maintenance-tickets/index.blade.php` | Admin queue grouped into active and completed tickets |
| `admin/maintenance-tickets/show.blade.php` | Ticket details, status/remarks update form, and college conversation when applicable |
| `college/maintenance-tickets/index.blade.php` | Tickets submitted across the signed-in user's college team |
| `college/maintenance-tickets/create.blade.php` | New repair ticket form, including an unlisted “Others” location |
| `college/maintenance-tickets/show.blade.php` | Ticket detail, progress, and live college-to-GSU conversation |

College repair tickets require the account's explicit tracking last name for public status lookup. Ticket codes use the `RM-…` format.

## GSU PDF Form Views

These are standalone HTML pages (not using the app layout) designed to replicate the official University of Antique GSU paper forms. They are styled to look like printed documents with official university headers, and can be filled in the browser then downloaded as PDF.

### `forms/facilities.blade.php`
**Path:** `resources/views/forms/facilities.blade.php`
**Route:** `GET /forms/facilities` → `forms.facilities.show`
**Layout:** Standalone (no layout inheritance)

Full-page HTML replica of the **"Facilities and Utilization Form"** (GSU document).

**Sections:**
1. **Header:** Republic of the Philippines, University of Antique letterhead, address
2. **Control No. & Date Request:** fields at top-right
3. **Info Table:** Requesting Party, College/Org, Date of Activity, Time (From/To), Contact Person, Purpose
4. **Venue checkboxes:** 11 UA facilities as checkboxes (BUSALAN HALL, AVR-USA HALL, E-HUB, etc.) + "Others" text field
5. **Facilities/Equipment table:** Items (Monobloc Chair, Table, Electric Fan, Rostrum, Flag & School Color, Sound, LED Display) with quantity inputs
6. **Signature grid:** Three columns — Requested by, Noted by (Immediate Supervisor), Approved by (VP/Admin Director)
7. **Download button:** Submits POST to `forms.facilities.download`

**Styling:** Custom inline CSS using EB Garamond (serif) and Source Sans 3 (sans-serif). Navy blue (#1a3a6b) accent colors. Designed to print at letter size.

---

### `forms/repair.blade.php`
**Path:** `resources/views/forms/repair.blade.php`
**Route:** `GET /forms/repair` → `forms.repair.show`
**Layout:** Standalone (no layout inheritance)

Full-page HTML replica of the **"Repair and Maintenance Requisition Form"** (GSU document).

**Sections:**
1. **Header:** Same UA letterhead as facilities form
2. **Control No.:** auto-generated or manual
3. **Request Details:** Requesting Party, College/Org, Date, Tel No.
4. **Submission mode:** Radio buttons (In-person, Letter/phone call, Email, System-generated)
5. **Request Type:** Checkboxes (Electrical, Plumbing, Masonry, Carpentry, Welding, Others)
6. **Description:** Textarea for issue description
7. **Approval section:** Approved/Not Approved checkboxes, Reason field, Date
8. **GSU Personnel:** Staff name, preferred time fields
9. **Work Done table:** Multi-row table with Date, Work done, Remarks, Staff signature columns
10. **Completion section:** Date completed, inspected/confirmed by
11. **Download button:** Submits POST to `forms.repair.download`

**Styling:** Same styling approach as the facilities form — printed document replica.

---

## Notification Views

### `notifications/index.blade.php`
**Path:** `resources/views/notifications/index.blade.php`
**Route:** `GET /notifications` → `notifications.index`
**Layout:** `layouts.app`

Displays all notifications for the authenticated user, grouped into four categories.

**Notification Categories:**
| Category | Header | Notification Types |
|---|---|---|
| New / pending requests | "New / pending requests" | `form_pending`, `form_pending_admin` |
| Decisions on your requests | "Decisions on your requests" | `form_approved`, `form_disapproved` |
| Booking updates | "Booking updates" | `booking_created`, `booking_rescheduled`, `booking_cancelled` |
| Other | "Other notifications" | Any type not matching above |

**Each Category Table Columns:** Status (Read/New badge), Title, Message, When (date), Action ("Mark as read" button for unread items)

**Mark as Read:** POST form to `notifications.read` route

---

## View Hierarchy Summary

```
layouts/
├── app.blade.php               ← Root layout (header, nav, footer)
├── admin.blade.php             ← Extends app, adds admin sidebar
├── college.blade.php           ← Extends app, adds college sidebar
├── org.blade.php               ← Extends app, adds org sidebar
└── partials/
    └── sidebar.blade.php       ← Reusable sidebar partial

welcome.blade.php               ← Public homepage, reservation calendar, and FM/BK/RM tracker
auth/login.blade.php            ← Login page

admin/
├── dashboard.blade.php         ← Admin dashboard
├── facilities/
│   ├── index.blade.php         ← Facilities list
│   ├── create.blade.php        ← Add facility form
│   └── edit.blade.php          ← Edit facility form
├── bookings/
│   ├── index.blade.php         ← Bookings list
│   └── edit.blade.php          ← View/edit booking
├── calendar/
│   └── index.blade.php         ← Admin calendar view
├── overview/
│   └── index.blade.php         ← Monthly overview chart
├── forms/
│   ├── facilities_index.blade.php  ←  Approve Requests list
│   ├── facilities_show.blade.php   ← Request detail + actions
│   ├── index.blade.php         ← Legacy tabbed submissions view
│   ├── show.blade.php          ← Legacy submission detail
│   └── _table.blade.php        ← Reusable submission table partial
└── users/
    ├── index.blade.php         ← Users list
    └── create.blade.php        ← Add user form

college/
├── dashboard.blade.php         ← College dashboard
├── facilities/
│   ├── index.blade.php         ← College facilities list
│   ├── create.blade.php        ← Add college facility
│   └── edit.blade.php          ← Edit college facility
├── bookings/
│   ├── index.blade.php         ← Placeholder page
│   └── calendar.blade.php      ← Read-only booking calendar
├── requests/
│   ├── facilities_create.blade.php  ← New utilization request form
│   ├── facilities_index.blade.php   ← Own requests list
│   └── facilities_show.blade.php    ← Request detail view
└── forms/                      ← Legacy form pages
    ├── facilities.blade.php
    ├── index.blade.php
    └── show.blade.php

org/
├── dashboard.blade.php         ← Org dashboard
├── bookings/
│   └── calendar.blade.php      ← Read-only booking calendar
└── requests/
    └── facilities_index.blade.php  ← Own requests list

forms/
├── facilities.blade.php        ← GSU Facilities Utilization PDF form
└── repair.blade.php            ← GSU Repair & Maintenance PDF form

notifications/
└── index.blade.php             ← Notifications page (all roles)
```
