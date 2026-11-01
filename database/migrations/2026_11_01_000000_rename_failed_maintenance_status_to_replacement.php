<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // "Failed" repair tickets are now "Replacement": the item couldn't be repaired and must be replaced
    public function up(): void
    {
        DB::table('maintenance_tickets')->where('status', 'failed')->update(['status' => 'replacement']);
    }

    public function down(): void
    {
        DB::table('maintenance_tickets')->where('status', 'replacement')->update(['status' => 'failed']);
    }
};
