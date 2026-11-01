<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migration = $argv[1] ?? '2024_01_01_000007_create_sessions_table';
$exists = app('db')->table('migrations')->where('migration', $migration)->exists();
if ($exists) {
    echo "ALREADY_EXISTS\n";
    exit(0);
}
$maxBatch = app('db')->table('migrations')->max('batch');
$batch = $maxBatch ?? 1;
app('db')->table('migrations')->insert(['migration' => $migration, 'batch' => $batch, 'migration' => $migration]);

echo "INSERTED: $migration (batch=$batch)\n";
