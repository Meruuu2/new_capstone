<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasTable('notifications')) {
    echo "TABLE_ALREADY_EXISTS\n";
    exit(0);
}

Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('type');
    $table->string('title');
    $table->text('message')->nullable();
    $table->json('data')->nullable();
    $table->boolean('is_read')->default(false);
    $table->timestamps();
    $table->index('user_id');
    $table->index('is_read');
});

echo "TABLE_CREATED\n";
