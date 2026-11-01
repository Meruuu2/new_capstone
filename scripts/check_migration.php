<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$migration = '2026_05_19_000001_create_notifications_table';
$result = app('db')->table('migrations')->where('migration', $migration)->get();
if ($result->isEmpty()) {
	echo "MIGRATION_RECORD_NOT_FOUND\n";
	exit(0);
}
echo "MIGRATION_RECORD_FOUND: ";
echo $result->first()->migration . " (batch=" . $result->first()->batch . ")\n";
