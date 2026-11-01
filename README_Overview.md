# UA-FMS project overview

The University of Antique Facility Management System is a Laravel web application for facility utilization requests, reservations, repair/maintenance coordination, and public status tracking.

## User groups

- **Public visitors and guests:** view the public calendar, submit facility requests without an account, and track facility requests, bookings, or repair tickets without signing in.
- **GSU administrators:** manage facilities and users, review requests, manage reservations, and update maintenance tickets.
- **College staff:** manage eligible college facilities, submit utilization and repair requests, and follow their unit's activity.
- **Organization staff:** submit utilization requests and view their own requests and reservations.

## Important workflows

- Facility requests progress through review and approval. On approval, the system checks availability and overlapping reservations before creating a reservation.
- Repair and maintenance tickets use `RM-…` tracking codes and statuses including notified, pending, ongoing, success, and failed.
- The public tracker requires the tracking code and the submitter's last name. New request data stores the last name explicitly, separately from the full name; legacy data without that field retains the legacy matcher.
- College repair tickets are visible across the college team and support a conversation with GSU. Organization tickets are visible to their requester and GSU.

## Technology

PHP 8.2+, Laravel 12, MySQL, Eloquent, Blade, Tailwind CSS 4, and Vite. Official forms are generated from DOCX templates; see [FORMS/FORMREADME.md](FORMS/FORMREADME.md).

See [README.md](README.md) for setup, [fdd.md](fdd.md) for user-facing behavior, [tech-stack-architecture-erd.md](tech-stack-architecture-erd.md) for the architecture, and [TESTING_GUIDE.md](TESTING_GUIDE.md) for verification.
