<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            // "Others": a place that isn't on the facility list, typed by the requester (then facility_id is empty)
            $table->foreignId('facility_id')->nullable()->change();
            $table->string('facility_other', 160)->nullable()->after('facility_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropColumn('facility_other');
        });

        // Only make facility_id required again when no "Others" ticket would break it
        if (! DB::table('maintenance_tickets')->whereNull('facility_id')->exists()) {
            Schema::table('maintenance_tickets', function (Blueprint $table) {
                $table->foreignId('facility_id')->nullable(false)->change();
            });
        }
    }
};
