<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GroupsBookingsByDay;
use App\Models\Booking;
use App\Models\Facility;
use App\Services\ReservationCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    use GroupsBookingsByDay;

    /**
     * List this org staff member's active reservations with filtering.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Booking::query()
            ->with('facilities')
            ->where('requester_id', $user->id)
            ->whereIn('status', ['reserved', 'rescheduled']);

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

        // Search by facility name or purpose
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('purpose', 'like', '%' . $search . '%')
                  ->orWhereHas('facilities', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
        }

        // Sort by start_time descending
        $bookings = $query->orderByDesc('start_time')
            ->paginate(15);

        // Get available facilities for filter dropdown
        $facilities = Facility::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('org.bookings.index', compact('bookings', 'facilities'));
    }

    public function calendar(Request $request, ReservationCalendarService $calendar)
    {
        $current = $this->resolveMonth($request);
        $bookings = $calendar->bookingsForMonth($current);

        $days = $this->groupByDay($bookings);

        $selectedDate = null;
        $selectedDateBookings = collect();

        if ($request->filled('day')) {
            $dayInt = (int) $request->query('day');

            if ($dayInt >= 1 && $dayInt <= $current->daysInMonth) {
                $selectedDate = $current->copy()->day($dayInt);
                $key = $selectedDate->toDateString();

                $selectedDateBookings = collect($days[$key] ?? [])
                    ->sortBy('start_time')
                    ->values();
            }
        }

        return view('org.bookings.calendar', [
            'currentMonth' => $current,
            'days' => $days,
            'selectedDate' => $selectedDate,
            'selectedDateBookings' => $selectedDateBookings,
        ]);
    }
}

