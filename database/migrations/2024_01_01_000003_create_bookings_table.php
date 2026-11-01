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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();

            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->string('requester_type'); // college, org
            $table->string('requester_unit')->nullable(); // "College of Engineering", "Supreme Student Council"

            $table->string('status')->default('pending'); 
            // pending, accepted, denied, rescheduled, cancelled

            $table->string('request_method')->nullable(); // online form (default)
            $table->text('purpose');
            $table->text('additional_details')->nullable();

            $table->dateTime('requested_at')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->string('booking_code')->unique(); // reference number

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
