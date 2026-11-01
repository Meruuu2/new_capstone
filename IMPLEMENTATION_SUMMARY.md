# Current System Implementation Summary

## Application

UA-FMS is a PHP 8.2+/Laravel 12 web application backed by MySQL, with server-rendered Blade pages, Tailwind CSS 4, and Vite assets.

## Implemented workflows

- Public facility calendar and homepage status tracker.
- Facility utilization requests from college staff, organization staff, and unauthenticated guests.
- GSU request review and reservation creation with availability and overlap checks.
- Admin, college, and organization portals with role-restricted routes.
- Repair/maintenance tickets with public status lookup. GSU can update ticket status and remarks; college repair tickets additionally support team visibility and a GSU conversation.
- Separate, explicit tracking last names for new accounts and guest requests. Lookup does not parse a surname from a full name; older records without explicit values continue using legacy matching.
- Guest payment-receipt uploads and signed document links where allowed.
- Official facilities and repair/maintenance document generation.

## Verification

Automated unit and feature tests are run with:

```bash
php artisan test
```

Verified on October 26, 2026: `php artisan test` passed with 38 tests and 174 assertions. Re-run the suite after code changes because results can change.

See [README.md](README.md) for setup and [the system documentation](documentation.md) for detailed UI and workflow references.
