<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Facility Requests Statistics
        $facilitiesStats = [
            'pending' => FormSubmission::where('type', 'facilities_utilization')->where('status', 'pending')->count(),
            'pending_payment' => FormSubmission::where('type', 'facilities_utilization')->where('status', 'pending_payment')->count(),
            'approved' => FormSubmission::where('type', 'facilities_utilization')->where('status', 'approved')->count(),
            'reserved' => FormSubmission::where('type', 'facilities_utilization')->where('status', 'reserved')->count(),
            'disapproved' => FormSubmission::where('type', 'facilities_utilization')->where('status', 'disapproved')->count(),
        ];
        $facilitiesStats['total'] = array_sum($facilitiesStats);

        $availabilityStats = ['available' => 0, 'unavailable' => 0, 'maintenance' => 0];
        Facility::query()->get(['is_active', 'availability_status'])->each(function (Facility $facility) use (&$availabilityStats) {
            if ($facility->availability_status === 'maintenance') {
                $availabilityStats['maintenance']++;
            } elseif (! $facility->isAvailable()) {
                $availabilityStats['unavailable']++;
            } else {
                $availabilityStats['available']++;
            }
        });

        // Maintenance Tickets Statistics
        $maintenanceStats = [
            'pending' => MaintenanceTicket::where('status', 'pending')->count(),
            'notified' => MaintenanceTicket::where('status', 'notified')->count(),
            'ongoing' => MaintenanceTicket::where('status', 'ongoing')->count(),
            'success' => MaintenanceTicket::where('status', 'success')->count(),
            'failed' => MaintenanceTicket::where('status', 'failed')->count(),
        ];
        $maintenanceStats['total'] = array_sum($maintenanceStats);

        return view('admin.dashboard', compact('facilitiesStats', 'maintenanceStats', 'availabilityStats'));
    }
}
