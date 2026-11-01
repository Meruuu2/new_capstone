<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GroupsBookingsByDay;
use App\Models\Booking;
use App\Models\Facility;
use App\Services\ReservationCalendarService;
use App\Models\Notification;
use App\Rules\OperatingHours;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Services\GuestRequestEmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    use GroupsBookingsByDay;
    public function __construct(
        protected BookingService $bookingService,
        protected \App\Services\NotificationService $notificationService,
        protected GuestRequestEmailService $guestRequestEmails
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with(['requester', 'facilities'])
            ->whereIn('status', ['reserved', 'rescheduled', 'cancelled', 'completed', 'pending']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by facility
        if ($request->filled('facility_id')) {
            $facilityId = $request->input('facility_id');
            $query->whereHas('facilities', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });
        }

        // Filter by month (based on start_time)
        if ($request->filled('month')) {
            $month = $request->input('month'); // format: YYYY-MM
            $query->whereRaw('YEAR(start_time) = YEAR(?) AND MONTH(start_time) = MONTH(?)', [$month . '-01', $month . '-01']);
        }

        // Search by facility name or purpose or requester name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('purpose', 'like', '%' . $search . '%')
                  ->orWhereHas('facilities', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('requester', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
        }

        // Sort by start_time descending
        $bookings = $query->orderByDesc('start_time')
            ->paginate(20);

        // Get available facilities for filter dropdown
        $facilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bookings.index', compact('bookings', 'facilities'));
    }

    public function createDirect()
    {
        $facilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bookings.create_direct', compact('facilities'));
    }

    public function storeDirect(Request $request)
    {
        $request->merge(['account_last_name' => auth()->user()->last_name]);

        $data = $request->validate([
            'account_last_name' => ['required', 'string', 'max:120'],
            'facility_ids'   => 'required|array|min:1',
            'facility_ids.*' => 'required|distinct|exists:facilities,id',
            'date_activity' => 'required|date|after_or_equal:today',
            'start_time'    => ['required', 'date_format:H:i', new OperatingHours],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time', new OperatingHours],
            'purpose'       => 'required|string|max:500',
            'requester_name' => 'required|string|max:255',

            // Equipment (optional)
            'qty_monobloc'  => 'nullable|integer|min:0',
            'qty_table'     => 'nullable|integer|min:0',
            'qty_fan'       => 'nullable|integer|min:0',
            'qty_rostrum'   => 'nullable|integer|min:0',
            'qty_flag'      => 'nullable|integer|min:0',
            'qty_sound'     => 'nullable|integer|min:0',
            'qty_led'       => 'nullable|integer|min:0',
        ], [
            'account_last_name.required' => 'Update your account with a separate last name before creating a direct reservation.',
        ]);
        unset($data['account_last_name']);

        $booking = $this->bookingService->createDirectBooking($data, auth()->id());

        return redirect()->route('admin.bookings.index')
            ->with('status', "High-priority reservation created ({$booking->booking_code}). Conflicting reservations have been moved back to Pending and requesters notified.");
    }

    public function edit(Booking $booking)
    {
        $facilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bookings.edit', compact('booking', 'facilities'));
    }

    /**
     * Month calendar view for admin: shows bookings per day and facility counts.
     */
    public function calendar(Request $request, ReservationCalendarService $calendar)
    {
        $current = $this->resolveMonth($request);
        $bookings = $calendar->bookingsForMonth($current, true);
        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();

        $days = $this->groupByDay($bookings);

        // Optional: daily detail when ?day=DD is present
        $selectedDate = null;
        $selectedDateBookings = collect();
        if ($request->filled('day')) {
            $dayInt = (int) $request->query('day');
            if ($dayInt >= 1 && $dayInt <= $current->daysInMonth) {
                $selectedDate = $current->copy()->day($dayInt);
                $key = $selectedDate->toDateString();
                $selectedDateBookings = collect($days[$key] ?? [])->sortBy('start_time');
            }
        }

        $facilityCounts = Facility::orderBy('name')
            ->get()
            ->map(function ($facility) use ($start, $end) {
                $count = $facility->bookings()
                    ->whereBetween('start_time', [$start, $end])
                    ->whereIn('status', ['reserved', 'rescheduled'])
                    ->count();

                return [
                    'facility' => $facility,
                    'count'    => $count,
                ];
            });

         return view('admin.calendar.index', [
            'currentMonth'          => $current,
            'days'                  => $days,
            'facilityCounts'        => $facilityCounts,
            'selectedDate'          => $selectedDate,
            'selectedDateBookings'  => $selectedDateBookings,
        ]);
    }

    /**
     * Monthly overview: bar chart of bookings per facility for the month.
     *
     * X-axis: facility names (core facilities + "OTHERS")
     * Y-axis: number of approved/rescheduled bookings.
     */
    public function overview(Request $request)
    {
        $current = $this->resolveMonth($request);

        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        $bookings = Booking::with('facilities')
            ->whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['reserved', 'rescheduled', 'completed'])
            ->get();

        $facilities = Facility::orderBy('name')->get();
        $series = $facilities->pluck('name')->mapWithKeys(fn ($name) => [$name => 0])->all();
        $series['OTHERS'] = 0;
        $bookedHours = array_fill_keys($facilities->pluck('id')->all(), 0.0);
        $monthEndExclusive = $start->copy()->addMonth();
        $availableCalendarHours = $start->diffInSeconds($monthEndExclusive) / 3600;

        foreach ($bookings as $booking) {
            $overlapStart = $booking->start_time->greaterThan($start) ? $booking->start_time->copy() : $start->copy();
            $overlapEnd = $booking->end_time->lessThan($monthEndExclusive) ? $booking->end_time->copy() : $monthEndExclusive->copy();
            $hours = $overlapStart->lt($overlapEnd) ? $overlapStart->diffInSeconds($overlapEnd) / 3600 : 0;

            if ($booking->facilities->isEmpty()) {
                $series['OTHERS']++;
                continue;
            }

            foreach ($booking->facilities as $facility) {
                if (array_key_exists($facility->name, $series)) {
                    $series[$facility->name]++;
                    $bookedHours[$facility->id] += $hours;
                } else {
                    $series['OTHERS']++;
                }
            }
        }

        $facilityUtilization = $facilities->map(function (Facility $facility) use ($bookedHours, $availableCalendarHours) {
            $hours = round($bookedHours[$facility->id] ?? 0, 2);

            return [
                'name' => $facility->name,
                'booked_hours' => $hours,
                'available_hours' => round($availableCalendarHours, 2),
                'utilization' => $availableCalendarHours > 0 ? round($hours / $availableCalendarHours * 100, 2) : 0,
            ];
        });

        return view('admin.overview.index', [
            'currentMonth' => $current,
            'series'       => $series,
            'facilityUtilization' => $facilityUtilization,
            'availableCalendarHours' => round($availableCalendarHours, 2),
        ]);
    }

    /**
     * Admin reschedules / modifies an existing booking.
     * Can change facility, date/time, and equipment payload.
     * Marks status as 'rescheduled' and notifies requester.
     */
    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'facility_ids'   => 'required|array',
            'facility_ids.*' => 'required|distinct|exists:facilities,id',
            'date_activity' => 'required|date|after_or_equal:today',
            'start_time'    => ['required', 'date_format:H:i', new OperatingHours],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time', new OperatingHours],
            'purpose'       => 'required|string|max:500',
            'reason'        => 'required|string|max:500',

            // Equipment (optional)
            'qty_monobloc'  => 'nullable|integer|min:0',
            'qty_table'     => 'nullable|integer|min:0',
            'qty_fan'       => 'nullable|integer|min:0',
            'qty_rostrum'   => 'nullable|integer|min:0',
            'qty_flag'      => 'nullable|integer|min:0',
            'qty_sound'     => 'nullable|integer|min:0',
            'qty_led'       => 'nullable|integer|min:0',
        ]);

        $startDateTime = Carbon::parse($data['date_activity'].' '.$data['start_time']);
        $endDateTime   = Carbon::parse($data['date_activity'].' '.$data['end_time']);

        if ($startDateTime->lessThan(now())) {
            return back()->withErrors(['date_activity' => 'A booking cannot be rescheduled to a time in the past.'])->withInput();
        }
        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            return back()->withErrors(['End time must be after start time.']);
        }

        // Merge / update additional_details (always treat as array)
        $existingDetails = is_array($booking->additional_details)
            ? $booking->additional_details
            : ($booking->additional_details ? json_decode($booking->additional_details, true) : []);


        $equipment = [
            'monobloc_chair' => (int) ($data['qty_monobloc'] ?? ($existingDetails['equipment']['monobloc_chair'] ?? 0)),
            'table'          => (int) ($data['qty_table'] ?? ($existingDetails['equipment']['table'] ?? 0)),
            'electric_fan'   => (int) ($data['qty_fan'] ?? ($existingDetails['equipment']['electric_fan'] ?? 0)),
            'rostrum'        => (int) ($data['qty_rostrum'] ?? ($existingDetails['equipment']['rostrum'] ?? 0)),
            'flag'           => (int) ($data['qty_flag'] ?? ($existingDetails['equipment']['flag'] ?? 0)),
            'sound'          => (int) ($data['qty_sound'] ?? ($existingDetails['equipment']['sound'] ?? 0)),
            'led'            => (int) ($data['qty_led'] ?? ($existingDetails['equipment']['led'] ?? 0)),
        ];

        $updatedDetails = array_merge($existingDetails, [
            'equipment'       => $equipment,
            'reschedule_note' => $data['reason'],
            'rescheduled_at'  => now()->toDateTimeString(),
            'rescheduled_by'  => auth()->user()->name ?? 'GSU Admin',
        ]);

        DB::transaction(function () use ($booking, $data, $startDateTime, $endDateTime, $updatedDetails) {
            $facilities = Facility::whereIn('id', $data['facility_ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            if ($facilities->count() !== count(array_unique($data['facility_ids']))) {
                throw ValidationException::withMessages(['facility_ids' => 'One or more selected facilities could not be found.']);
            }

            $unavailable = $facilities->first(fn (Facility $facility) => ! $facility->isAvailable());
            if ($unavailable) {
                throw ValidationException::withMessages(['facility_ids' => $unavailable->name . ' is not currently available.']);
            }

            $conflict = Booking::overlapping($data['facility_ids'], $startDateTime, $endDateTime)
                ->where('bookings.id', '<>', $booking->id)
                ->lockForUpdate()
                ->first();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'facility_ids' => 'The selected facility and time overlap booking ' . $conflict->booking_code . '. Choose a different time or venue.',
                ]);
            }

            $previousFacilityIds = $booking->facilities()->pluck('facilities.id')->map(fn ($id) => (int) $id)->all();
            $booking->facilities()->sync($data['facility_ids']);
            app(AuditLogService::class)->record('facilities_updated', $booking, [
                'facility_ids' => $previousFacilityIds,
            ], [
                'facility_ids' => array_map('intval', $data['facility_ids']),
            ]);
            $booking->start_time = $startDateTime;
            $booking->end_time = $endDateTime;
            $booking->purpose = $data['purpose'];
            $booking->status = 'rescheduled';
            $booking->additional_details = $updatedDetails;
            $booking->save();
        });
        $this->guestRequestEmails->sendBookingUpdate($booking, 'rescheduled', [
            'reason' => $data['reason'],
        ]);

        // Notify requester and other admins
        if ($booking->requester) {
            $this->notificationService->notifyBookingRescheduled(
                $booking->requester_id,
                $booking->booking_code,
                $data['reason'],
                $booking->id,
                $booking->facilities->first()->id ?? 0
            );
        }

        $this->notificationService->notifyAdminsOfBookingUpdate(
            $booking->id,
            $booking->booking_code,
            auth()->user()->name
        );

        return redirect()->route('admin.bookings.index')
            ->with('status', 'Booking updated and requester notified.');
    }

    /**
     * Admin cancels an already approved/converted booking.
     * Marks booking as 'cancelled' and notifies requester.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $booking->status = 'cancelled';

        // additional_details is cast as array in Booking model; keep it consistent
        $details = is_array($booking->additional_details)
            ? $booking->additional_details
            : ($booking->additional_details ? json_decode($booking->additional_details, true) : []);

        $details['cancel_reason']  = $data['reason'];
        $details['cancelled_by']   = auth()->user()->name ?? 'GSU Admin';
        $details['cancelled_at']   = now()->toDateTimeString();

        $booking->additional_details = $details; // array; cast will JSON it
        $booking->save();
        $this->guestRequestEmails->sendBookingUpdate($booking, 'cancelled', [
            'reason' => $data['reason'],
        ]);

        if ($booking->requester) {
            $this->notificationService->notifyBookingCancelled(
                $booking->requester_id,
                $booking->booking_code,
                $data['reason'],
                $booking->id
            );
        }

        $this->notificationService->notifyAdminsOfBookingCancellation(
            $booking->id,
            $booking->booking_code,
            auth()->user()->name
        );

        return redirect()->route('admin.bookings.index')
            ->with('status', 'Booking cancelled and requester notified.');
    }
}