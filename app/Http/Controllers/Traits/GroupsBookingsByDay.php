<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

    /**
     * A multi-day request is saved as one booking per day (CODE, CODE-D2, CODE-D3...).
     * Paginates reservations instead of days: each item is the first day, with every day in ->days.
     * shortcut: groups in PHP over every matching booking; move the grouping into SQL if bookings reach the tens of thousands.
     */
    protected function paginateReservations($query, Request $request, int $perPage): LengthAwarePaginator
    {
        $groups = $query->orderBy('start_time')->get()
            ->groupBy(fn ($booking) => $booking->booking_code ? preg_replace('/-D\d+$/', '', $booking->booking_code) : 'id-' . $booking->id)
            ->map(fn ($days) => $days->first()->setRelation('days', $days))
            ->sortByDesc(fn ($booking) => $booking->start_time)
            ->values();
        $page = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $groups->forPage($page, $perPage)->values(),
            $groups->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
