# UA Facility Management System

A role-based web application for facility requests and reservations, maintenance tickets, and public status tracking at the University of Antique.

## Current capabilities

- Public reservation calendar and status tracker for facility requests (`FM`), bookings (`BKG`), and repair/maintenance tickets (`RM`).
- Guest facility requests without account creation or sign-in.
- College portal for facility requests and repair/maintenance reporting; organization-unit portal for facility requests.
- GSU administration for facilities, request approvals, reservations, users, and maintenance ticket updates.
- Staff accounts can be deactivated without deleting their request and reservation history.
- Account names allow letters, spaces, hyphens, and apostrophes. New passwords require 8–94 characters, uppercase and lowercase letters, and a special character.
- Reservations are limited to 6:00 AM–7:00 PM. Public guest venue choices display the current published rate.
- Admins can search a read-only audit trail of account, reservation, facility, request, and maintenance changes.
- Laravel Reverb and Echo power real-time repair-ticket conversations over authorized private channels, with polling as a fallback.
- Official facility and repair/maintenance form generation.
- Explicit tracking last names for new submissions; the tracker does not guess a surname from a full name. Legacy records without one retain legacy matching.

Public status lookup is read-only and rate-limited. Approved guest facility requests may also expose applicable temporary signed document or payment-receipt actions. A receipt is not required when a guest request is first submitted; payment can be requested during review.

## Technology

- PHP 8.2+ and Laravel 12
- MySQL
- Blade, Tailwind CSS 4, and Vite
- PHPUnit feature and unit tests

## Local setup

Requirements: PHP and Composer, Node.js and npm, and a running MySQL database.

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

Configure the database connection and any mail/storage settings in `.env`, then run:

```bash
php artisan migrate
php artisan db:seed
```

Run `composer run dev` to start the local Laravel server, queue worker, logs, Vite, and Reverb WebSocket server. Open the URL printed by Artisan (normally `http://127.0.0.1:8000`). Do not use seeded sample credentials or development configuration in production.

## Tests

The test suite uses the dedicated MySQL database `ua_fms_testing`; it never targets the application database name from `.env`. Create that empty database and make sure the configured database user can access it before testing. `RefreshDatabase` rebuilds test tables and data.

```bash
mysql -u root -p -e "CREATE DATABASE ua_fms_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

GitHub Actions runs the automated test suite and production asset build on pushes and pull requests to `main`, using a temporary MySQL database.

See [USER_ONBOARDING_GUIDE.md](USER_ONBOARDING_GUIDE.md) for role-based user training, plus [QUICKSTART.md](QUICKSTART.md), [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md), and [TESTING_GUIDE.md](TESTING_GUIDE.md) for setup and workflow details. The [Functional Design Document](fdd.md), [controller reference](controller-documentation.md), [routes and views reference](views-and-routes-documentation.md), [model reference](model-documentation.md), and [architecture/ERD](tech-stack-architecture-erd.md) describe the system.
