<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCalendarDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_data_is_shared_and_exposes_details_only_to_staff(): void
    {
        $requester = User::factory()->create(['role' => 'college_staff']);
        $facility = Facility::create([
            'name' => 'Calendar Test Hall',
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);
        $booking = Booking::create([
            'requester_id' => $requester->id,
            'start_time' => '2027-02-11 09:00:00',
            'end_time' => '2027-02-11 11:00:00',
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'reserved',
            'purpose' => 'Annual planning session',
            'booking_code' => 'CALENDAR-TEST-001',
        ]);
        $booking->facilities()->attach($facility->id);

        $this->getJson(route('calendar.data', ['month' => '2027-02']))
            ->assertOk()
            ->assertJsonPath('days.2027-02-11.count', 1)
            ->assertJsonPath('days.2027-02-11.items.0.facility', 'Calendar Test Hall')
            ->assertJsonPath('days.2027-02-11.items.0.time', '9:00 AM – 11:00 AM')
            ->assertJsonMissingPath('days.2027-02-11.items.0.event');

        $this->actingAs($requester)
            ->getJson(route('calendar.data', ['month' => '2027-02']))
            ->assertOk()
            ->assertJsonPath('days.2027-02-11.items.0.event', 'Annual planning session')
            ->assertJsonPath('days.2027-02-11.items.0.who', 'College · Test College');
    }

    public function test_portal_calendar_pages_render_from_the_shared_booking_query(): void
    {
        $requester = User::factory()->create(['role' => 'college_staff']);
        $facility = Facility::create([
            'name' => 'Calendar Page Hall',
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);
        $booking = Booking::create([
            'requester_id' => $requester->id,
            'start_time' => '2027-02-12 09:00:00',
            'end_time' => '2027-02-12 11:00:00',
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'reserved',
            'purpose' => 'Calendar page test',
            'booking_code' => 'CALENDAR-PAGE-001',
        ]);
        $booking->facilities()->attach($facility->id);

        foreach ([
            ['college_staff', 'college.calendar'],
            ['org_staff', 'org.bookings.calendar'],
            ['admin', 'admin.calendar'],
        ] as [$role, $route]) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route($route, ['month' => '2027-02']))
                ->assertOk()
                ->assertSee('Calendar Page Hall');
        }
    }
}
