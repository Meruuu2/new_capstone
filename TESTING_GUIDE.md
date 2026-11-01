# Testing Guide — UA Facility Management System

## Automated test suite

Run the full Laravel suite from the repository root:

```bash
mysql -u root -p -e "CREATE DATABASE ua_fms_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test
```

PHPUnit is configured to use the dedicated MySQL database `ua_fms_testing`, not the application database name from `.env`. Create this empty database before running tests and ensure the configured database user can access it. Tests using `RefreshDatabase` rebuild test tables and data, so never change the test database setting to a database with data you need to preserve.

GitHub Actions runs the suite against a temporary MySQL database and builds production frontend assets for pushes and pull requests to `main`.

Relevant feature coverage includes:

- Facility request validation, multi-facility/date reservations, and overlap prevention.
- Public tracking of FM facility requests, BKG bookings, and RM repair/maintenance tickets.
- Exact explicit tracking-last-name matching, rejection of partial surnames, and legacy lookup behavior.
- Guest booking approval, booking creation, signed-out tracking, and the explicit surname in guest email.
- College request submission, administrator approval, booking creation, and signed-out tracking.
- Private payment receipt and supporting attachment authorization and validation.

## Manual smoke test

Start the application and visit `http://127.0.0.1:8000`:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Public and guest flow

1. Confirm the homepage displays the public reservation calendar and status tracker.
2. Open `/request-facility` and submit a test request with a full name and a separately entered tracking last name.
3. Note the `FM-…` code on the confirmation and use the code plus the exact last name in the homepage tracker.
4. Confirm no sign-in is required and an incorrect or partial surname does not return the record.
5. After GSU approval, confirm any guest PDF or payment actions appear only when applicable.

### College and organization flows

1. Sign in using an isolated seeded/test account for the intended role.
2. Confirm the account has an explicit last name in its profile.
3. Submit a facility request and verify the saved request's tracker uses that account value.
4. Submit a repair/maintenance ticket, note its `RM-…` code, and test signed-out lookup using the account's exact tracking last name.
5. For college tickets, verify team visibility and the conversation with GSU.

### GSU review and reservation

1. Sign in as an administrator and open the facilities request queue.
2. Approve a valid pending request.
3. Confirm the request transitions to reserved and a booking is created only if facilities are available and no overlapping reservation exists.
4. Check the resulting booking from the public tracker using the booking code and the correct submitter last name.
5. Open the maintenance ticket queue, update a test ticket's status/remarks, and confirm the expected in-app notifications.

## Manual UI verification

For UI changes, use Playwright to inspect the affected page at desktop and narrow/mobile widths. Check form labels, validation feedback, status text, and whether content overflows or clips.

## Data safety

Use disposable records and an isolated development database for manual checks. Do not run seeders or destructive test workflows against production data. Keep local uploads and test artifacts out of commits.
