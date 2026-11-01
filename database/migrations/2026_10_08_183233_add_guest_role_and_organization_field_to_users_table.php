<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // organization_name is already created by 2024_01_01_000000_add_role_and_profile_columns_to_users_table;
        // only add it if this database is missing it.
        if (Schema::hasColumn('users', 'organization_name')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Add organization_name field for guest users
            $table->string('organization_name')->nullable()->after('college_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: organization_name belongs to the 2024 migration, so rolling back here must not drop it.
    }
};
