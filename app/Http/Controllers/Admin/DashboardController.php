<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\MaintenanceTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Shared by full admins, facility staff and repair staff; each only gets the sections their access level covers
    public function index(Request $request)
    {
        $user = $request->user();
        $showFacilities = $user->managesFacilities();
        $showRepair = $user->isAdmin() || $user->isMaintenanceStaff();

        $year = filter_var($request->query('year', now()->year), FILTER_VALIDATE_INT);
        $year = $year !== false && $year >= 2024 && $year <= 2100 ? $year : now()->year;

        $facilitiesStats = $availabilityStats = $maintenanceStats = $reservationsByMonth = $ticketsByMonth = $completedByMonth = null;

        if ($showFacilities) {
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

            // Same bookings the Monthly records page counts
            $reservationsByMonth = $this->countByMonth(
                Booking::whereIn('status', MonthlyRecordsController::REPORTABLE_BOOKING_STATUSES),
                'start_time',
                $year
            );
        }

        if ($showRepair) {
            $maintenanceStats = [
                'pending' => MaintenanceTicket::where('status', 'pending')->count(),
                'notified' => MaintenanceTicket::where('status', 'notified')->count(),
                'ongoing' => MaintenanceTicket::where('status', 'ongoing')->count(),
                'success' => MaintenanceTicket::where('status', 'success')->count(),
                'replacement' => MaintenanceTicket::where('status', 'replacement')->count(),
            ];
            $maintenanceStats['total'] = array_sum($maintenanceStats);

            $ticketsByMonth = $this->countByMonth(MaintenanceTicket::query(), 'created_at', $year);
            $completedByMonth = $this->countByMonth(MaintenanceTicket::where('status', 'success'), 'completed_at', $year);
        }

        return view('admin.dashboard', compact(
            'showFacilities',
            'showRepair',
            'year',
            'facilitiesStats',
            'maintenanceStats',
            'availabilityStats',
            'reservationsByMonth',
            'ticketsByMonth',
            'completedByMonth'
        ));
    }

    /** Rows per month (Jan..Dec) of $year, keyed 1..12. */
    public static function countByMonth($query, string $column, int $year): array
    {
        $counts = $query->whereYear($column, $year)
            ->selectRaw("MONTH({$column}) as m, COUNT(*) as n")
            ->groupByRaw("MONTH({$column})")
            ->pluck('n', 'm');

        return collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => (int) ($counts[$m] ?? 0)])->all();
    }
}
