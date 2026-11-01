<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\GroupsBookingsByDay;
use App\Models\Facility;
use App\Services\ReservationCalendarService;
use Illuminate\Http\Request;

class PublicCalendarController extends Controller
{
    use GroupsBookingsByDay;

    public function calendarData(Request $request, ReservationCalendarService $calendar)
    {
        $month = $this->resolveMonth($request);
        $user = $request->user();
        $includeEventDetails = $user?->isAdmin()
            || $user?->isCollegeStaff()
            || $user?->isOrgStaff();

        return response()->json([
            'month' => $month->format('Y-m'),
            'days' => $calendar->dataForMonth($month, $includeEventDetails),
        ]);
    }

    public function index(Request $request)
    {
        $current = $this->resolveMonth($request);
        $bookings = app(ReservationCalendarService::class)->bookingsForMonth(
            $current,
            auth()->user()?->isAdmin() ?? false
        );

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

        return view('welcome', [
            'currentMonth'          => $current,
            'days'                  => $days,
            'unavailableFacilities' => $unavailableFacilities,
            'selectedDate'          => $selectedDate,
            'selectedDateBookings'  => $selectedDateBookings,
        ]);
    }
}
