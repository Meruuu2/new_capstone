<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FacilitiesFormPdfController;
use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\User;
use App\Services\BookingService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MultiFacilityRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_single_and_multiple_facility_requests_reserve_all_requested_days(): void
    {
        $user = $this->user();
        $notifications = \Mockery::mock(NotificationService::class);
        $notifications->shouldReceive('notifyBookingCreated')->times(4);
        $service = new BookingService($notifications);

        $cases = [
            ['facility_count' => 1, 'day_count' => 1],
            ['facility_count' => 2, 'day_count' => 1],
            ['facility_count' => 1, 'day_count' => 3],
            ['facility_count' => 2, 'day_count' => 3],
        ];

        foreach ($cases as $index => $case) {
            $facilities = collect(range(1, $case['facility_count']))
                ->map(fn () => $this->facility())
                ->values();
            $startDate = now()->addDays(40 + ($index * 20))->toDateString();
            $endDate = $case['day_count'] > 1
                ? Carbon::parse($startDate)->addDays($case['day_count'] - 1)->toDateString()
                : null;
            $facilityIds = $facilities->pluck('id')->all();
            $submission = $this->submission($user, $facilityIds, $startDate, $endDate);

            $result = $service->createFromSubmission($submission);

            $this->assertInstanceOf(Booking::class, $result);
            $this->assertSame('reserved', $submission->fresh()->status);

            $bookings = Booking::where('requester_id', $user->id)
                ->get()
                ->filter(fn (Booking $booking) => ($booking->additional_details['form_submission_id'] ?? null) === $submission->id)
                ->values();

            $this->assertCount($case['day_count'], $bookings);
            foreach ($bookings as $booking) {
                $this->assertEqualsCanonicalizing(
                    $facilityIds,
                    $booking->facilities->modelKeys()
                );
            }
        }
    }

    public function test_overlapping_reservations_block_submission_but_adjacent_times_remain_available(): void
    {
        $user = $this->user();
        $facility = $this->facility();
        $otherFacility = $this->facility();
        $activityDate = now()->addDays(40)->toDateString();
        $start = Carbon::parse($activityDate . ' 10:00');
        $this->reservedBooking($user, $facility, $start->copy(), $start->copy()->addHour());

        $overlapping = $this->requestValidator([
            'date_activity' => $activityDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'facility_ids' => [$facility->id, $otherFacility->id],
            'purpose' => 'Overlapping time test',
        ]);
        $this->assertTrue($overlapping->fails());
        $this->assertArrayHasKey('start_time', $overlapping->errors()->messages());
        $this->assertStringContainsString($facility->name, $overlapping->errors()->first('start_time'));

        $adjacent = $this->requestValidator([
            'date_activity' => $activityDate,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'facility_ids' => [$facility->id],
            'purpose' => 'Adjacent time test',
        ]);
        $this->assertFalse($adjacent->fails(), 'A reservation ending at the requested start time does not overlap.');

        $immediatelyBefore = $this->requestValidator([
            'date_activity' => $activityDate,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'facility_ids' => [$facility->id],
            'purpose' => 'Immediately before reservation',
        ]);
        $this->assertFalse($immediatelyBefore->fails(), 'A request ending at the reservation start time does not overlap.');

        $unreservedFacility = $this->facility();
        $otherVenue = $this->requestValidator([
            'date_activity' => $activityDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'facility_ids' => [$unreservedFacility->id],
            'purpose' => 'Same time on another facility',
        ]);
        $this->assertFalse($otherVenue->fails(), 'Another facility can be reserved at the same time.');

        $submission = $this->submission($user, [$facility->id], $activityDate);
        $notifications = \Mockery::mock(NotificationService::class);
        $notifications->shouldNotReceive('notifyBookingCreated');

        $result = (new BookingService($notifications))->createFromSubmission($submission);

        $this->assertIsString($result);
        $this->assertStringContainsString('already booked', $result);
        $this->assertSame('pending', $submission->fresh()->status);
    }

    public function test_multi_day_request_is_blocked_when_any_day_overlaps(): void
    {
        $user = $this->user();
        $facility = $this->facility();
        $activityDate = now()->addDays(40)->toDateString();
        $conflictDate = Carbon::parse($activityDate)->addDay();
        $this->reservedBooking(
            $user,
            $facility,
            $conflictDate->copy()->setTime(10, 0),
            $conflictDate->copy()->setTime(11, 0)
        );

        $validator = $this->requestValidator([
            'date_activity' => $activityDate,
            'date_end' => $conflictDate->copy()->addDay()->toDateString(),
            'start_time' => '10:30',
            'end_time' => '12:00',
            'facility_ids' => [$facility->id],
            'purpose' => 'Multi-day overlap test',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString($conflictDate->format('M j, Y'), $validator->errors()->first('start_time'));
    }

    public function test_single_legacy_facility_id_is_normalized_and_thirty_day_notice_is_enforced(): void
    {
        $facility = $this->facility();
        $request = StoreFacilitiesUtilizationRequest::create('/college/requests/facilities', 'POST', [
            'last_name' => 'User',
            'date_activity' => now()->addDays(30)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'facility_id' => $facility->id,
            'purpose' => 'Legacy single-facility request',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->setRouteResolver(fn () => \Illuminate\Support\Facades\Route::getRoutes()
            ->getByName('college.requests.facilities.store'));
        $request->validateResolved();

        $this->assertSame([$facility->id], array_map('intval', $request->validated()['facility_ids']));

        $tooSoon = StoreFacilitiesUtilizationRequest::create('/college/requests/facilities', 'POST', [
            'last_name' => 'User',
            'date_activity' => now()->addDays(29)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'facility_ids' => [$facility->id],
            'purpose' => 'Insufficient notice',
        ]);
        $tooSoonValidator = Validator::make($tooSoon->all(), $tooSoon->rules());

        $this->assertTrue($tooSoonValidator->fails());
        $this->assertArrayHasKey('date_activity', $tooSoonValidator->errors()->messages());
    }

    public function test_multi_facility_date_range_generates_a_document(): void
    {
        $user = $this->user();
        $facilities = collect(['BUSALIAN HALL', 'E-HUB'])->map(fn (string $name) => Facility::create([
            'name' => $name,
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]));
        $submission = FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'reserved',
            'payload' => [
                'facility_ids' => $facilities->pluck('id')->all(),
                'date_activity' => '2030-02-10',
                'date_end' => '2030-02-12',
                'time_range' => ['start' => '09:00', 'end' => '11:00'],
                'purpose' => 'Multi-facility PDF test',
            ],
        ]);

        $response = app(FacilitiesFormPdfController::class)->generate($submission);
        $filePath = $response->getFile()->getPathname();

        try {
            $this->assertFileExists($filePath);
            $contents = file_get_contents($filePath);
            $this->assertIsString($contents);
            $this->assertTrue(
                str_starts_with($contents, '%PDF-') || str_starts_with($contents, "PK\x03\x04"),
                'The generated output should be a PDF or the documented DOCX fallback.'
            );

            if (str_starts_with($contents, '%PDF-')) {
                $pdftotext = (new ExecutableFinder())->find('pdftotext');
                if ($pdftotext) {
                    $process = new Process([$pdftotext, $filePath, '-']);
                    $process->mustRun();
                    $contents = $process->getOutput();
                }
            } else {
                $document = new \ZipArchive();
                $this->assertSame(true, $document->open($filePath));
                $contents = $document->getFromName('word/document.xml');
                $document->close();
                $this->assertIsString($contents);
                $contents = html_entity_decode(strip_tags($contents));
            }

            $this->assertStringContainsString('2030-02-10', $contents);
            $this->assertStringContainsString('2030-02-12', $contents);
            $this->assertStringContainsString('BUSALIAN HALL', $contents);
            $this->assertStringContainsString('E-HUB', $contents);
        } finally {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Multi Facility Test User',
            'last_name' => 'User',
            'email' => 'multi-facility-' . uniqid() . '@example.test',
            'password' => 'test-password',
            'role' => 'college_staff',
        ]);
    }

    private function facility(): Facility
    {
        return Facility::create([
            'name' => 'Test Venue ' . uniqid(),
            'owner_type' => 'gsu',
            'is_active' => true,
            'availability_status' => 'available',
        ]);
    }

    private function submission(User $user, array $facilityIds, string $date, ?string $dateEnd = null): FormSubmission
    {
        return FormSubmission::create([
            'type' => 'facilities_utilization',
            'requester_id' => $user->id,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'pending',
            'payload' => [
                'facility_id' => $facilityIds[0],
                'facility_ids' => $facilityIds,
                'date_activity' => $date,
                'date_end' => $dateEnd,
                'date_request' => now()->toDateString(),
                'time_range' => ['start' => '09:00', 'end' => '11:00'],
                'purpose' => 'Multi-facility schedule test',
            ],
        ]);
    }

    private function reservedBooking(User $user, Facility $facility, Carbon $start, Carbon $end): Booking
    {
        $booking = Booking::create([
            'requester_id' => $user->id,
            'start_time' => $start,
            'end_time' => $end,
            'requester_type' => 'college',
            'requester_unit' => 'Test College',
            'status' => 'reserved',
            'purpose' => 'Existing reservation',
            'booking_code' => 'EXISTING-' . uniqid(),
        ]);
        $booking->facilities()->attach($facility->id);

        return $booking;
    }

    private function requestValidator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $data['last_name'] ??= 'Test';
        $request = StoreFacilitiesUtilizationRequest::create('/college/requests/facilities', 'POST', $data);
        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        return $validator;
    }
}
