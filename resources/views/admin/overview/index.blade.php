@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $monthLabel = $currentMonth->format('F Y');
    $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
    $labels = array_keys($series);   // facility labels
    $data   = array_values($series); // counts
    $total      = array_sum($data);
    $usedCount  = count(array_filter($data, fn ($c) => $c > 0));
    $topCount   = $data ? max($data) : 0;
    $topLabel   = $topCount > 0 ? array_search($topCount, $series, true) : null;
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/grand-stand.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 6-6"/></svg>
                Monthly overview
            </p>
            <div class="ov-period">
                <a href="{{ route('admin.overview', ['month' => $prevMonth]) }}" class="ov-step" aria-label="Previous month" title="{{ $currentMonth->copy()->subMonth()->format('F Y') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <h1>{{ $monthLabel }}</h1>
                <a href="{{ route('admin.overview', ['month' => $nextMonth]) }}" class="ov-step" aria-label="Next month" title="{{ $currentMonth->copy()->addMonth()->format('F Y') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </div>
            <p class="pk-hero-sub">Reservations this month by facility, including completed bookings and every venue in a multi-facility reservation.</p>
        </div>
    </section>

    <section class="pk-stats is-3" aria-label="Summary for {{ $monthLabel }}">
        <div class="pk-stat is-dark">
            <div>
                <p class="pk-stat-label">Reservations</p>
                <p class="pk-stat-value">{{ $total }}</p>
                <p class="pk-stat-hint">Reserved, rescheduled, or completed</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 6-6"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Facilities in use</p>
                <p class="pk-stat-value">{{ $usedCount }} <span class="pk-stat-of">/ {{ count($labels) }}</span></p>
                <p class="pk-stat-hint">Including "OTHERS"</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
        </div>
        <div class="pk-stat">
            <div class="u-minw-0">
                <p class="pk-stat-label">Top facility</p>
                <p class="pk-stat-value pk-stat-value--text u-break">{{ $topLabel ?? '—' }}</p>
                <p class="pk-stat-hint">{{ $topLabel ? $topCount . ' ' . \Illuminate\Support\Str::plural('reservation', $topCount) : 'No reservations yet' }}</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></span>
        </div>
    </section>

    <div class="ov-card">
        <div class="ov-card-head">
            <h3>Reservations per facility</h3>
            <span>{{ $monthLabel }}</span>
        </div>
        <div class="ov-chart"><canvas id="fmsOverviewChart" role="img" aria-label="Bar chart of reservations per facility for {{ $monthLabel }}"></canvas></div>

        <details class="ov-table-toggle">
            <summary>Show as table</summary>
            <div class="pk-table-wrap">
                <table class="pk-table">
                    <thead><tr><th>Facility</th><th class="u-text-right">Reservations</th></tr></thead>
                    <tbody>
                        @foreach ($series as $label => $count)
                            <tr><td class="pk-strong">{{ $label }}</td><td class="u-text-right">{{ $count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    <div class="ov-card">
        <div class="ov-card-head">
            <h3>Facility occupancy</h3>
            <span>{{ $monthLabel }}</span>
        </div>
        <p class="ov-method-note">Utilization is booked hours ÷ {{ number_format($availableCalendarHours, 0) }} calendar hours for this month. Operating hours are not configured.</p>
        <div class="pk-table-wrap">
            <table class="pk-table">
                <thead><tr><th>Facility</th><th class="u-text-right">Booked hours</th><th class="u-text-right">Available calendar hours</th><th class="u-text-right">Utilization</th></tr></thead>
                <tbody>
                    @foreach ($facilityUtilization as $row)
                        <tr>
                            <td class="pk-strong">{{ $row['name'] }}</td>
                            <td class="u-text-right">{{ number_format($row['booked_hours'], 2) }}</td>
                            <td class="u-text-right">{{ number_format($row['available_hours'], 0) }}</td>
                            <td class="u-text-right"><strong>{{ number_format($row['utilization'], 2) }}%</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Inside the section: layouts have no @stack('scripts'), so a pushed script never ran --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
    window.addEventListener('load', function () {
    (function () {
        const labels = @json($labels);
        const data   = @json($data);
        const canvas = document.getElementById('fmsOverviewChart');
        if (!canvas || typeof Chart === 'undefined') return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Reservations',
                    data: data,
                    backgroundColor: '#334155',
                    hoverBackgroundColor: '#0f172a',
                    borderRadius: { topRight: 4, bottomRight: 4 },
                    borderSkipped: 'left',
                    maxBarThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a', padding: 10, displayColors: false,
                        callbacks: { label: (ctx) => ctx.parsed.x + (ctx.parsed.x === 1 ? ' reservation' : ' reservations') }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#64748B', font: { family: 'Inter', size: 11 } },
                        grid: { color: '#F1F5F9' },
                        border: { display: false }
                    },
                    y: {
                        ticks: { color: '#334155', font: { family: 'Inter', size: 12, weight: '600' } },
                        grid: { display: false },
                        border: { color: '#E2E8F0' }
                    }
                }
            }
        });
    })();
    });
</script>
@endsection
