<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait GroupsBookingsByDay
{
    protected function resolveMonth(Request $request): Carbon
    {
        $month = $request->query('month');
        if (! is_string($month) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return now()->startOfMonth();
        }

        try {
            $current = Carbon::createFromFormat('!Y-m', $month);
            return $current && $current->format('Y-m') === $month
                ? $current->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    protected function groupByDay(Collection $bookings): array
    {
        $days = [];

        foreach ($bookings as $booking) {
            $dayKey = $booking->start_time->toDateString();
            $days[$dayKey][] = $booking;
        }

        return $days;
    }
}
