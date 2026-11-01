@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $ticketTone = fn ($s) => match ($s) {
        'pending' => 'is-warn',
        'ongoing' => 'is-info',
        'success' => 'is-good',
        'replacement' => 'is-bad',
        default   => 'is-neutral',
    };
    $yearTotal     = $months->sum('total');
    $yearCompleted = array_sum($completedByMonth);
    $yearOpen      = $months->sum('open') + $months->sum('ongoing');
    $monthName     = fn ($m) => \Carbon\Carbon::create(null, $m, 1)->format('F');
    $isCurrentYear = $year >= now()->year;
    $monthLabels   = collect(range(1, 12))->map(fn ($m) => \Carbon\Carbon::create(null, $m, 1)->format('M'))->all();
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                Repair records
            </p>
            <h1>Repair &amp; maintenance · {{ $year }}</h1>
            <p class="pk-hero-sub">Requests received and completed each month of the year.</p>
        </div>
        <div class="pk-hero-actions rr-print-hide">
            <a href="{{ route('admin.repair-records.index', ['year' => $year - 1]) }}" class="pk-btn-ghost" aria-label="Previous year">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                {{ $year - 1 }}
            </a>
            @unless ($isCurrentYear)
                <a href="{{ route('admin.repair-records.index', ['year' => $year + 1]) }}" class="pk-btn-ghost" aria-label="Next year">
                    {{ $year + 1 }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            @endunless
            <button type="button" onclick="window.print()" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>
                Print
            </button>
        </div>
    </section>

    <section class="pk-stats" aria-label="Summary for {{ $year }}">
        <div class="pk-stat is-dark">
            <div>
                <p class="pk-stat-label">Received</p>
                <p class="pk-stat-value">{{ $yearTotal }}</p>
                <p class="pk-stat-hint">Repair requests in {{ $year }}</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Completed</p>
                <p class="pk-stat-value">{{ $yearCompleted }}</p>
                <p class="pk-stat-hint">Marked success in {{ $year }}</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Still open</p>
                <p class="pk-stat-value">{{ $yearOpen }}</p>
                <p class="pk-stat-hint">Received this year, not finished</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Average time to complete</p>
                <p class="pk-stat-value">{{ $avgDays ?? '—' }}@if ($avgDays !== null)<span class="pk-stat-of"> days</span>@endif</p>
                <p class="pk-stat-hint">From received to success</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></span>
        </div>
    </section>

    <section class="rr-grid">
        <div class="dash-card">
            <div class="dash-card-head">
                <h3>Requests per Month</h3>
                <span>{{ $year }}</span>
            </div>
            <div class="dash-chart">
                <canvas id="repairRecordsChart" role="img" aria-label="Repair requests received and completed per month in {{ $year }}"></canvas>
            </div>
        </div>

        <div>
            <div class="pk-section-head">
                <h2 class="pk-section-title">Monthly records</h2>
                <span class="pk-section-meta">By month received · pick a month to list its requests</span>
            </div>
            <div class="pk-table-wrap">
                <table class="pk-table rr-table">
                    <thead>
                        <tr><th>Month</th><th>Received</th><th>Open</th><th>Ongoing</th><th>Success</th><th>Replacement</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($months as $m => $row)
                            <tr class="{{ $m === $month ? 'is-selected' : '' }}">
                                <td><a href="{{ route('admin.repair-records.index', ['year' => $year, 'month' => $m]) }}#month-tickets" {{ $m === $month ? 'aria-current=true' : '' }}>{{ $monthName($m) }}</a></td>
                                <td><strong>{{ $row['total'] }}</strong></td>
                                <td>{{ $row['open'] ?: '–' }}</td>
                                <td>{{ $row['ongoing'] ?: '–' }}</td>
                                <td>{{ $row['success'] ?: '–' }}</td>
                                <td>{{ $row['replacement'] ?: '–' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ $yearTotal }}</td>
                            <td>{{ $months->sum('open') }}</td>
                            <td>{{ $months->sum('ongoing') }}</td>
                            <td>{{ $months->sum('success') }}</td>
                            <td>{{ $months->sum('replacement') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

    <section id="month-tickets">
        <div class="pk-section-head">
            <h2 class="pk-section-title">{{ $monthName($month) }} {{ $year }}</h2>
            <span class="pk-section-meta">{{ $tickets->count() }} {{ \Illuminate\Support\Str::plural('request', $tickets->count()) }}</span>
        </div>
        @if ($tickets->isNotEmpty())
            <div class="pk-table-wrap">
                <table class="pk-table">
                    <thead>
                        <tr><th>Code</th><th>Subject</th><th>Sender</th><th>Facility</th><th>Status</th><th>Received</th><th>Completed</th><th class="rr-print-hide"><span class="pk-sr">Actions</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td><span class="pk-code">{{ $ticket->ticket_code }}</span></td>
                                <td>{{ $ticket->subject }}</td>
                                <td>{{ $ticket->requester?->name ?? 'Unknown' }}</td>
                                <td>{{ $ticket->facility?->name ?? $ticket->facility_other ?? 'Facility removed' }}</td>
                                <td><span class="pk-pill {{ $ticketTone($ticket->status) }}">{{ ucfirst($ticket->status) }}</span></td>
                                <td class="pk-nowrap">{{ $ticket->created_at->format('M d, Y') }}</td>
                                <td class="pk-nowrap">{{ $ticket->completed_at?->format('M d, Y') ?? '–' }}</td>
                                <td class="rr-print-hide">
                                    <a class="pk-row-link" href="{{ route('admin.maintenance-tickets.show', $ticket) }}">
                                        Review
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="pk-empty">
                <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/></svg></span>
                <h3>No repair requests</h3>
                <p>Nothing was received in {{ $monthName($month) }} {{ $year }}.</p>
            </div>
        @endif
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
window.addEventListener('load', function () {
    const bar = { borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', maxBarThickness: 22 };
    new Chart(document.getElementById('repairRecordsChart'), {
        type: 'bar',
        data: {
            labels: @json($monthLabels),
            datasets: [
                { label: 'Received', data: @json($months->pluck('total')->values()), backgroundColor: '#334155', hoverBackgroundColor: '#0f172a', ...bar },
                { label: 'Completed', data: @json(array_values($completedByMonth)), backgroundColor: '#1baf7a', hoverBackgroundColor: '#14845c', ...bar }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, border: { color: '#E5E5E5' }, ticks: { color: '#525252', font: { family: 'Inter', size: 12 } } },
                y: { beginAtZero: true, grid: { color: '#F0F0F0' }, border: { display: false }, ticks: { precision: 0, color: '#737373', font: { family: 'Inter', size: 12 } } }
            },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, color: '#404040', font: { family: 'Inter', size: 12 } } },
                tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8, titleFont: { family: 'Inter', weight: '600' }, bodyFont: { family: 'Inter' } }
            }
        }
    });
});
</script>
@endsection
