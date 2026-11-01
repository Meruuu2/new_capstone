<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Facility Requests Statistics (only for this user)
        $facilitiesStats = [
            'pending' => FormSubmission::where('type', 'facilities_utilization')
                ->where('requester_id', $userId)
                ->where('status', 'pending')->count(),
            'pending_payment' => FormSubmission::where('type', 'facilities_utilization')
                ->where('requester_id', $userId)
                ->where('status', 'pending_payment')->count(),
            'approved' => FormSubmission::where('type', 'facilities_utilization')
                ->where('requester_id', $userId)
                ->where('status', 'approved')->count(),
            'reserved' => FormSubmission::where('type', 'facilities_utilization')
                ->where('requester_id', $userId)
                ->where('status', 'reserved')->count(),
            'disapproved' => FormSubmission::where('type', 'facilities_utilization')
                ->where('requester_id', $userId)
                ->where('status', 'disapproved')->count(),
        ];
        $facilitiesStats['total'] = array_sum($facilitiesStats);

        // Upcoming Events (All bookings - public view)
        $upcomingEvents = Booking::with(['facilities', 'requester'])
            ->where('status', 'reserved')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // Unread notifications count for the notification badge
        $unreadNotificationsCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return view('guest.dashboard', compact('facilitiesStats', 'upcomingEvents', 'unreadNotificationsCount'));
    }
}
