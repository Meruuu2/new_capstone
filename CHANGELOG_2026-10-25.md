# Changelog — October 25, 2026

## Public homepage tracker

- Added public status lookup for facility requests (`FM`), bookings (`BK` / `BKG-…`) and maintenance tickets (`RM`).
- Removed sign-in as a prerequisite for checking status. Every lookup requires both the tracking code and the last name of the person who submitted the record.
- Kept results read-only and prevented requester identity from being returned. Approved guest facility requests retain their existing signed receipt/PDF actions where applicable.
- Reworked the search card into a readable two-row desktop layout that stacks on narrow screens.
- Added feature coverage for public lookups and surname validation.

## Verification

- Laravel test suite: 32 tests passed, 135 assertions.
- Blade view cache compilation and `git diff --check` passed.
- Production Vite build succeeded.
- Playwright confirmed the required surname field and checked the tracker layout in the browser.
