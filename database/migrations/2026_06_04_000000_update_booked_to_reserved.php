<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update legacy bookings
        DB::table('bookings')
            ->where('status', 'booked')
            ->update(['status' => 'reserved']);

        // Update legacy form submissions if any used "booked"
        DB::table('form_submissions')
            ->where('status', 'booked')
            ->update(['status' => 'reserved']);
    }

    public function down(): void
    {
        // Optional: revert if you really need to
        DB::table('bookings')
            ->where('status', 'reserved')
            ->update(['status' => 'booked']);

        DB::table('form_submissions')
            ->where('status', 'reserved')
            ->update(['status' => 'booked']);
    }
};
