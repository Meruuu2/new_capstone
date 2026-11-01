<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = app('db')->select('SHOW TABLES');
echo json_encode($tables, JSON_PRETTY_PRINT);
