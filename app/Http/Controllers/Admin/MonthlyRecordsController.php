<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Support\VenueRates;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyRecordsController extends Controller
{
    private const REPORTABLE_BOOKING_STATUSES = ['reserved', 'rescheduled', 'completed'];

    public function index(Request $request)
    {
        $month = filter_var($request->query('month', now()->month), FILTER_VALIDATE_INT);
        $year = filter_var($request->query('year', now()->year), FILTER_VALIDATE_INT);
        $month = $month !== false && $month >= 1 && $month <= 12 ? $month : now()->month;
        $year = $year !== false && $year >= 2000 && $year <= 2100 ? $year : now()->year;

        // Create date range for the selected month
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $venueRates = $this->venueRates();

        $bookings = Booking::with(['facilities', 'requester'])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereIn('status', self::REPORTABLE_BOOKING_STATUSES)
            ->orderBy('start_time', 'asc')
            ->get();

        // Each booking records the exact source submission, avoiding accidental matches
        // when the same requester has multiple reservations.
        $submissionIds = $bookings
            ->pluck('additional_details.form_submission_id')
            ->filter()
            ->unique();
        $formSubmissions = FormSubmission::whereIn('id', $submissionIds)->get()->keyBy('id');

        // Format bookings for display
        $records = $bookings->map(function ($booking) use ($formSubmissions, $venueRates) {
            $facilities = $booking->facilities;
            $details = $booking->additional_details ?? [];
            $formSubmission = $formSubmissions->get($details['form_submission_id'] ?? null);
            $payload = $formSubmission?->payload ?? [];
            $equipment = $details['equipment'] ?? $payload['equipment'] ?? [];

            return [
                'date_requested' => $booking->requested_at ? $booking->requested_at->format('F d, Y') : ($booking->created_at ? $booking->created_at->format('F d, Y') : '-'),
                'date_of_activity' => $booking->start_time->format('F d, Y'),
                'time' => $booking->start_time->format('g:ia') . '-' . $booking->end_time->format('g:ia'),
                'purpose' => $booking->purpose ?? '-',
                'requester' => $payload['client_name'] ?? $booking->requester->name ?? '-',
                'contact_no' => $payload['contact_number'] ?? $booking->requester?->contact_number ?? '-',
                'venue' => $facilities->isNotEmpty() ? $facilities->pluck('name')->join(', ') : '-',
                'equipment' => [
                    'chairs' => $equipment['monobloc_chair'] ?? $payload['qty_monobloc'] ?? 0,
                    'tables' => $equipment['table'] ?? $payload['qty_table'] ?? 0,
                    'fans' => $equipment['electric_fan'] ?? $payload['qty_fan'] ?? 0,
                    'rostrum' => $equipment['rostrum'] ?? $payload['qty_rostrum'] ?? 0,
                    'flags' => $equipment['flag'] ?? $payload['qty_flag'] ?? 0,
                    'sound' => $equipment['sound'] ?? $payload['qty_sound'] ?? 0,
                    'led' => $equipment['led'] ?? $payload['qty_led'] ?? 0,
                ],
                'rental_fee' => $facilities->sum(fn ($facility) => $venueRates[$this->normalizeName($facility->name)] ?? 0),
            ];
        });

        // Calculate venue usage summary
        $venueSummary = $bookings
            ->flatMap(fn ($booking) => $booking->facilities->pluck('name'))
            ->countBy()
            ->sortKeys();

        $allVenues = Facility::orderBy('name')
            ->pluck('name')
            ->mapWithKeys(fn ($name) => [$name => (int) $venueSummary->get($name, 0)]);
        foreach ($venueSummary as $venue => $count) {
            $allVenues[$venue] = $count;
        }
        $allVenues = $allVenues->sortByDesc(fn ($count) => $count);

        // Calculate total rental fees
        $totalRentalFees = $records->sum('rental_fee');

        $trendStart = now()->subMonths(11)->startOfMonth();
        $trendEnd = now()->endOfMonth();
        $trendCounts = Booking::query()
            ->selectRaw('YEAR(start_time) as report_year, MONTH(start_time) as report_month, COUNT(*) as booking_count')
            ->whereBetween('start_time', [$trendStart, $trendEnd])
            ->whereIn('status', self::REPORTABLE_BOOKING_STATUSES)
            ->groupByRaw('YEAR(start_time), MONTH(start_time)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->report_year, $row->report_month));

        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $trendMonth = now()->subMonths($i);
            $key = $trendMonth->format('Y-m');

            $monthlyTrend[] = [
                'month' => $trendMonth->format('M Y'),
                'count' => (int) ($trendCounts->get($key)->booking_count ?? 0),
            ];
        }

        return view('admin.monthly-records.index', compact(
            'records',
            'venueSummary',
            'allVenues',
            'totalRentalFees',
            'month',
            'year',
            'monthlyTrend'
        ));
    }

    public function exportCharts(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2024,2030'],
        ]);
        $month = (int) $filters['month'];
        $year = (int) $filters['year'];

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();
        $venueSummary = Booking::with('facilities')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereIn('status', self::REPORTABLE_BOOKING_STATUSES)
            ->get()
            ->flatMap(fn ($booking) => $booking->facilities->pluck('name'))
            ->countBy()
            ->sortKeys();

        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $trendMonth = now()->subMonths($i);
            $monthlyTrend[] = [
                'label' => $trendMonth->format('M Y'),
                'count' => Booking::whereBetween('start_time', [
                    $trendMonth->copy()->startOfMonth(),
                    $trendMonth->copy()->endOfMonth(),
                ])->whereIn('status', self::REPORTABLE_BOOKING_STATUSES)->count(),
            ];
        }

        return response()->streamDownload(function () use ($monthlyTrend, $venueSummary, $startDate) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Chart', 'Category', 'Bookings']);

            foreach ($monthlyTrend as $point) {
                fputcsv($output, ['Booking Trend (last 12 months)', $point['label'], $point['count']]);
            }
            foreach ($venueSummary as $venue => $count) {
                fputcsv($output, ['Facility Usage (' . $startDate->format('F Y') . ')', $venue, $count]);
            }

            fclose($output);
        }, sprintf('monthly-graphs-%04d-%02d.csv', $startDate->year, $startDate->month), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function venueRates(): array
    {
        return collect(VenueRates::all())
            ->mapWithKeys(function (array $venue) {
                preg_match('/PhP\s*([\d,.]+)/i', $venue['rate'], $matches);

                return [
                    $this->normalizeName($venue['name']) => isset($matches[1])
                        ? (float) str_replace(',', '', $matches[1])
                        : 0,
                ];
            })
            ->all();
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim($name)));
    }
}
