<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    public function hasOverlap(int $facilityId, Carbon $startTime, Carbon $endTime): bool
    {
        return Booking::whereHas('facilities', fn ($query) => $query->where('facility_id', $facilityId))
            ->whereIn('status', ['reserved', 'rescheduled', 'booked'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function createFromSubmission(FormSubmission $submission): Booking|string
    {
        $payload = $submission->payload ?? null;
        if (!$payload) {
            return 'Submission payload is missing.';
        }

        $facilityIds = $payload['facility_ids'] ?? (isset($payload['facility_id']) ? [$payload['facility_id']] : []);
        if (! is_array($facilityIds)) {
            return 'Facility information in the submission is invalid.';
        }
        $facilityIds = array_values(array_unique(array_filter(
            array_map('intval', $facilityIds),
            fn (int $id) => $id > 0
        )));
        $dateActivity = $payload['date_activity'] ?? null;
        $timeRange    = $payload['time_range'] ?? null;

        if (!$facilityIds || !$dateActivity || !$timeRange || empty($timeRange['start']) || empty($timeRange['end'])) {
            return 'Incomplete date/time or facility information in the submission.';
        }

        if (Carbon::parse($dateActivity)->lessThan(now()->addDays(30)->startOfDay())) {
            return 'Date of activity is less than 30 days away.';
        }

        $facilitiesById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        if ($facilitiesById->count() !== count($facilityIds)) {
            return 'One or more requested facilities could not be found.';
        }

        foreach ($facilitiesById as $facility) {
            if (! $facility->isAvailable()) {
                return "{$facility->name} is currently {$facility->availability_status} and cannot be booked.";
            }
        }

        // One booking per day: a single-day request has one day; a multi-day request (payload date_end) has several
        $lastDay = !empty($payload['date_end']) ? $payload['date_end'] : $dateActivity;
        $startDay = Carbon::parse($dateActivity)->startOfDay();
        $endDay = Carbon::parse($lastDay)->startOfDay();
        if ($endDay->lt($startDay)) {
            return 'The end date of the event is before its start date.';
        }
        if ($startDay->diffInDays($endDay) > 13) {
            return 'Events can span at most 14 days.';
        }

        $slots = [];
        for ($day = $startDay->copy(); $day->lte($endDay); $day->addDay()) {
            $slotStart = Carbon::parse($day->toDateString() . ' ' . $timeRange['start']);
            $slotEnd   = Carbon::parse($day->toDateString() . ' ' . $timeRange['end']);

            if ($slotEnd->lessThanOrEqualTo($slotStart)) {
                return 'End time must be after start time.';
            }

            $slots[] = [$slotStart, $slotEnd];
        }

        $requester = $submission->requester;
        if (!$requester) {
            return 'Requester account not found.';
        }

        $baseCode  = 'BKG-' . now()->format('YmdHis') . '-' . $facilityIds[0] . '-' . Str::upper(Str::random(6));
        $dayCount  = count($slots);

        $booking = DB::transaction(function () use ($slots, $dayCount, $baseCode, $requester, $submission, $payload, $facilityIds) {
            $lockedFacilities = Facility::whereIn('id', $facilityIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($lockedFacilities->count() !== count($facilityIds)) {
                return 'One or more requested facilities could not be found.';
            }

            foreach ($lockedFacilities as $facility) {
                if (! $facility->isAvailable()) {
                    return "{$facility->name} is currently {$facility->availability_status} and cannot be booked.";
                }
            }

            foreach ($slots as [$slotStart, $slotEnd]) {
                $conflictExists = Booking::whereHas('facilities', fn ($query) => $query->whereIn('facility_id', $facilityIds))
                    ->whereIn('status', ['reserved', 'rescheduled', 'booked'])
                    ->where('start_time', '<', $slotEnd)
                    ->where('end_time', '>', $slotStart)
                    ->exists();

                if ($conflictExists) {
                    return 'One or more requested facilities are already booked on ' . $slotStart->format('M d, Y') . ' at the requested time.';
                }
            }

            // All requested facilities and event days are booked together or not at all.
            $first = null;
            foreach ($slots as $i => [$slotStart, $slotEnd]) {
                $details = [
                    'form_submission_id' => $submission->id,
                    'equipment'          => $payload['equipment'] ?? [],
                ];
                if ($dayCount > 1) {
                    $details['multi_day'] = ['day' => $i + 1, 'of' => $dayCount];
                }

                $created = Booking::create([
                    'requester_id'       => $requester->id,
                    'start_time'         => $slotStart,
                    'end_time'           => $slotEnd,
                    'requester_type'     => $submission->requester_type,
                    'requester_unit'     => $submission->requester_unit,
                    'status'             => 'reserved',
                    'request_method'     => 'online_form',
                    'purpose'            => $payload['purpose'] ?? null,
                    'additional_details' => $details,
                    'requested_at'       => $payload['date_request'] ?? now(),
                    'approved_at'        => now(),
                    // Day 1 keeps the usual code; later days get a -D2, -D3 ... suffix
                    'booking_code'       => $i === 0 ? $baseCode : $baseCode . '-D' . ($i + 1),
                ]);

                $created->facilities()->sync($facilityIds);
                app(AuditLogService::class)->record('facilities_attached', $created, newValues: [
                    'facility_ids' => $facilityIds,
                ]);
                $first ??= $created;
            }

            $submission->markBooked();

            return $first;
        });

        if (is_string($booking)) {
            return $booking;
        }

        $this->notifications->notifyBookingCreated(
            $submission->requester_id,
            $submission->id,
            $booking->id,
            $booking->booking_code
        );

        return $booking;
    }

    public function createDirectBooking(array $data, int $adminId): Booking
    {
        $startDateTime = Carbon::parse($data['date_activity'] . ' ' . $data['start_time']);
        $endDateTime   = Carbon::parse($data['date_activity'] . ' ' . $data['end_time']);
        if ($startDateTime->lt(now())) {
            throw ValidationException::withMessages(['date_activity' => 'A direct reservation cannot start in the past.']);
        }

        $facilityIds = array_values(array_unique(array_map('intval', $data['facility_ids'])));
        $bookingCode = 'BKG-' . now()->format('YmdHis') . '-' . head($facilityIds) . '-' . Str::upper(Str::random(6));
        [$booking, $conflictingBookings] = DB::transaction(function () use ($facilityIds, $startDateTime, $endDateTime, $data, $adminId, $bookingCode) {
            $facilities = Facility::whereIn('id', $facilityIds)->orderBy('id')->lockForUpdate()->get();
            if ($facilities->count() !== count($facilityIds)) {
                throw ValidationException::withMessages(['facility_ids' => 'One or more selected facilities could not be found.']);
            }

            $unavailable = $facilities->first(fn (Facility $facility) => ! $facility->isAvailable());
            if ($unavailable) {
                throw ValidationException::withMessages(['facility_ids' => $unavailable->name . ' is not currently available.']);
            }

            $conflictingBookings = Booking::overlapping($facilityIds, $startDateTime, $endDateTime)
                ->with('requester')
                ->lockForUpdate()
                ->get();
            foreach ($conflictingBookings as $conflictingBooking) {
                $conflictingBooking->status = 'pending';
                $conflictingBooking->save();
            }
            $preemptedSubmissionIds = $conflictingBookings
                ->map(fn (Booking $conflict) => $conflict->additional_details['form_submission_id'] ?? null)
                ->filter()
                ->unique()
                ->values();
            if ($preemptedSubmissionIds->isNotEmpty()) {
                FormSubmission::whereIn('id', $preemptedSubmissionIds)
                    ->where('status', 'reserved')
                    ->lockForUpdate()
                    ->get()
                    ->each(function (FormSubmission $submission): void {
                        $submission->status = 'pending';
                        $submission->save();
                    });
            }

            $equipment = $this->buildEquipmentArray($data);
            $booking = Booking::create([
                'requester_id'       => $adminId,
                'start_time'         => $startDateTime,
                'end_time'           => $endDateTime,
                'requester_type'     => 'admin',
                'requester_unit'     => 'GSU Office',
                'status'             => 'reserved',
                'request_method'     => 'direct_admin',
                'purpose'            => $data['purpose'],
                'additional_details' => [
                    'equipment' => $equipment,
                    'requester_name' => $data['requester_name'],
                ],
                'requested_at'       => now(),
                'approved_at'        => now(),
                'booking_code'       => $bookingCode,
            ]);
            $booking->facilities()->sync($facilityIds);
            app(AuditLogService::class)->record('facilities_attached', $booking, newValues: [
                'facility_ids' => $facilityIds,
            ]);

            $submission = FormSubmission::create([
                'type'           => 'facilities_utilization',
                'requester_id'   => $adminId,
                'requester_type' => 'admin',
                'requester_unit' => 'GSU Office',
                'status'         => 'reserved',
                'payload'        => [
                    'control_no'      => $bookingCode,
                    'date_request'    => now()->toDateString(),
                    'requester_name'  => $data['requester_name'],
                    'client_name'     => $data['requester_name'],
                    'contact_number'  => $data['requester_contact'] ?? null,
                    'date_activity'   => $data['date_activity'],
                    'time_range'      => ['start' => $data['start_time'], 'end' => $data['end_time']],
                    'facility_id'     => head($facilityIds),
                    'facility_ids'    => $facilityIds,
                    'facility_names'  => $facilities->pluck('name')->all(),
                    'purpose'         => $data['purpose'],
                    'equipment'       => $equipment,
                    'approved_datetime' => now()->format('M d, Y h:i A'),
                    'is_direct_booking' => true,
                ],
            ]);

            $details = $booking->additional_details ?? [];
            $details['form_submission_id'] = $submission->id;
            $booking->additional_details = $details;
            $booking->save();

            return [$booking, $conflictingBookings];
        });

        foreach ($conflictingBookings as $conflictingBooking) {
            if ($conflictingBooking->requester) {
                $this->notifications->notifyBookingPreempted(
                    $conflictingBooking->requester_id,
                    $conflictingBooking->id,
                    $conflictingBooking->booking_code
                );
            }
        }

        return $booking;
    }

    public function buildEquipmentArray(array $data, array $existingEquipment = []): array
    {
        return [
            'monobloc_chair' => (int) ($data['qty_monobloc'] ?? ($existingEquipment['monobloc_chair'] ?? 0)),
            'table'          => (int) ($data['qty_table'] ?? ($existingEquipment['table'] ?? 0)),
            'electric_fan'   => (int) ($data['qty_fan'] ?? ($existingEquipment['electric_fan'] ?? 0)),
            'rostrum'        => (int) ($data['qty_rostrum'] ?? ($existingEquipment['rostrum'] ?? 0)),
            'flag'           => (int) ($data['qty_flag'] ?? ($existingEquipment['flag'] ?? 0)),
            'sound'          => (int) ($data['qty_sound'] ?? ($existingEquipment['sound'] ?? 0)),
            'led'            => (int) ($data['qty_led'] ?? ($existingEquipment['led'] ?? 0)),
        ];
    }
}