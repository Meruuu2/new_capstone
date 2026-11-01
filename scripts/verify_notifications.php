<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$dbDefault = config('database.default');
$dbName = config('database.connections.' . $dbDefault . '.database');
$host = config('database.connections.' . $dbDefault . '.host');
$user = config('database.connections.' . $dbDefault . '.username');

echo "DB_CONNECTION=" . $dbDefault . "\n";
echo "DB_DATABASE=" . $dbName . "\n";
echo "DB_HOST=" . $host . "\n";
echo "DB_USER=" . $user . "\n";

if (Schema::hasTable('notifications')) {
    echo "TABLE_EXISTS=true\n";
    try {
        $count = app('db')->table('notifications')->count();
        echo "ROW_COUNT=" . $count . "\n";
    } catch (Exception $e) {
        echo "COUNT_ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "TABLE_EXISTS=false\n";
}

$tables = app('db')->select("SHOW TABLES");
echo "TABLE_LIST:\n";
foreach ($tables as $t) {
    $vals = (array) $t;
    echo implode(', ', $vals) . "\n";
}
