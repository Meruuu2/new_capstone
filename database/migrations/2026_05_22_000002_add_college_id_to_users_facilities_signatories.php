<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete()->after('organization_name');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete()->after('owner_college');
        });

        Schema::table('signatories', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->constrained('colleges')->nullOnDelete()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn('college_id');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn('college_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['college_id']);
            $table->dropColumn('college_id');
        });
    }
};
