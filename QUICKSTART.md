# Quick Start — UA Facility Management System

## Requirements

- PHP 8.2 or later and Composer
- MySQL or a compatible MariaDB server
- Node.js and npm for compiling frontend assets

## Install and configure

From the repository root:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Edit `.env` with the local MySQL database name, username, and password. Create the database in MySQL before running migrations.

```bash
php artisan migrate
php artisan db:seed
npm run build
```

Start the local application services, including Reverb for WebSocket chat:

```bash
composer run dev
```

Open the URL printed by Artisan (normally `http://127.0.0.1:8000`). This runs the Laravel server, queue worker, Vite, and Reverb. The homepage exposes the public calendar, guest facility request form, and public status tracker.

## Roles and test data

The database seeder creates sample admin, college, organization, facility, and other development data. Test accounts are for local development only; do not deploy seeded credentials or sample data. See [TEST_ACCOUNTS.md](TEST_ACCOUNTS.md).

Staff sign in through the role-appropriate portal and need an active account with a separately recorded last name to submit new facility or maintenance requests. Guests submit facility requests without signing in.

## Run tests

```bash
php artisan test
```

## Common commands

```bash
php artisan migrate              # Apply database migrations
php artisan db:seed              # Seed development/sample data
php artisan route:list           # Inspect registered routes
php artisan optimize:clear       # Clear cached Laravel configuration/views/routes
npm run build                    # Compile production frontend assets
```

For production configuration, private upload storage, and web-server setup, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md). For Laragon-specific setup, see [LARAGON_SETUP.md](LARAGON_SETUP.md).

## Notes

- Public tracking uses a code plus the submitter's explicitly recorded last name. Do not guess or derive that value from the full name.
- The old guest sign-in portal is retired. Legacy `/guest/...` URLs redirect to the public request workflow.
- Desktop/mobile wrapper documentation under `legacy/` describes optional or historical clients, not a prerequisite for running the web app.
