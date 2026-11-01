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
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('payment_attachment')->nullable()->after('payload');
            $table->string('payment_status')->default('not_required')->after('payment_attachment')
                ->comment('not_required, pending_payment, payment_uploaded, payment_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['payment_attachment', 'payment_status']);
        });
    }
};
