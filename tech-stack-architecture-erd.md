# Tech Stack, Architecture & ERD — UA Facility Management System

> **NOTE:** Current reservation status uses **`reserved`** instead of legacy **`booked`**. The hourly `bookings:normalize-and-complete` scheduled command normalizes legacy rows and marks elapsed reservations `completed`.
>
> - Active reservation statuses: `reserved`, `rescheduled`
> - Historical statuses: `completed`, `cancelled`
> - Current facility request statuses include `pending`, `reserved`, `disapproved`, and `cancelled`; `approved` and `pending_payment` remain for legacy rows
> - Guest payment states are tracked separately in `payment_status`
>
> Older records may still contain `booked`; it is a legacy value, not the current active status.

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [System Architecture](#system-architecture)
3. [Entity Relationship Diagram (ERD)](#entity-relationship-diagram-erd)

---

## Tech Stack

The maintained application is the Laravel web system. Desktop, Electron, and Android material under `legacy/` is archival and is not required to operate the web app. The current web application has no `/fms-portal-entry` token route; staff sign in through `/login` with Laravel sessions and role middleware.

### Backend

| Component | Technology | Version | Purpose |
|---|---|---|---|
| Language | PHP | 8.2+ | Server-side programming |
| Framework | Laravel | 12.x | MVC web framework |
| ORM | Eloquent | (bundled with Laravel) | Database abstraction and model relationships |
| Templating | Blade | (bundled with Laravel) | Server-side HTML rendering |
| Database | MySQL | — | Production relational database |
| Session Driver | Database in `.env.example` | — | Session-based authentication; another supported driver may be configured |
| Broadcasting | Laravel Reverb + Echo | Reverb 1.x / Echo 2.x | WebSocket infrastructure; existing conversations continue polling |

### Backend Dependencies (Composer)

| Package | Purpose |
|---|---|
| `phpoffice/phpword` | Generate DOCX documents (facilities utilization and repair forms) |
| `dompdf/dompdf` | PDF generation dependency |
| `defuse/php-encryption` | Composer dependency; verify application call sites before relying on it |
| `web-token/jwt-core` | Composer dependency; staff web authentication uses Laravel sessions |
| `phpseclib/phpseclib` | Cryptographic library dependency |
| `hashids/hashids` | ID-encoding dependency; not a substitute for authorization |

### Backend Dev Dependencies

| Package | Purpose |
|---|---|
| `fakerphp/faker` | Generate fake data for testing and seeding |
| `laravel/pail` | Real-time log tailing |
| `laravel/pint` | Code style fixer (PSR-12) |
| `laravel/sail` | Docker development environment |
| `phpunit/phpunit` | Unit and feature testing |
| `mockery/mockery` | Mock objects for testing |
| `nunomaduro/collision` | Better error reporting in CLI |

### Frontend

| Component | Technology | Version | Purpose |
|---|---|---|---|
| CSS Framework | Tailwind CSS | 4.x | Utility-first CSS styling |
| Build Tool | Vite | 7.x | Asset bundling and HMR |
| Vite Plugin | `laravel-vite-plugin` | 2.x | Laravel/Vite integration |
| HTTP Client | Axios | 1.x | AJAX requests |
| Fonts | Defined in the current Blade/CSS assets | — | See the UI documentation for design details |

### Frontend Dependencies (npm)

| Package | Purpose |
|---|---|
| `crypto-js` | Dependency present; not a substitute for server-side controls |
| `bcryptjs` | Dependency present; Laravel performs server-side password hashing |
| `jsonwebtoken` | Dependency present; staff web authentication uses Laravel sessions |
| `laravel-echo`, `pusher-js` | Reverb WebSocket client |

### Frontend Dev Dependencies

| Package | Purpose |
|---|---|
| `@tailwindcss/vite` | Tailwind CSS Vite plugin |
| `concurrently` | Run multiple dev servers in parallel |
| `javascript-obfuscator` | Obfuscate client-side JavaScript |
| `terser` | JavaScript minification |

### Archived Desktop Launcher References

Old launcher documentation is retained under `legacy/`; launcher source files and token-gated portal entry are not part of the current web workflow.

| Component | Technology | Purpose |
|---|---|---|
| Runtime | Python 3.x | Desktop launcher scripting |
| GUI Framework | pywebview | Lightweight embedded browser (CEF/WebKit) |
| Packager | PyInstaller | Bundle into standalone `.exe` files |
| Obfuscation | PyArmor | Protect Python source code |
| Security | cryptography, PyJWT, bcrypt | Token validation and key verification |

**Documentation:** `legacy/LAUNCHER_README.md`

### Archived Electron References

The former Electron launcher is not part of the current application workflow; do not rely on the old token-based authentication details below.

| Component | Technology | Purpose |
|---|---|---|
| Framework | Electron | 34.x | Cross-platform desktop app shell |
| Packager | electron-builder | 25.x | Build portable `.exe` files |
| Config | variant.json + write-variant.js | Role selection at build time |

**Status:** Historical project material only.

### Archived Android Client Reference

The organization WebView client is archived under `legacy/android/`; its token-based portal entry is not supported by the current web routes.

| Component | Technology | Purpose |
|---|---|---|
| Language | Kotlin | Native Android development |
| UI | Android WebView | Wraps the Laravel web portal |
| Build System | Gradle (Kotlin DSL) | Build and package APK |
| Target | Organization Staff only | Mirrors `org_launcher.py` behavior |

**App Files:**
- `legacy/android/ua-fms-org/` — archived Android WebView client source

### Document Generation

| Component | Technology | Purpose |
|---|---|---|
| DOCX Generation | PHPOffice/PHPWord | Create Word documents from templates |
| PDF Conversion | LibreOffice (headless) | Convert DOCX → PDF on the server |

### Dev Tooling

| Tool | Purpose |
|---|---|
| `composer dev` | Runs Laravel server, queue, log tail, and Vite concurrently |
| `composer test` | Runs PHPUnit test suite |
| `composer setup` | Full project setup (install, key, migrate, build) |
| Laravel Pint | PHP code formatting |
| PHPUnit | Automated testing |

---

## System Architecture

### High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                             │
│                                                                 │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                     Web Browser                            │  │
│  │ Public calendar, guest request form, status tracker, login, │  │
│  │ and role-restricted staff portals                           │  │
│  │ Legacy desktop/mobile wrappers are not required or supported│  │
│  └─────────────────────────────┬──────────────────────────────┘  │
└────────────────────────────┬─────────────────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                            │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                    Laravel 12 (PHP 8.2)                    │  │
│  │                                                            │  │
│  │  ┌─────────────────────────────────────────────────────┐   │  │
│  │  │                   MIDDLEWARE                         │   │  │
│  │  │                                                     │   │  │
│  │  │  auth (session)           →  Authentication          │   │  │
│  │  │  RoleMiddleware           →  Role-based access       │   │  │
│  │  │  EnsureActiveUser         →  Active-account checks   │   │  │
│  │  │  CSRF protection          →  Form security           │   │  │
│  │  └─────────────────────────────────────────────────────┘   │  │
│  │                                                            │  │
│  │  ┌─────────────────────────────────────────────────────┐   │  │
│  │  │                  CONTROLLERS                        │   │  │
│  │  │                                                     │   │  │
│  │  │  AuthController           →  Login/logout            │   │  │
│  │  │  PublicCalendarController →  Public homepage          │   │  │
│  │  │  TicketLookupController → Public FM/BK/RM status     │   │  │
│  │  │  NotificationController   →  User notifications      │   │  │
│  │  │  GsuFormController        →  PDF form generation      │   │  │
│  │  │                                                     │   │  │
│  │  │  Admin\                                              │   │  │
│  │  │    DashboardController    →  Admin dashboard          │   │  │
│  │  │    FacilityController     →  Manage facilities         │   │  │
│  │  │    BookingController      →  Manage bookings          │   │  │
│  │  │    FormSubmissionController → Review requests         │   │  │
│  │  │    MaintenanceTicketController → Update tickets       │   │  │
│  │  │    FacilitiesFormPdfController → Generate PDFs        │   │  │
│  │  │    UserController         →  Manage users             │   │  │
│  │  │                                                     │   │  │
│  │  │  College\                                            │   │  │
│  │  │    DashboardController    →  College dashboard        │   │  │
│  │  │    FacilityController     →  College facilities       │   │  │
│  │  │    BookingController      →  Read-only calendar       │   │  │
│  │  │    FormController         →  Submit/view requests     │   │  │
│  │  │    MaintenanceTicketController → Tickets and chat     │   │  │
│  │  │                                                     │   │  │
│  │  │  Org\                                                │   │  │
│  │  │    DashboardController    →  Org dashboard            │   │  │
│  │  │    BookingController      →  Read-only calendar       │   │  │
│  │  │    FormController         →  Submit/view requests     │   │  │
│  │  │    MaintenanceTicketController → Own tickets          │   │  │
│  │  └─────────────────────────────────────────────────────┘   │  │
│  │                                                            │  │
│  │  ┌─────────────────────────────────────────────────────┐   │  │
│  │  │                     VIEWS                           │   │  │
│  │  │                                                     │   │  │
│  │  │  Blade templates                                     │   │  │
│  │  │  Tailwind CSS v4 styling                             │   │  │
│  │  │  Vite asset bundling                                 │   │  │
│  │  │  Standalone PDF form views (facilities + repair)     │   │  │
│  │  └─────────────────────────────────────────────────────┘   │  │
│  │                                                            │  │
│  │  ┌─────────────────────────────────────────────────────┐   │  │
│  │  │                ELOQUENT MODELS                      │   │  │
│  │  │                                                     │   │  │
│  │  │  User, College, Facility, Signatory, Equipment,      │   │  │
│  │  │  Booking, FormSubmission, MaintenanceTicket,         │   │  │
│  │  │  MaintenanceTicketMessage, MaintenanceLog,           │   │  │
│  │  │  StaffAssignment, FormControl, Notification          │   │  │
│  │  └─────────────────────────────────────────────────────┘   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────┬───────────────────────────────────┘
                               │
                               ▼
┌──────────────────────────────────────────────────────────────────┐
│                       DATA LAYER                                 │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                      MySQL Database                        │  │
│  │                                                            │  │
│  │  users, colleges, facilities, signatories, equipment,      │  │
│  │  bookings, booking_facility, form_submissions,             │  │
│  │  maintenance_tickets, maintenance_ticket_messages,         │  │
│  │  maintenance_logs, staff_assignments, form_controls,       │  │
│  │  notifications, sessions, jobs, cache                      │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                   File System Storage                      │  │
│  │                                                            │  │
│  │  Private payment receipts and supporting attachments       │  │
│  │  Generated DOCX/PDF documents (temporary)                  │  │
│  │  Session files, cache, logs                                │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### Authentication & Access Flow

```
Browser ── GET /login ──> Laravel session authentication
                              │
                              ├── role:admin ─────────────> Admin portal
                              ├── role:college_staff ─────> College portal
                              └── role:org_staff ─────────> Organization portal

Public calendar, guest facility requests, and status lookup do not require staff authentication.
Active-account middleware blocks deactivated users from protected pages.
```

### Role-Based Access Control (RBAC)

| Role | Dashboard | Facilities | Bookings | Requests | Users | Forms PDF | Notifications |
|---|---|---|---|---|---|---|---|
| **admin** | Admin dashboard | Full management | Manage reservations + calendar + reports | Review, approve, disapprove, reserve | Manage staff accounts | Generate & download | Yes |
| **college_staff** | College dashboard | Manage own college facilities | Own reservations + college calendar | Submit, view, cancel own | Manage college team | — | Yes |
| **org_staff** | Org dashboard | — | Own reservations + calendar | Submit, view, cancel own | Manage organization team | — | Yes |
| **viewer** | Public calendar | — | — | — | — | — | — |

### Request-to-Booking Workflow

```
College/Org Staff                         Admin (GSU)
      │                                       │
      │  1. Submit Facilities                  │
      │     Utilization Request                │
      │  ─────────────────────────────────→    │
      │     (FormSubmission created,           │
      │      status = 'pending')               │
      │                                        │
      │                                   2. Review request
      │                                        │
      │                                   3a. Review / approve
      │                                        │  Create reservation if available
      │                                        │  (submission status → 'reserved')
      │                                        │  Guest payment tracked separately
      │                                        │
      │                                   3b. OR Disapprove
      │                                        │  (status → 'disapproved')
      │                                        │
      │                                   4. Generate PDF
      │                                        │  (PHPWord → DOCX → PDF)
      │                                        │
      │                                   5. Reserve requested facilities/days
      │                                        │  (Booking records created,
      │                                        │   FormSubmission → 'reserved')
      │                                        │  Notification sent to requester
      │  ←─────────────────────────────────    │
      │     Notification: "booking_created"    │
      │                                        │
      │                                   6. Manage booking
      │                                        │  (reschedule / cancel)
      │                                        │
```

### Public Status Lookup Flow

The homepage (`GET /`) renders the public calendar and tracker. The portal calendars and reservation date pickers share the rate-limited `GET /calendar/data` month endpoint, backed by `ReservationCalendarService`; requester details are limited to staff roles. The tracker calls the rate-limited `GET /track/ticket` endpoint with a record type, tracking code, and submitter last name; authentication is not required.

| Type | Record source | Code | Status/details returned |
|---|---|---|---|
| `FM` | Facility utilization `FormSubmission` | `FM-<submission year>-<submission id>` | Status, venue, date/time; a rescheduled booking's updated schedule is used |
| `BK` | `Booking` | `booking_code` (`BKG-…`) | Status, venue, purpose, date/time |
| `RM` | `MaintenanceTicket` | `ticket_code` (`RM-…`) | Status, facility, subject, submitted date |

For new records, the controller checks the separately stored tracking last name: the account's `last_name` for maintenance and internal requests, or `requester_last_name` in the FM payload for guest submissions. Bookings linked to an FM submission use that submission's tracking value. It does not infer a surname from the full name when an explicit value exists; older records without one retain legacy full-name matching. Results omit requester identity, and an unmatched code and surname produce the same not-found response. This lookup path is read-only. For approved guest FM submissions only, the response can include short-lived signed links to the PDF and, when applicable, receipt-upload action. Receipt upload is a separate signed, rate-limited POST endpoint.

### Deployment Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Production Server                     │
│                                                         │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────┐  │
│  │  Web Server  │    │   Laravel    │    │   MySQL   │  │
│  │  (Apache/    │───→│   App       │───→│  Database  │  │
│  │   Nginx)    │    │  (PHP 8.2)  │    │           │  │
│  │  + TLS      │    │             │    │           │  │
│  └─────────────┘    └──────────────┘    └───────────┘  │
│                            │                            │
│                     ┌──────┴──────┐                     │
│                     │ LibreOffice │                     │
│                     │ (headless)  │                     │
│                     │ DOCX → PDF  │                     │
│                     └─────────────┘                     │
└─────────────────────────────────────────────────────────┘
                              ▲
                              │
                       ┌──────┴──────┐
                       │ Web Browser │
                       │   (HTTPS)   │
                       └─────────────┘
```

### Directory Structure

```
Capstone-Univerity-Of-Antique-Facility-Management-System/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              ← Admin controllers
│   │   │   ├── College/            ← College staff controllers
│   │   │   ├── Org/                ← Organization controllers
│   │   │   ├── AuthController.php
│   │   │   ├── GsuFormController.php
│   │   │   ├── NotificationController.php
│   │   │   └── PublicCalendarController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   └── EnsureActiveUser.php
│   │   └── ...
│   ├── Models/                     ← Eloquent models (13)
│   └── Providers/
│       └── AppServiceProvider.php  ← Global notification count view composer
│
├── resources/
│   ├── views/
│   │   ├── layouts/                ← Layout templates and shared partials
│   │   ├── admin/                  ← Admin views
│   │   ├── college/                ← College views
│   │   ├── org/                    ← Organization views
│   │   ├── forms/                  ← GSU PDF form views (2)
│   │   ├── notifications/          ← Notification views
│   │   ├── auth/                   ← Auth views
│   │   ├── public/                 ← Public views
│   │   └── welcome.blade.php      ← Public landing page, calendar, and ticket tracker
│   ├── css/app.css
│   └── js/app.js
│
├── routes/
│   └── web.php                     ← All route definitions
│
├── database/
│   ├── migrations/                 ← Schema migrations
│   ├── seeders/                    ← FacilitySeeder + DatabaseSeeder
│   └── factories/                  ← Model factories
│
├── legacy/                         ← Archived client documentation and Android wrapper
│
├── DEPLOYMENT_GUIDE.md
├── composer.json
├── package.json
└── vite.config.js
```

---

## Entity Relationship Diagram (ERD)

### Text-Based ERD


```mermaid
classDiagram
    class User {
        +bigint id
        +string name
        +string last_name
        +string email
        +string role
        +string position
        +string contact_number
        +string college_name
        +bigint college_id
        +string organization_name
        +bool is_active
    }

    class College {
        +bigint id
        +string name
        +string short_name
        +text description
    }

    class Facility {
        +bigint id
        +string name
        +string location
        +string owner_type
        +string owner_college
        +bigint college_id
        +text description
        +bool is_active
        +string availability_status
    }

    class Booking {
        +bigint id
        +datetime start_time
        +datetime end_time
        +string requester_type
        +string requester_unit
        +string status
        +text purpose
        +text additional_details
        +string booking_code
    }

    class MaintenanceTicket {
        +bigint id
        +string request_method
        +string status
        +text issue_description
        +text admin_remarks
        +datetime requested_at
        +datetime approved_at
        +datetime completed_at
    }

    class StaffAssignment {
        +bigint id
        +datetime assigned_at
        +datetime preferred_time
    }

    class MaintenanceLog {
        +bigint id
        +text work_done
        +text remarks
        +datetime logged_at
        +string staff_signature
    }

    class FormControl {
        +bigint id
        +string control_number
        +string form_type
    }

    class FormSubmission {
        +bigint id
        +string type
        +string requester_type
        +string requester_unit
        +string status
        +string payment_status
        +json payload
    }

    class Signatory {
        +bigint id
        +string type
        +string name
        +string unit
        +bigint college_id
        +bool is_active
    }

    class MaintenanceTicketMessage {
        +bigint id
        +bigint maintenance_ticket_id
        +bigint user_id
        +text body
    }

    class Notification {
        +bigint id
        +string type
        +string title
        +text message
        +json data
        +bool is_read
    }

    class Equipment {
        +bigint id
        +string name
        +string category
        +int total_quantity
        +bool is_active
    }

    class AuditLog {
        +bigint id
        +bigint actor_id
        +string action
        +string subject_type
        +bigint subject_id
        +json old_values
        +json new_values
    }

    User "1" --> "*" Booking : requester_id
    User "1" --> "*" MaintenanceTicket : requester_id
    User "1" --> "*" StaffAssignment : staff_id
    User "1" --> "*" MaintenanceLog : staff_id
    User "1" --> "*" Notification : user_id
    User "1" --> "*" FormSubmission : requester_id
    User "1" --> "*" AuditLog : actor_id (nullable)

    College "1" --> "*" User : college_id
    College "1" --> "*" Facility : college_id
    College "1" --> "*" Signatory : college_id
    Facility "*" --> "*" Booking : booking_facility
    Facility "1" --> "*" MaintenanceTicket : facility_id
    Booking "1" --> "*" MaintenanceTicket : booking_id (nullable)

    MaintenanceTicket "1" --> "*" StaffAssignment : maintenance_ticket_id
    MaintenanceTicket "1" --> "*" MaintenanceLog : maintenance_ticket_id
    MaintenanceTicket "1" --> "*" MaintenanceTicketMessage : maintenance_ticket_id
    User "1" --> "*" MaintenanceTicketMessage : user_id

    note for FormSubmission "Facilities utilization / repair form data stored as JSON payload"
    note for Equipment "Equipment quantities are stored in JSON payload (not via FK in schema)"
    note for FormControl "Generates sequential GSU control numbers for forms"
```

> **Historical diagram:** The box-drawing ERD below is an older snapshot and does not represent the current schema. Use the Mermaid ERD and relationship summary above/below as the current references.

┌─────────────────────────────────┐
│              USER               │
├─────────────────────────────────┤
│ PK  id              BIGINT     │
│     name            VARCHAR    │
│     email           VARCHAR UQ │
│     email_verified_at TIMESTAMP│
│     password        VARCHAR    │
│     remember_token  VARCHAR    │
│     role            VARCHAR    │
│     college_name    VARCHAR    │
│     organization_name VARCHAR  │
│     created_at      TIMESTAMP  │
│     updated_at      TIMESTAMP  │
└──────────┬──────────────────────┘
           │
           │ 1
           │
     ┌─────┼──────────┬──────────────┬───────────────┐
     │     │          │              │               │
     │ ∞   │ ∞        │ ∞            │ ∞             │ ∞
     ▼     ▼          ▼              ▼               ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────────┐
│   BOOKING    │ │ MAINTENANCE  │ │    STAFF     │ │ MAINTENANCE  │ │   NOTIFICATION   │
│              │ │   TICKET     │ │  ASSIGNMENT  │ │     LOG      │ │                  │
├──────────────┤ ├──────────────┤ ├──────────────┤ ├──────────────┤ ├──────────────────┤
│PK id         │ │PK id         │ │PK id         │ │PK id         │ │PK id             │
│FK requester_id│ │FK facility_id│ │FK maint_     │ │FK maint_     │ │FK user_id        │
│FK facility_id│ │FK requester_id││   ticket_id  │ │   ticket_id  │ │   type           │
│   start_time │ │FK booking_id │ │FK staff_id   │ │FK staff_id   │ │   title          │
│   end_time   │ │   request_   │ │   assigned_at│ │   work_done  │ │   message        │
│   requester_ │ │     method   │ │   preferred_ │ │   remarks    │ │   data (JSON)    │
│     type     │ │   status     │ │     time     │ │   logged_at  │ │   is_read        │
│   requester_ │ │   issue_     │ └──────────────┘ │   staff_     │ └──────────────────┘
│     unit     │ │   description│                   │   signature  │
│   status     │ │   admin_     │                   └──────────────┘
│   request_   │ │     remarks  │
│     method   │ │   requested_ │
│   purpose    │ │     at       │
│   additional_│ │   approved_at│
│     details  │ │   completed_ │
│   requested_ │ │     at       │
│     at       │ └──────────────┘
│   approved_at│
│   booking_   │
│     code UQ  │
└──────┬───────┘
       │
       │ 1
       │
       │ ∞
       ▼
┌──────────────┐
│ MAINTENANCE  │
│   TICKET     │
│ (booking_id) │
└──────────────┘


┌─────────────────────────────────┐
│            FACILITY             │
├─────────────────────────────────┤
│ PK  id              BIGINT     │
│     name            VARCHAR    │
│     location        VARCHAR    │
│     owner_type      VARCHAR    │
│     owner_college   VARCHAR    │
│     description     TEXT       │
│     is_active       BOOLEAN    │
│     availability_   VARCHAR    │
│       status                   │
│     created_at      TIMESTAMP  │
│     updated_at      TIMESTAMP  │
└──────────┬──────────────────────┘
           │ 1
           │
     ┌─────┴──────────┐
     │ ∞              │ ∞
     ▼                ▼
┌──────────┐   ┌──────────────┐
│ BOOKING  │   │ MAINTENANCE  │
│          │   │   TICKET     │
└──────────┘   └──────────────┘


┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│          FORM_CONTROL           │       │        FORM_SUBMISSION          │
├─────────────────────────────────┤       ├─────────────────────────────────┤
│ PK  id              BIGINT     │       │ PK  id              BIGINT     │
│     control_number  VARCHAR UQ │       │     type            VARCHAR    │
│     form_type       VARCHAR    │       │ FK  requester_id    BIGINT     │
│     created_at      TIMESTAMP  │       │     requester_type  VARCHAR    │
│     updated_at      TIMESTAMP  │       │     requester_unit  VARCHAR    │
└─────────────────────────────────┘       │     status          VARCHAR    │
                                          │     payload         JSON       │
┌─────────────────────────────────┐       │     created_at      TIMESTAMP  │
│           EQUIPMENT             │       │     updated_at      TIMESTAMP  │
├─────────────────────────────────┤       └─────────────────────────────────┘
│ PK  id              BIGINT     │                    │
│     name            VARCHAR    │                    │ ∞
│     category        VARCHAR    │                    │
│     total_quantity  INTEGER    │                    ▼
│     is_active       BOOLEAN    │               ┌──────────┐
│     created_at      TIMESTAMP  │               │   USER   │
│     updated_at      TIMESTAMP  │               │   (1)    │
└─────────────────────────────────┘               └──────────┘
```

### Relationship Summary Table

| Relationship | From | To | Type | Foreign Key | On Delete |
|---|---|---|---|---|---|
| User → Bookings | User | Booking | One-to-Many | `bookings.requester_id` | CASCADE |
| User → MaintenanceTickets | User | MaintenanceTicket | One-to-Many | `maintenance_tickets.requester_id` | CASCADE |
| User → StaffAssignments | User | StaffAssignment | One-to-Many | `staff_assignments.staff_id` | CASCADE |
| User → MaintenanceLogs | User | MaintenanceLog | One-to-Many | `maintenance_logs.staff_id` | CASCADE |
| User → Notifications | User | Notification | One-to-Many | `notifications.user_id` | CASCADE |
| User → FormSubmissions | User | FormSubmission | One-to-Many | `form_submissions.requester_id` | CASCADE |
| User → AuditLogs | User | AuditLog | One-to-Many | `audit_logs.actor_id` | SET NULL |
| User → TicketMessages | User | MaintenanceTicketMessage | One-to-Many | `maintenance_ticket_messages.user_id` | SET NULL |
| Facility ↔ Bookings | Facility | Booking | Many-to-Many | `booking_facility.facility_id`, `booking_facility.booking_id` | CASCADE |
| Facility → MaintenanceTickets | Facility | MaintenanceTicket | One-to-Many | `maintenance_tickets.facility_id` | CASCADE |
| Booking → MaintenanceTickets | Booking | MaintenanceTicket | One-to-Many | `maintenance_tickets.booking_id` | SET NULL |
| MaintenanceTicket → StaffAssignments | MaintenanceTicket | StaffAssignment | One-to-Many | `staff_assignments.maintenance_ticket_id` | CASCADE |
| MaintenanceTicket → MaintenanceLogs | MaintenanceTicket | MaintenanceLog | One-to-Many | `maintenance_logs.maintenance_ticket_id` | CASCADE |
| MaintenanceTicket → Messages | MaintenanceTicket | MaintenanceTicketMessage | One-to-Many | `maintenance_ticket_messages.maintenance_ticket_id` | CASCADE |
| College → Users/Facilities/Signatories | College | User/Facility/Signatory | One-to-Many | `college_id` | Depends on schema constraint |

### Standalone Tables (No Foreign Key Relationships)

| Table | Purpose |
|---|---|
| `equipment` | Equipment inventory (quantities are stored with request data rather than linked per booking) |
| `form_controls` | Control number sequence tracking for GSU forms |
| `sessions` | Laravel session storage |
| `cache`, `jobs` | Laravel cache and queue tables |

### Cardinality Summary

```
User (1) ───────── (∞) Booking
User (1) ───────── (∞) MaintenanceTicket
User (1) ───────── (∞) StaffAssignment
User (1) ───────── (∞) MaintenanceLog
User (1) ───────── (∞) Notification
User (1) ───────── (∞) FormSubmission

Facility (∞) ───── (∞) Booking  [booking_facility pivot]
Facility (1) ───── (∞) MaintenanceTicket

Booking (1) ────── (∞) MaintenanceTicket  [nullable, SET NULL on delete]

MaintenanceTicket (1) ── (∞) StaffAssignment
MaintenanceTicket (1) ── (∞) MaintenanceLog
MaintenanceTicket (1) ── (∞) MaintenanceTicketMessage

Equipment ──── (standalone)
FormControl ── (standalone)
```
