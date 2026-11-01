<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

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

        // Repair tickets are shared among staff in the same college.
        $tickets = MaintenanceTicket::whereIn('requester_id', $user->collegeTeamIds());
        $maintenanceStats = collect(['pending', 'notified', 'ongoing', 'success', 'failed'])
            ->mapWithKeys(fn ($status) => [$status => (clone $tickets)->where('status', $status)->count()])
            ->all();
        $maintenanceStats['total'] = array_sum($maintenanceStats);

        return view('college.dashboard', compact('facilitiesStats', 'maintenanceStats'));
    }
}
