# Database Model Documentation — UA Facility Management System

---

## 1. `User`
**File:** `app/Models/User.php`
**Table:** `users`

### Description
The central authentication model. Extends Laravel's `Authenticatable` base. Represents staff accounts and the shared non-interactive walk-in account used for public submissions. `last_name` is stored separately from `name` so status lookups do not infer a surname from full-name formats.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `name` | string | required | Full name |
| `last_name` | string(120) | nullable for legacy accounts; required when creating/updating an account | Exact name value used for public status verification; not parsed from `name` |
| `email` | string | required, unique | Login email |
| `email_verified_at` | timestamp | nullable | Email verification timestamp |
| `password` | string | required, hashed | Argon2id-hashed password; legacy bcrypt hashes remain verifiable during migration |
| `remember_token` | string | nullable | "Remember me" session token |
| `role` | string | default `'viewer'` | Roles include `admin`, `college_staff`, `org_staff`, `guest`, and legacy/general `viewer`; public guests do not sign in |
| `college_name` | string | nullable | College affiliation (used when `role = college_staff`) |
| `organization_name` | string | nullable | Organization affiliation (used when `role = org_staff`) |
| `college_id` | bigint | nullable FK → `colleges.id` | Linked college for scoped staff data |
| `position` | string | nullable | Staff position used by team workflows |
| `contact_number` | string | nullable | Staff contact number |
| `is_active` | boolean | default `true` | Active-account gate; deactivation preserves the user and history |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`name`, `last_name`, `contact_number`, `email`, `password`, `role`, `college_name`, `college_id`, `organization_name`, `position`

The additive migration leaves `last_name` null for existing accounts; legacy lookups continue using the former full-name matching until the account is updated with its explicit tracking last name.

### Hidden
`password`, `remember_token`

### Casts
- `email_verified_at` → `datetime`
- `password` → `hashed`
- `is_active` → `boolean`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `bookings()` | hasMany | `Booking` | `requester_id` | Bookings requested by this user |
| `maintenanceTickets()` | hasMany | `MaintenanceTicket` | `requester_id` | Maintenance tickets filed by this user |
| `staffAssignments()` | hasMany | `StaffAssignment` | `staff_id` | Maintenance tasks assigned to this staff member |
| `maintenanceLogs()` | hasMany | `MaintenanceLog` | `staff_id` | Work logs recorded by this staff member |
| `notifications()` | hasMany | `Notification` | `user_id` | Notifications for this user |
| `college()` | belongsTo | `College` | `college_id` | The user's college, when assigned |

### Traits
`HasFactory`, `Notifiable`

---

## 2. `Facility`
**File:** `app/Models/Facility.php`
**Table:** `facilities`

### Description
Represents a bookable university facility (e.g., halls, AVRs, gyms, track oval). Facilities can be owned by GSU (university-wide) or by a specific college.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `name` | string | required | Facility name (e.g., "BUSALIAN HALL") |
| `location` | string | nullable | Campus/building location |
| `owner_type` | string | default `'gsu'` | Ownership: `gsu`, `college`, or `org` |
| `owner_college` | string | nullable | College name (for college-owned facilities) |
| `college_id` | bigint | nullable FK → `colleges.id` | Linked college for scoped facilities |
| `description` | text | nullable | Description of the facility |
| `is_active` | boolean | default `true` | Whether the facility is active/visible |
| `availability_status` | string(20) | default `'available'` | One of: `available`, `unavailable`, `maintenance` |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`name`, `location`, `owner_type`, `owner_college`, `college_id`, `description`, `is_active`, `availability_status`

### Casts
- `is_active` → `boolean`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `bookings()` | belongsToMany | `Booking` | `booking_facility` pivot | All reservations including this facility |
| `maintenanceTickets()` | hasMany | `MaintenanceTicket` | `facility_id` | Maintenance tickets filed for this facility |
| `college()` | belongsTo | `College` | `college_id` | Linked college, when present |

### Custom Methods
| Method | Returns | Description |
|---|---|---|
| `isCoreFacility()` | `bool` | Returns `true` when the name matches the configured core-facility list. |

### Traits
`HasFactory`

---

## 3. `Equipment`
**File:** `app/Models/Equipment.php`
**Table:** `equipment`

### Description
Represents equipment items that can be requested alongside facility bookings (e.g., monobloc chairs, tables, fans, rostrums, sound systems, LED displays).

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `name` | string | required | Equipment name (e.g., "Monobloc chair") |
| `category` | string | required | Category label (e.g., "seating", "sound") |
| `total_quantity` | integer | default `0` | Total available quantity |
| `is_active` | boolean | default `true` | Whether the equipment is active/available |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`name`, `category`, `total_quantity`, `is_active`

### Casts
- `total_quantity` → `integer`
- `is_active` → `boolean`

### Relationships
None defined.

### Traits
`HasFactory`

---

## 4. `Booking`
**File:** `app/Models/Booking.php`
**Table:** `bookings`

### Description
Represents a facility reservation. A request may create one booking per event day linked to one or more facilities through the pivot table; admins may also create high-priority direct reservations. The public tracker identifies bookings by `booking_code` and requires the requester's tracking last name.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `requester_id` | bigint | FK → `users.id`, cascade delete | User who requested the booking |
| `start_time` | datetime | required | Booking start date/time |
| `end_time` | datetime | required | Booking end date/time |
| `requester_type` | string | required | Type of requester: `college` or `org` |
| `requester_unit` | string | nullable | Unit name (e.g., "College of Engineering", "Student Council") |
| `status` | string | default `'pending'` | Current reservation states include `pending`, `reserved`, `rescheduled`, `cancelled`, and `completed`; legacy values may still exist during normalization. |
| `request_method` | string | nullable | How the request was made (e.g., `online_form`) |
| `purpose` | text | required | Purpose/reason for the booking |
| `additional_details` | text | nullable | JSON-encoded extra data (equipment quantities, reschedule notes, cancel reasons) |
| `requested_at` | datetime | nullable | When the original request was submitted |
| `approved_at` | datetime | nullable | When the booking was approved |
| `booking_code` | string | unique | Public tracking reference (e.g., `BKG-20260521143000-5`) |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`requester_id`, `start_time`, `end_time`, `requester_type`, `requester_unit`, `status`, `request_method`, `purpose`, `additional_details`, `requested_at`, `approved_at`, `booking_code`

### Casts
- `start_time` → `datetime`
- `end_time` → `datetime`
- `requested_at` → `datetime`
- `approved_at` → `datetime`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `requester()` | belongsTo | `User` | `requester_id` | The user who made the booking |
| `facilities()` | belongsToMany | `Facility` | `booking_facility.booking_id` | Facilities included in this reservation |
| `maintenanceTickets()` | hasMany | `MaintenanceTicket` | `booking_id` | Maintenance tickets associated with this booking |

The `booking_facility` pivot is the many-to-many association and supports reservations covering multiple facilities. The legacy `bookings.facility_id` column was removed by migration `2026_05_24_132456_remove_facility_id_from_bookings_table.php`. For a booking created from an FM request, the linked submission's `requester_last_name` is also used for exact status verification, including walk-in guest bookings attached to a shared account.

### Traits
`HasFactory`

---

## 5. `MaintenanceTicket`
**File:** `app/Models/MaintenanceTicket.php`
**Table:** `maintenance_tickets`

### Description
Represents a repair/maintenance request for a facility. Can optionally be linked to a specific booking. Tracks the maintenance lifecycle and has a public `ticket_code` used by the homepage status tracker; lookup also requires the requester's last name.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `ticket_code` | string | nullable, unique | Public maintenance tracking code (`RM-…`) |
| `facility_id` | bigint | nullable FK → `facilities.id`, cascade delete | Listed facility needing maintenance; null when the requester enters an unlisted place |
| `facility_other` | string(160) | nullable | Requester's typed location when no listed facility applies |
| `requester_id` | bigint | FK → `users.id`, cascade delete | User who filed the ticket |
| `booking_id` | bigint | FK → `bookings.id`, nullable, null on delete | Related booking (if applicable) |
| `request_method` | string | required | How submitted: `in-person`, `phone`, `email`, `system` |
| `subject` | string | nullable | Short maintenance issue title shown in the public tracker |
| `status` | string | default `'pending'` | Current lifecycle values: `notified`, `pending`, `ongoing`, `success`, `failed` |
| `issue_description` | text | required | Description of the issue/problem |
| `admin_remarks` | text | nullable | Admin notes/remarks |
| `requested_at` | datetime | nullable | When the ticket was submitted |
| `approved_at` | datetime | nullable | When the ticket was approved |
| `completed_at` | datetime | nullable | When the work was completed |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`ticket_code`, `facility_id`, `facility_other`, `requester_id`, `booking_id`, `request_method`, `subject`, `status`, `issue_description`, `admin_remarks`, `requested_at`, `approved_at`, `completed_at`

### Casts
- `requested_at` → `datetime`
- `approved_at` → `datetime`
- `completed_at` → `datetime`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `facility()` | belongsTo | `Facility` | `facility_id` | The facility the ticket is for |
| `requester()` | belongsTo | `User` | `requester_id` | The user who filed the ticket |
| `booking()` | belongsTo | `Booking` | `booking_id` | The related booking (if any) |
| `messages()` | hasMany | `MaintenanceTicketMessage` | `maintenance_ticket_id` | College-ticket conversation messages, ordered by ID |
| `staffAssignments()` | hasMany | `StaffAssignment` | `maintenance_ticket_id` | Staff members assigned to this ticket |
| `maintenanceLogs()` | hasMany | `MaintenanceLog` | `maintenance_ticket_id` | Work logs for this ticket |

### Maintenance ticket conversation model

`MaintenanceTicketMessage` stores the college-to-GSU conversation:

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Message identifier and ordering key |
| `maintenance_ticket_id` | bigint | FK → `maintenance_tickets.id`, cascade delete | Parent ticket |
| `user_id` | bigint | nullable FK → `users.id`, null on delete | Message author, when the account remains available |
| `body` | text | required | Message body |
| `created_at`, `updated_at` | timestamp | auto | Message timestamps |

The conversation is available to GSU admins and members of the ticket's college team. Organization tickets do not use this conversation feature. Tickets in `success` or `failed` status have a read-only conversation.

### Traits
`HasFactory`

---

## 6. `MaintenanceLog`
**File:** `app/Models/MaintenanceLog.php`
**Table:** `maintenance_logs`

### Description
Records the actual work performed on a maintenance ticket. Each log entry represents a unit of work done by a staff member.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `maintenance_ticket_id` | bigint | FK → `maintenance_tickets.id`, cascade delete | Parent ticket |
| `staff_id` | bigint | FK → `users.id`, cascade delete | Staff member who performed the work |
| `work_done` | text | required | Description of work performed |
| `remarks` | text | nullable | Additional notes |
| `logged_at` | timestamp | required | When the work was logged |
| `staff_signature` | string | required | Staff name or code (as signature) |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`maintenance_ticket_id`, `staff_id`, `work_done`, `remarks`, `logged_at`, `staff_signature`

### Casts
- `logged_at` → `datetime`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `maintenanceTicket()` | belongsTo | `MaintenanceTicket` | `maintenance_ticket_id` | The parent ticket |
| `staff()` | belongsTo | `User` | `staff_id` | The staff member who did the work |

### Traits
`HasFactory`

---

## 7. `StaffAssignment`
**File:** `app/Models/StaffAssignment.php`
**Table:** `staff_assignments`

### Description
Links a staff member to a maintenance ticket, representing the assignment of personnel to a repair/maintenance job.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `maintenance_ticket_id` | bigint | FK → `maintenance_tickets.id`, cascade delete | The maintenance ticket |
| `staff_id` | bigint | FK → `users.id`, cascade delete | The assigned staff member |
| `assigned_at` | datetime | required | When the assignment was made |
| `preferred_time` | datetime | nullable | Preferred time for the work |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`maintenance_ticket_id`, `staff_id`, `assigned_at`, `preferred_time`

### Casts
- `assigned_at` → `datetime`
- `preferred_time` → `datetime`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `maintenanceTicket()` | belongsTo | `MaintenanceTicket` | `maintenance_ticket_id` | The parent ticket |
| `staff()` | belongsTo | `User` | `staff_id` | The assigned staff member |

### Traits
`HasFactory`

---

## 8. `FormControl`
**File:** `app/Models/FormControl.php`
**Table:** `form_controls`

### Description
Tracks auto-generated control numbers for GSU forms. Each record stores a unique control number used to identify a specific form document (facilities utilization or repair/maintenance).

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `control_number` | string | unique | Control number in format `GSU-YYYYMMDD-XXXX` |
| `form_type` | string | required | Form type: `facilities` or `repair` |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`control_number`, `form_type`

### Casts
None.

### Relationships
None defined.

### Traits
None (extends base `Model`).

---

## 9. `FormSubmission`
**File:** `app/Models/FormSubmission.php`
**Table:** `form_submissions`

### Description
Tracks a facility utilization submission through its lifecycle from pending to approved/disapproved and reservation. Public FM tracking derives its code from the submission year and ID (`FM-<year>-<id>`); it requires the submitter's last name and does not require a separate tracking-code column.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `type` | string | required | Form type (e.g., `facilities_utilization`, `repair_maintenance`) |
| `requester_id` | bigint | FK → `users.id`, cascade delete | User who submitted the form |
| `requester_type` | string | required | Requester type: `college`, `org`, `admin`, or `guest` |
| `requester_unit` | string | nullable | College name or organization name |
| `status` | string | default `'pending'` | Current workflow values include `pending`, `reserved`, `disapproved`, and `cancelled`; `approved` and `pending_payment` may appear on legacy submissions, and `booked` is normalized to `reserved`. |
| `payload` | json | required | Full form data as JSON (see Payload Structure below) |
| `payment_attachment` | string | nullable | Private storage path for an uploaded receipt |
| `payment_status` | string | default `not_required` | Payment workflow state: `not_required`, `pending_payment`, `payment_uploaded`, or `payment_verified`. A receipt is not required when a public request is first submitted. |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Typical Payload Structure (facilities_utilization)
```json
{
  "control_no": null,
  "date_request": "2026-05-21",
  "requester_name": "College Staff User",
  "requester_unit": "College of Engineering",
  "date_activity": "2026-06-01",
  "time_range": { "start": "08:00", "end": "12:00" },
  "facility_ids": [3, 4],
  "facility_names": ["BUSALIAN HALL", "CEA AVR"],
  "date_end": "2026-06-03",
  "requester_last_name": "User",
  "purpose": "Seminar on AI",
  "equipment": {
    "monobloc_chair": 50,
    "table": 5,
    "electric_fan": 3,
    "rostrum": 1,
    "flag": 0,
    "sound": 1,
    "led": 0
  }
}
```

New internal submissions save the account's explicit `last_name` as `requester_last_name` in the payload. Public guest submissions use `requester_type=guest` and are linked to a shared walk-in account; the visitor's full name and explicitly entered tracking last name (`requester_last_name`) are stored in the payload. New lookups compare that explicit value as a whole after case/whitespace normalization and treating commas/periods as spaces; they do not infer a last name from the full name. Partial/suffix-only matches are rejected. Existing records without an explicit last name retain the legacy full-name matching behavior. A matching FM lookup returns status and relevant schedule details, not requester identity. Approved guest facility submissions may receive temporary signed PDF/payment action URLs. Payment state is stored separately from request status; receipt upload is available after approval when payment is applicable.

### Fillable
`type`, `requester_id`, `requester_type`, `requester_unit`, `status`, `payload`

### Casts
- `payload` → `array`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `requester()` | belongsTo | `User` | `requester_id` | The user who submitted the form |

### Traits
`HasFactory`

---

## 10. `Notification`
**File:** `app/Models/Notification.php`
**Table:** `notifications`

### Description
In-app notifications sent to users about form status changes, booking updates, and other events. Supports read/unread status tracking.

### Schema

| Column | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint (auto) | PK | Primary key |
| `user_id` | bigint | FK → `users.id`, cascade delete, indexed | Recipient user |
| `type` | string | required | Notification type (e.g., `form_pending`, `form_approved`, `form_disapproved`, `booking_rescheduled`, `booking_cancelled`, `booking_created`, `form_pending_admin`) |
| `title` | string | required | Short notification title |
| `message` | text | nullable | Notification body text |
| `data` | json | nullable | Additional data (e.g., `{"submission_id": 1, "booking_id": 3}`) |
| `is_read` | boolean | default `false`, indexed | Whether the user has read this notification |
| `created_at` | timestamp | auto | Record creation time |
| `updated_at` | timestamp | auto | Record update time |

### Fillable
`user_id`, `type`, `title`, `message`, `data`, `is_read`

### Casts
- `data` → `array`
- `is_read` → `boolean`

### Relationships
| Relationship | Type | Related Model | Foreign Key | Description |
|---|---|---|---|---|
| `user()` | belongsTo | `User` | `user_id` | The recipient user |

### Traits
None (extends base `Model`).

---

## 11. `College`
**File:** `app/Models/College.php`
**Table:** `colleges`

Stores college records used to scope users, facilities, and signatories.

| Column | Type | Description |
|---|---|---|
| `id` | bigint (auto) | Primary key |
| `name` | string | College name |
| `short_name` | string | Nullable short label |
| `description` | text | Nullable description |

Relationships: `users()`, `facilities()`, and `signatories()` are `hasMany` relations.

## 12. `Signatory`
**File:** `app/Models/Signatory.php`
**Table:** `signatories`

Stores selectable signatories used by facility-request forms. Fillable fields are `type`, `name`, `unit`, `college_id`, and `is_active`; `is_active` is cast to boolean. `college()` belongs to `College`, and `scopeForCollege()` matches by college ID or legacy unit name.

## 13. `MaintenanceTicketMessage`
**File:** `app/Models/MaintenanceTicketMessage.php`
**Table:** `maintenance_ticket_messages`

Stores messages between a college team and GSU for a maintenance ticket. Fillable fields are `maintenance_ticket_id`, `user_id`, and `body`. `ticket()` and `user()` are `belongsTo` relations. Ticket access is scoped in the controllers; organization tickets do not use this conversation workflow.

## 14. `AuditLog`
**File:** `app/Models/AuditLog.php`
**Table:** `audit_logs`

Stores actor, action, polymorphic subject, redacted before/after field data, request IP/user agent, route, and timestamp. Application model create/update/delete events and authentication login/logout events are recorded. Audit entries are visible through the admin-only `/admin/audit-logs` page and are not editable there.

---

## Entity Relationship Summary

```
College ──┬── hasMany ──→ User
          ├── hasMany ──→ Facility
          └── hasMany ──→ Signatory

User ──┬── hasMany ──→ Booking (requester_id)
       ├── hasMany ──→ MaintenanceTicket (requester_id)
       ├── hasMany ──→ StaffAssignment (staff_id)
       ├── hasMany ──→ MaintenanceLog (staff_id)
       └── hasMany ──→ Notification (user_id)

Facility ──┬── belongsTo ──→ College
           ├── belongsToMany ──→ Booking (booking_facility)
           └── hasMany ──→ MaintenanceTicket (facility_id)

Booking ──── belongsToMany ──→ Facility (booking_facility)
Booking ──── hasMany ──→ MaintenanceTicket (booking_id)

MaintenanceTicket ──┬── hasMany ──→ StaffAssignment (maintenance_ticket_id)
                    └── hasMany ──→ MaintenanceLog (maintenance_ticket_id)
                    └── hasMany ──→ MaintenanceTicketMessage (maintenance_ticket_id)

FormSubmission ──── belongsTo ──→ User (requester_id)
Signatory ──── belongsTo ──→ College
FormControl ──── (standalone)
Equipment ──── (standalone, no relationships)
```
