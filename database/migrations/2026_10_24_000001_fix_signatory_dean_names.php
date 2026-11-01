<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix Issue #8: CEA dean was incorrectly named "CCIS Dean"
     * and CMG dean was incorrectly named "CBA Dean".
     */
    public function up(): void
    {
        DB::table('signatories')
            ->where('type', 'dean')
            ->where('unit', 'CEA')
            ->where('name', 'CCIS Dean')
            ->update(['name' => 'CEA Dean']);

        DB::table('signatories')
            ->where('type', 'dean')
            ->where('unit', 'CMG')
            ->where('name', 'CBA Dean')
            ->update(['name' => 'CMG Dean']);
    }

    public function down(): void
    {
        DB::table('signatories')
            ->where('type', 'dean')
            ->where('unit', 'CEA')
            ->where('name', 'CEA Dean')
            ->update(['name' => 'CCIS Dean']);

        DB::table('signatories')
            ->where('type', 'dean')
            ->where('unit', 'CMG')
            ->where('name', 'CMG Dean')
            ->update(['name' => 'CBA Dean']);
    }
};
