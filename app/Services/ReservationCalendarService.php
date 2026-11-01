<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReservationCalendarService
{
    public function bookingsForMonth(Carbon $month, bool $includeAdminDetails = false): Collection
    {
        $relations = $includeAdminDetails ? ['facilities', 'requester'] : ['facilities'];

        return Booking::with($relations)
            ->whereBetween('start_time', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->whereIn('status', ['reserved', 'rescheduled'])
            ->orderBy('start_time')
            ->get();
    }

    public function dataForMonth(Carbon $month, bool $includeEventDetails = false): array
    {
        $days = [];
        $typeLabels = ['admin' => 'Admin', 'college' => 'College', 'org' => 'Org Unit', 'guest' => 'Guest'];

        foreach ($this->bookingsForMonth($month) as $booking) {
            $date = $booking->start_time->toDateString();
            $facilityNames = $booking->facilities->pluck('name')->join(', ') ?: 'Facility';
            $item = [
                'time' => $booking->start_time->format('g:i A') . ' – ' . $booking->end_time->format('g:i A'),
                'start' => $booking->start_time->format('H:i'),
                'end' => $booking->end_time->format('H:i'),
                'status' => $booking->status,
                'facility' => $facilityNames,
            ];
            if ($includeEventDetails) {
                $requesterType = $typeLabels[$booking->requester_type] ?? ucfirst((string) $booking->requester_type);
                $item['event'] = $booking->purpose ?? '';
                $item['who'] = trim($requesterType . ' · ' . ($booking->requester_unit ?? ''), ' ·');
            }

            $days[$date][] = $item;
        }

        return collect($days)
            ->map(fn (array $items) => ['count' => count($items), 'items' => $items])
            ->all();
    }
}
