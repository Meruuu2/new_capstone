<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
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

        // Maintenance Tickets Statistics (only for this user)
        $maintenanceStats = [
            'pending' => MaintenanceTicket::where('requester_id', $userId)->where('status', 'pending')->count(),
            'notified' => MaintenanceTicket::where('requester_id', $userId)->where('status', 'notified')->count(),
            'ongoing' => MaintenanceTicket::where('requester_id', $userId)->where('status', 'ongoing')->count(),
            'success' => MaintenanceTicket::where('requester_id', $userId)->where('status', 'success')->count(),
            'failed' => MaintenanceTicket::where('requester_id', $userId)->where('status', 'failed')->count(),
        ];
        $maintenanceStats['total'] = array_sum($maintenanceStats);

        return view('org.dashboard', compact('facilitiesStats', 'maintenanceStats'));
    }
}
