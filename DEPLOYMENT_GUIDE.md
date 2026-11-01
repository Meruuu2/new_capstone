# UA Facility Management System — Deployment Guide

This is a Laravel web application. A normal deployment runs PHP/Laravel, MySQL, and a web server; desktop or mobile wrappers are optional clients and are not required to operate the web system.

## Requirements

- PHP 8.2+ with the extensions required by Laravel and the Composer dependencies.
- Composer and Node.js/npm for installing dependencies and building assets.
- MySQL or compatible MariaDB.
- Nginx or Apache configured to serve the repository's `public/` directory over HTTPS.
- LibreOffice headless if the deployment must convert generated DOCX files to PDF.

## Configure the environment

Create a production `.env` from `.env.example` and set a unique application key, database credentials, HTTPS application URL, mail configuration, and any deployment-specific storage settings. Keep `.env` out of source control and do not reuse sample or development credentials.

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=<generate-a-unique-key-with-artisan>
HASH_DRIVER=argon2id
HASH_VERIFY=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<production-database>
DB_USERNAME=<least-privilege-database-user>
DB_PASSWORD=<database-password>

MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=<smtp-port>
MAIL_USERNAME=<smtp-user>
MAIL_PASSWORD=<smtp-password>
MAIL_FROM_ADDRESS=<verified-sender>
```

The `FMS_LOGIN_URL` and `FMS_ACCESS_TOKEN` variables remain in `.env.example` for legacy portal-entry integrations. The current `routes/web.php` does not register `/fms-portal-entry`, and `LoginAccessMiddleware` is not registered in the current middleware configuration. The normal web workflow is `/login` with Laravel sessions and role middleware; do not treat those legacy variables as active protection for current web routes. If maintaining a separate integration, verify its route and middleware in that deployment, then use a unique secret over HTTPS. Do not publish tokens in documentation, source, launcher binaries, or screenshots.

## Deploy Laravel

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Generate `APP_KEY` with `php artisan key:generate` only for a new installation; preserve the existing key during routine deploys or encrypted/session data may become unreadable. Configure PHP-FPM/web-server permissions so `storage/` and `bootstrap/cache/` are writable by the application service user. Point the virtual host document root at `public/`, enable TLS, and ensure `APP_DEBUG=false`.

Run `php artisan db:seed` only when intentionally setting up development/sample data. Do not seed sample accounts into production.

The application uses Argon2id so passwords up to 94 characters are supported. `HASH_VERIFY=false` allows existing bcrypt password hashes to be verified and rehashed automatically at successful login; retain this setting until legacy accounts have migrated.

## WebSocket broadcasting

Laravel Reverb is the configured broadcaster, and Laravel Echo is included in the Vite bundle. Set unique `REVERB_APP_ID`, `REVERB_APP_KEY`, and `REVERB_APP_SECRET` values through the deployment secret store. Set `REVERB_HOST`, `REVERB_PORT`, and `REVERB_SCHEME` to the public WebSocket endpoint, and restrict `REVERB_ALLOWED_ORIGINS` to the deployment's hostnames (host only, comma-separated).

Run the Reverb server under Supervisor or systemd, binding it to loopback behind the TLS-terminating web server:

```bash
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Configure Nginx/Apache to proxy WebSocket upgrades from the public WSS endpoint to that process. Keep the Reverb app secret private and allow the configured public WebSocket port through the firewall only where required. Developers can start Reverb alongside the other local services with `composer run dev`. Repair-ticket messages are broadcast to authorized private channels; the conversation UI retains polling as a fallback when a WebSocket connection is unavailable.

### Single ngrok tunnel for local testing

When a single ngrok HTTP tunnel must serve both Laravel and Reverb, run the included Node proxy on port `8000`. It forwards normal requests to Laravel on port `8011`, Reverb WebSocket handshakes under `/app/` to port `8080`, and signed Reverb publishing requests under `/apps/` to port `8080`. Point the single ngrok tunnel at port `8000`. Visitors only open the ngrok website; they do not install anything.

Set the browser-facing `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`, and `REVERB_ALLOWED_ORIGINS` to include the ngrok hostname (hostnames only in `REVERB_HOST` and `REVERB_ALLOWED_ORIGINS`). Set `REVERB_BROADCAST_HOST=127.0.0.1`, `REVERB_BROADCAST_PORT=8080`, and `REVERB_BROADCAST_SCHEME=http` so Laravel publishes directly to the local Reverb server. Build assets again after changing `VITE_REVERB_*`, then run the app with `composer run dev:ngrok`; the existing ngrok HTTP tunnel should point to port `8000`.

This proxy is for local development/testing only. For production, use a TLS-terminating reverse proxy such as Nginx in front of Laravel and Reverb, and restrict Reverb origins to the application's real hostname.

## Audit trail

Apply the `audit_logs` migration during deployment. Admins can review and filter changes at `/admin/audit-logs`. Audit rows are append-only through the application UI, include actor/request metadata and field diffs, and redact passwords, contact details, submitted payloads, and message/file contents.

## Private receipts and supporting files

New receipt and attachment uploads use the private local payment-receipts disk, outside the public web root. They are streamed by authorization-checked application routes; do not create a public symlink or web-server alias for this storage.

For durable production storage, configure a persistent directory outside the release directory:

```env
PAYMENT_RECEIPTS_PATH=/var/lib/ua-fms/payment-receipts
```

Create it with ownership for the PHP-FPM user and restrictive permissions. Back up this directory together with the database. Keep legacy receipt files available to the authorized download path until they have been migrated and verified.

The form accepts a receipt and up to 10 supporting files, each up to 10 MiB. Set PHP and web-server request limits to allow the combined maximum payload plus multipart overhead; the existing deployment guide configuration uses at least `120M` for `post_max_size` and the Nginx client body limit, with `upload_max_filesize=10M` and `max_file_uploads>=11`.

## Background processes and monitoring

The project configures database-backed queue/session/cache drivers in `.env.example`. If the deployed features enqueue queued jobs, run and supervise a Laravel queue worker.

Laravel's scheduler runs the hourly `bookings:normalize-and-complete` task. Configure the system cron to invoke the scheduler every minute from the release directory:

```cron
* * * * * cd /path/to/ua-fms && php artisan schedule:run >> /dev/null 2>&1
```

Monitor Laravel logs, disk space, database health, and the private upload directory. Confirm that outbound mail works if guest email notifications are expected.

## Post-deploy verification

1. Confirm the homepage, login, and health check respond over HTTPS.
2. Sign in with one account per role and verify role-restricted pages.
3. Submit a test guest facility request and confirm the code/last-name tracking path.
4. Submit and update a maintenance ticket; verify its `RM-…` tracker lookup.
5. Test a request approval in a controlled environment and confirm the reservation is created only when facilities are available.
6. Verify private receipt downloads are available only through the authorized application flow.
7. Run `php artisan test` in a separate test environment, not against production data.

## Legacy clients

Older desktop launcher, Electron, and Android documentation is retained under `legacy/` and `android/`. Treat those clients as optional legacy material and verify their compatibility and authentication path separately before distributing them.

See [QUICKSTART.md](QUICKSTART.md) for local development and [TEST_ACCOUNTS.md](TEST_ACCOUNTS.md) for safe development test data.
