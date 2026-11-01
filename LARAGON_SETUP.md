# Local Setup with Laragon

This guide runs the Laravel web application on Windows using Laragon's PHP and MySQL services.

## 1. Start services and install dependencies

Start MySQL from Laragon, then open a terminal in the repository:

```powershell
composer install
npm install
```

## 2. Configure the environment and database

Copy `.env.example` to `.env` using File Explorer or:

```powershell
copy .env.example .env
```

Create an empty database in phpMyAdmin or the MySQL client. Set the matching connection values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facility_management
DB_USERNAME=your_local_mysql_user
DB_PASSWORD=your_local_mysql_password
```

Generate the Laravel key, apply migrations, and seed development data:

```powershell
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

## 3. Run the application

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Visit `http://127.0.0.1:8000`. Use only development/sample accounts in this local environment; see [TEST_ACCOUNTS.md](TEST_ACCOUNTS.md).

## Useful commands

```powershell
php artisan test
php artisan route:list
php artisan optimize:clear
```

For private receipt storage and production web-server configuration, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md). Avoid using default database passwords or seeded credentials on a shared or production host.
