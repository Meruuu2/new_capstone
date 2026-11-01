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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // facility_utilization, maintenance, etc.
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->string('requester_type'); // college, org, admin
            $table->string('requester_unit')->nullable(); // college_name / organization_name
            $table->string('status')->default('pending'); // pending, approved, disapproved, cancelled, booked
            $table->json('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
