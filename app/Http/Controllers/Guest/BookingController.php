<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GroupsBookingsByDay;
use App\Models\Booking;
use App\Models\Facility;
use App\Services\ReservationCalendarService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use GroupsBookingsByDay;

    public function index()
    {
        $bookings = Booking::with('facilities')
            ->where('requester_id', auth()->id())
            ->latest('start_time')
            ->paginate(15);

        return view('guest.bookings.index', compact('bookings'));
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
                $selectedDateBookings = collect($days[$key] ?? [])->sortBy('start_time');
            }
        }

        $unavailableFacilities = Facility::whereIn('availability_status', ['unavailable', 'maintenance'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('guest.bookings.calendar', [
            'currentMonth'          => $current,
            'days'                  => $days,
            'unavailableFacilities' => $unavailableFacilities,
            'selectedDate'          => $selectedDate,
            'selectedDateBookings'  => $selectedDateBookings,
        ]);
    }
}
