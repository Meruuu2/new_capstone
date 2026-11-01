<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class OtherEquipmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_other_equipment_input_is_cleaned_and_listed_with_the_standard_items(): void
    {
        $others = FormSubmission::otherEquipmentFrom(['other_equipment' => [
            ['name' => '  Extension cord ', 'qty' => '3'],
            ['name' => '', 'qty' => '5'],          // no name: dropped
            ['name' => 'Projector screen'],        // no qty: counts as 1
            'not-a-row',
        ]]);

        $this->assertSame([
            ['name' => 'Extension cord', 'qty' => 3],
            ['name' => 'Projector screen', 'qty' => 1],
        ], $others);

        $items = FormSubmission::equipmentItems([
            'equipment' => ['monobloc_chair' => 20, 'table' => 0],
            'equipment_others' => $others,
        ]);

        $this->assertSame([
            ['label' => 'Monobloc chair', 'qty' => 20],
            ['label' => 'Extension cord', 'qty' => 3],
            ['label' => 'Projector screen', 'qty' => 1],
        ], $items->all());
    }

    public function test_other_equipment_rules_reject_bad_quantities_and_too_many_rows(): void
    {
        $rules = FormSubmission::OTHER_EQUIPMENT_RULES;

        $this->assertFalse(Validator::make(['other_equipment' => [['name' => 'Cord', 'qty' => 2]]], $rules)->fails());
        $this->assertTrue(Validator::make(['other_equipment' => [['name' => 'Cord', 'qty' => 0]]], $rules)->fails());
        $this->assertTrue(Validator::make(['other_equipment' => [['name' => str_repeat('x', 101), 'qty' => 1]]], $rules)->fails());
        $this->assertTrue(Validator::make(['other_equipment' => array_fill(0, 11, ['name' => 'Cord', 'qty' => 1])], $rules)->fails());
    }

    public function test_approved_request_carries_other_equipment_onto_the_booking(): void
    {
        $user = User::create([
            'name' => 'Other Equipment User', 'last_name' => 'User',
            'email' => 'other-equipment-' . uniqid() . '@example.test',
            'password' => 'test-password', 'role' => 'college_staff',
        ]);
        $facility = Facility::create([
            'name' => 'Other Equipment Venue ' . uniqid(), 'owner_type' => 'gsu',
            'is_active' => true, 'availability_status' => 'available',
        ]);
        $submission = FormSubmission::create([
            'type' => 'facilities_utilization', 'requester_id' => $user->id, 'requester_type' => 'college',
            'requester_unit' => 'Test College', 'status' => 'pending',
            'payload' => [
                'facility_id' => $facility->id, 'facility_ids' => [$facility->id],
                'date_activity' => now()->addDays(60)->toDateString(), 'date_request' => now()->toDateString(),
                'time_range' => ['start' => '09:00', 'end' => '11:00'], 'purpose' => 'Other equipment test',
                'equipment' => ['monobloc_chair' => 10],
                'equipment_others' => [['name' => 'Extension cord', 'qty' => 3]],
            ],
        ]);

        $notifications = \Mockery::mock(NotificationService::class);
        $notifications->shouldReceive('notifyBookingCreated');
        $booking = (new BookingService($notifications))->createFromSubmission($submission);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertSame([['name' => 'Extension cord', 'qty' => 3]], $booking->fresh()->additional_details['equipment_others']);
        $this->assertContains('3 × Extension cord', $submission->fresh()->ticketData()['equipment']->all());
    }
}
