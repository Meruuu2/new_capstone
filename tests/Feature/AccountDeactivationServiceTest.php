<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\AccountDeactivationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountDeactivationServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deactivation_cancels_upcoming_work_and_preserves_past_history(): void
    {
        $user = User::create([
            'name' => 'Deactivation Test User',
            'email' => 'deactivation-' . uniqid() . '@example.test',
            'password' => 'test-password',
            'role' => 'college_staff',
        ]);

        $futurePayload = [
            'date_activity' => now()->addDays(10)->toDateString(),
            'time_range' => ['start' => '09:00', 'end' => '10:00'],
        ];
        $futureSubmission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'reserved',
            'payload' => $futurePayload,
        ]);
        $futurePendingSubmission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'pending',
            'payload' => $futurePayload,
        ]);
        $pastSubmission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'pending',
            'payload' => [
                'date_activity' => now()->subDays(2)->toDateString(),
                'time_range' => ['start' => '09:00', 'end' => '10:00'],
            ],
        ]);
        $futureBooking = $this->booking($user, 'reserved', now()->addDays(10), [
            'form_submission_id' => $futureSubmission->id,
        ]);
        $pendingBooking = $this->booking($user, 'pending', now()->addDays(12));
        $rescheduledBooking = $this->booking($user, 'rescheduled', now()->addDays(14));
        $pastBooking = $this->booking($user, 'completed', now()->subDays(2));
        $pastReservedBooking = $this->booking($user, 'reserved', now()->subDays(4));

        $result = app(AccountDeactivationService::class)->deactivate($user);

        $this->assertFalse($user->fresh()->is_active);
        $this->assertSame('cancelled', $futureBooking->fresh()->status);
        $this->assertSame('Requester account deactivated', $futureBooking->fresh()->additional_details['cancel_reason']);
        $this->assertSame('cancelled', $pendingBooking->fresh()->status);
        $this->assertSame('cancelled', $rescheduledBooking->fresh()->status);
        $this->assertSame('completed', $pastBooking->fresh()->status);
        $this->assertSame('reserved', $pastReservedBooking->fresh()->status);
        $this->assertSame('cancelled', $futureSubmission->fresh()->status);
        $this->assertSame('cancelled', $futurePendingSubmission->fresh()->status);
        $this->assertSame('pending', $pastSubmission->fresh()->status);
        $this->assertSame(3, $result['bookings']);
        $this->assertSame(2, $result['requests']);
    }

    private function booking(User $user, string $status, \Carbon\Carbon $startTime, array $details = []): Booking
    {
        return Booking::create([
            'requester_id' => $user->id,
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addHour(),
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => $status,
            'request_method' => 'online_form',
            'purpose' => 'Account deactivation test',
            'additional_details' => $details,
            'requested_at' => now(),
            'booking_code' => 'TEST-' . uniqid(),
        ]);
    }
}
