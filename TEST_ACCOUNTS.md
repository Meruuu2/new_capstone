# Development Test Accounts

Development sample accounts are created by the project's seeders (`DatabaseSeeder` and `SampleUsersSeeder`).

```bash
php artisan migrate
php artisan db:seed
```

The seeder creates sample admin, college-staff, and organization-staff users, along with facilities and other test data. Inspect `database/seeders/SampleUsersSeeder.php` for the current local sample-account definitions. Never use sample credentials outside an isolated development/test environment, and never seed sample users into production.

## Account data and tracking names

New staff accounts need a separately recorded `last_name`. It is used as the exact tracking value for facility and maintenance status lookup; the application does not parse it from the account's full name. Older accounts can have a null value until updated, and existing records without explicit tracking surnames retain the legacy matching behavior.

The public guest form does not create a sign-in account. Each guest request stores its own full name and explicit tracking last name and is linked internally to a non-interactive walk-in account.

## Local database account note

Account rows and credentials in an existing local database can differ from seeded defaults because administrators can edit or deactivate users. Seeding is not a reliable way to recreate deleted historical records. Use a disposable local database for evaluator demonstrations and tests.

See [QUICKSTART.md](QUICKSTART.md) for setup and [TESTING_GUIDE.md](TESTING_GUIDE.md) for workflow checks.
