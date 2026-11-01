<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->string('ticket_code')->nullable()->unique();
            $table->string('subject')->nullable();
        });

        DB::table('maintenance_tickets')->where('status', 'approved')->update(['status' => 'pending']);
        DB::table('maintenance_tickets')->where('status', 'in_progress')->update(['status' => 'ongoing']);
        DB::table('maintenance_tickets')->where('status', 'completed')->update(['status' => 'success']);
        DB::table('maintenance_tickets')->where('status', 'rejected')->update(['status' => 'failed']);
    }

    public function down(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropUnique(['ticket_code']);
            $table->dropColumn(['ticket_code', 'subject']);
        });
    }
};