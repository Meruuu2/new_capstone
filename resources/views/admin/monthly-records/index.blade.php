@extends('layouts.admin')

@section('admin-content')
@php
    $periodStart  = \Carbon\Carbon::create((int) $year, (int) $month, 1);
    $prevPeriod   = $periodStart->copy()->subMonth();
    $nextPeriod   = $periodStart->copy()->addMonth();
    $bookingCount = $records->count();
    $venuesUsed   = $venueSummary->filter(fn ($c) => $c > 0)->count();
    $busiestVenue = $venueSummary->filter(fn ($c) => $c > 0)->sortDesc()->keys()->first();
    $busiestCount = $busiestVenue ? $venueSummary[$busiestVenue] : 0;
@endphp

<div class="records-page">
    <section class="records-hero" style="--records-hero-image: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="records-eyebrow">Monthly Records</p>
            <div class="records-period">
                <a class="records-step" href="{{ route('admin.monthly-records.index', ['month' => $prevPeriod->month, 'year' => $prevPeriod->year]) }}" aria-label="Previous month: {{ $prevPeriod->format('F Y') }}" title="{{ $prevPeriod->format('F Y') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <h1>{{ $periodStart->format('F Y') }}</h1>
                <a class="records-step" href="{{ route('admin.monthly-records.index', ['month' => $nextPeriod->month, 'year' => $nextPeriod->year]) }}" aria-label="Next month: {{ $nextPeriod->format('F Y') }}" title="{{ $nextPeriod->format('F Y') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>
            <p class="records-hero-sub">Facility bookings, equipment and rental fees for the month.</p>
        </div>

        <div class="records-tools">
            <!-- Month/Year Filter -->
            <form method="GET" action="{{ route('admin.monthly-records.index') }}" class="records-filter">
                <div>
                    <label for="records-month">Month</label>
                    <select id="records-month" name="month">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="records-year">Year</label>
                    <select id="records-year" name="year">
                        @foreach(range(2024, 2030) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit">View</button>
            </form>
            <a href="{{ route('admin.monthly-records.export', ['month' => $month, 'year' => $year]) }}" class="records-print">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/></svg>
                Export chart data
            </a>
            <button type="button" onclick="window.print()" class="records-print">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
                Print
            </button>
        </div>
    </section>

    <!-- Key figures -->
    <section class="records-stats" aria-label="Key figures for {{ $periodStart->format('F Y') }}">
        <div class="records-stat is-dark">
            <p class="records-stat-label">Bookings</p>
            <p class="records-stat-value">{{ $bookingCount }}</p>
            <p class="records-stat-hint">Reserved this month</p>
        </div>
        <div class="records-stat">
            <p class="records-stat-label">Estimated rental fees</p>
            <p class="records-stat-value">₱{{ number_format($totalRentalFees, 2) }}</p>
            <p class="records-stat-hint">Based on published venue rates</p>
        </div>
        <div class="records-stat">
            <p class="records-stat-label">Venues used</p>
            <p class="records-stat-value">{{ $venuesUsed }}</p>
            <p class="records-stat-hint">With at least one booking</p>
        </div>
        <div class="records-stat">
            <p class="records-stat-label">Busiest venue</p>
            <p class="records-stat-value is-text">{{ $busiestVenue ?? '—' }}</p>
            <p class="records-stat-hint">{{ $busiestVenue ? $busiestCount . ' ' . \Illuminate\Support\Str::plural('booking', $busiestCount) : 'No bookings yet' }}</p>
        </div>
    </section>

    <!-- Records Table -->
    <div>
        <!-- Charts Section -->
        <h2 class="records-section-title">Trends</h2>
        <div class="records-charts">
            <!-- Monthly Trend Chart -->
            <div class="records-card">
                <h3>Booking Trend (Last 12 Months)</h3>
                <div class="records-chart"><canvas id="monthlyTrendChart"></canvas></div>
            </div>

            <!-- Facility Usage Chart -->
            <div class="records-card">
                <h3>Facility Usage ({{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }})</h3>
                <div class="records-chart"><canvas id="facilityUsageChart"></canvas></div>
            </div>
        </div>

        <h2 class="records-section-title">Booking ledger</h2>
        @if($records->count() > 0)
            <div class="records-table-wrap">
            <table class="w-full border-2 border-black text-xs">
                <thead class="text-white u-bg-ink">
                    <tr>
                        <th class="border border-black px-2 py-2 text-left">Date Requested</th>
                        <th class="border border-black px-2 py-2 text-left">Date of Activity</th>
                        <th class="border border-black px-2 py-2 text-left">Time</th>
                        <th class="border border-black px-2 py-2 text-left">Purpose</th>
                        <th class="border border-black px-2 py-2 text-left">Requester</th>
                        <th class="border border-black px-2 py-2 text-left">Contact No.</th>
                        <th class="border border-black px-2 py-2 text-left">Venue</th>
                        <th colspan="7" class="border border-black px-2 py-2 text-center u-bg-slate-800">Facilities to be Used (Quantity)</th>
                        <th class="border border-black px-2 py-2 text-right">Rental Fee</th>
                    </tr>
                    <tr>
                        <th colspan="7" class="border border-black"></th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Chairs</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Tables</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Fans</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Rostrum</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Flags</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">Sound</th>
                        <th class="border border-black px-2 py-1 text-center text-xs">LED</th>
                        <th class="border border-black"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-black px-2 py-2">{{ $record['date_requested'] }}</td>
                            <td class="border border-black px-2 py-2">{{ $record['date_of_activity'] }}</td>
                            <td class="border border-black px-2 py-2">{{ $record['time'] }}</td>
                            <td class="border border-black px-2 py-2">{{ Str::limit($record['purpose'], 40) }}</td>
                            <td class="border border-black px-2 py-2">{{ $record['requester'] }}</td>
                            <td class="border border-black px-2 py-2">{{ $record['contact_no'] }}</td>
                            <td class="border border-black px-2 py-2 font-semibold">{{ $record['venue'] }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['chairs'] > 0 ? $record['equipment']['chairs'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['tables'] > 0 ? $record['equipment']['tables'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['fans'] > 0 ? $record['equipment']['fans'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['rostrum'] > 0 ? $record['equipment']['rostrum'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['flags'] > 0 ? $record['equipment']['flags'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['sound'] > 0 ? $record['equipment']['sound'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-center">{{ $record['equipment']['led'] > 0 ? $record['equipment']['led'] : '-' }}</td>
                            <td class="border border-black px-2 py-2 text-right font-semibold">
                                @if($record['rental_fee'] > 0)
                                    ₱{{ number_format($record['rental_fee'], 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="14" class="border border-black px-2 py-2 text-right">TOTAL RENTAL FEES:</td>
                        <td class="border border-black px-2 py-2 text-right text-lg">₱{{ number_format($totalRentalFees, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            </div>
        @else
            <div class="records-empty">
                <p>No bookings found for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
            </div>
        @endif
    </div>

    <!-- Venue Usage Summary -->
    <div>
        <h3 class="records-section-title">Venues utilized · {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h3>
        @php
            $maxVenueCount = max(1, (int) $allVenues->max());
        @endphp
        <div class="records-venues">
            @foreach($allVenues as $venue => $count)
                <div class="records-venue {{ $count > 0 ? '' : 'is-unused' }}">
                    <p class="records-venue-name" title="{{ $venue }}">{{ $venue }}</p>
                    <p class="records-venue-count">{{ $count }}</p>
                    <div class="records-venue-track" aria-hidden="true"><div class="records-venue-fill" style="width: {{ round($count / $maxVenueCount * 100) }}%"></div></div>
                </div>
            @endforeach
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
window.addEventListener('load', function () {
// Monthly Trend Chart
const monthlyTrendData = {
    labels: @json(array_column($monthlyTrend, 'month')),
    datasets: [{
        label: 'Number of Bookings',
        data: @json(array_column($monthlyTrend, 'count')),
        borderColor: '#0f172a',
        backgroundColor: 'rgba(15, 23, 42, 0.08)',
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: '#0f172a',
        tension: 0.3,
        fill: true
    }]
};

new Chart(document.getElementById('monthlyTrendChart'), {
    type: 'line',
    data: monthlyTrendData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Facility Usage Chart
const facilityLabels = @json($venueSummary->keys()->toArray());
const facilityCounts = @json($venueSummary->values()->toArray());


new Chart(document.getElementById('facilityUsageChart'), {
    type: 'bar',
    data: {
        labels: facilityLabels,
        datasets: [{
            label: 'Number of Bookings',
            data: facilityCounts,
            backgroundColor: '#334155',
            hoverBackgroundColor: '#0f172a',
            borderRadius: { topLeft: 4, topRight: 4 },
            borderSkipped: 'bottom',
            maxBarThickness: 40
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 45,
                    font: {
                        size: 10
                    }
                }
            }
        }
    }
});
});
</script>
@endsection
