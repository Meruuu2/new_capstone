<?php
// run-migrations.php - Run this to execute the database migrations

shell_exec('php artisan migrate');
echo "Migrations completed!\n";
