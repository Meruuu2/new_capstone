@extends('layouts.admin')

@section('admin-content')
@php
  $now        = \Carbon\Carbon::now();
  $hour       = (int) $now->format('G');
  $greeting   = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $adminName  = optional(auth()->user())->name ?? 'Admin';
  $openTickets = ($maintenanceStats['pending'] ?? 0) + ($maintenanceStats['notified'] ?? 0) + ($maintenanceStats['ongoing'] ?? 0);
  $repairOnly  = $showRepair && ! $showFacilities;
  $monthLabels = collect(range(1, 12))->map(fn ($m) => \Carbon\Carbon::create(null, $m, 1)->format('M'))->all();
  // Year switch on the monthly charts; keeps the page on the dashboard
  $yearNav = fn () => '<span class="dash-year">'
      . '<a href="' . e(route('admin.dashboard', ['year' => $year - 1])) . '" aria-label="Previous year">&lsaquo;</a>'
      . '<strong>' . $year . '</strong>'
      . ($year < now()->year ? '<a href="' . e(route('admin.dashboard', ['year' => $year + 1])) . '" aria-label="Next year">&rsaquo;</a>' : '<span aria-hidden="true">&rsaquo;</span>')
      . '</span>';
@endphp

<div class="dash">

  {{-- Welcome banner --}}
  <section class="dash-hero" style="--dash-hero-image: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
    <div>
      <p class="dash-hero-date">{{ $now->format('l, F j, Y') }}</p>
      <h1>{{ $greeting }}, {{ $adminName }}</h1>
      <p>{{ $repairOnly ? "Here's the state of repair and maintenance requests today." : "Here's what's happening across University of Antique facilities today." }}</p>
    </div>
    <div class="dash-hero-actions">
      @if ($repairOnly)
      <a href="{{ route('admin.maintenance-tickets.index') }}" class="dash-hero-btn primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg>
        Repair requests
      </a>
      <a href="{{ route('admin.repair-records.index') }}" class="dash-hero-btn ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
        Repair records
      </a>
      @else
      <a href="{{ route('admin.forms.facilities.index') }}" class="dash-hero-btn primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        All requests
      </a>
      <a href="{{ route('admin.bookings.create-direct') }}" class="dash-hero-btn ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        New reservation
      </a>
      @endif
    </div>
  </section>

  {{-- KPI tiles --}}
  <section class="dash-stats" aria-label="Key figures">
    @if ($showFacilities)
    <div class="dash-stat is-dark">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Facility requests</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M8 13h8M8 17h5"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $facilitiesStats['total'] }}</span>
      <span class="dash-stat-hint">All submitted requests</span>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Awaiting approval</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $facilitiesStats['pending'] }}</span>
      <span class="dash-stat-hint">Pending review</span>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Reserved</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M9 15l2 2 4-4"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $facilitiesStats['reserved'] }}</span>
      <span class="dash-stat-hint">Confirmed bookings</span>
    </div>
    @endif
    @unless ($showRepair)
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Awaiting payment</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $facilitiesStats['pending_payment'] }}</span>
      <span class="dash-stat-hint">Approved, payment not verified</span>
    </div>
    @endunless
    @if ($repairOnly)
    <div class="dash-stat is-dark">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Repair requests</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M8 13h8M8 17h5"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $maintenanceStats['total'] }}</span>
      <span class="dash-stat-hint">All tickets received</span>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">In progress</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $maintenanceStats['ongoing'] }}</span>
      <span class="dash-stat-hint">Ongoing repairs</span>
    </div>
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Completed</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $maintenanceStats['success'] }}</span>
      <span class="dash-stat-hint">Marked success</span>
    </div>
    @endif
    @if ($showRepair)
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Open maintenance</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $openTickets }}</span>
      <span class="dash-stat-hint">Pending, notified &amp; in progress</span>
    </div>
    @endif
  </section>

  @if ($showFacilities)
  <section class="dash-availability" aria-label="Current facility availability">
    <div class="dash-availability-item">
      <span class="dash-availability-dot is-available" aria-hidden="true"></span>
      <span>Available</span>
      <strong>{{ $availabilityStats['available'] }}</strong>
    </div>
    <div class="dash-availability-item">
      <span class="dash-availability-dot is-unavailable" aria-hidden="true"></span>
      <span>Unavailable</span>
      <strong>{{ $availabilityStats['unavailable'] }}</strong>
    </div>
    <div class="dash-availability-item">
      <span class="dash-availability-dot is-maintenance" aria-hidden="true"></span>
      <span>Under maintenance</span>
      <strong>{{ $availabilityStats['maintenance'] }}</strong>
    </div>
  </section>
  @endif

  {{-- Charts --}}
  <section class="dash-charts">
    @if ($showFacilities)
    <!-- Facilities Requests Chart (Donut) -->
    <div class="dash-card">
      <div class="dash-card-head">
        <h3>Facilities Requests Overview</h3>
        <span>By status</span>
      </div>
      <div class="dash-chart">
        <div id="facilitiesChart3D"></div>
        @if (($facilitiesStats['total'] ?? 0) == 0)
          <div class="dash-chart-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.2 15.9A10 10 0 1 1 8 2.8"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
            No facility requests yet
          </div>
        @endif
      </div>
      <div class="dash-card-foot">
        <p>Total Requests: <strong>{{ $facilitiesStats['total'] }}</strong></p>
      </div>
    </div>

    <!-- Reservations per month -->
    <div class="dash-card">
      <div class="dash-card-head">
        <h3>Reservations per Month</h3>
        {!! $yearNav() !!}
      </div>
      <div class="dash-chart">
        <canvas id="reservationsMonthlyChart" aria-label="Reservations per month in {{ $year }}" role="img"></canvas>
      </div>
      <div class="dash-card-foot">
        <p>Reservations in {{ $year }}: <strong>{{ array_sum($reservationsByMonth) }}</strong> · <a href="{{ route('admin.monthly-records.index', ['year' => $year]) }}">Monthly records</a></p>
      </div>
    </div>
    @endif

    @if ($showRepair)
    <!-- Maintenance Tickets Chart -->
    <div class="dash-card">
      <div class="dash-card-head">
        <h3>Repair &amp; Maintenance Overview</h3>
        <span>Tickets by status</span>
      </div>
      <div class="dash-chart">
        <canvas id="maintenanceChart"></canvas>
      </div>
      <div class="dash-card-foot">
        <p>Total Tickets: <strong>{{ $maintenanceStats['total'] }}</strong></p>
      </div>
    </div>

    <!-- Repair tickets per month -->
    <div class="dash-card">
      <div class="dash-card-head">
        <h3>Repair Requests per Month</h3>
        {!! $yearNav() !!}
      </div>
      <div class="dash-chart">
        <canvas id="repairMonthlyChart" aria-label="Repair requests received and completed per month in {{ $year }}" role="img"></canvas>
      </div>
      <div class="dash-card-foot">
        <p>Received in {{ $year }}: <strong>{{ array_sum($ticketsByMonth) }}</strong> · Completed: <strong>{{ array_sum($completedByMonth) }}</strong> · <a href="{{ route('admin.repair-records.index', ['year' => $year]) }}">Repair records</a></p>
      </div>
    </div>
    @endif
  </section>

  {{-- Quick access --}}
  <section class="quick-access-section">
    <h2>Quick access</h2>
    <div class="admin-grid">
      @php
        $arrow = '<svg class="admin-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>';
      @endphp
      @if ($showFacilities)
      <a href="{{ route('admin.facilities.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
        <div>
          <h3>Facilities</h3>
          <p>Manage campus facilities and equipment</p>
        </div>
      </a>
      <a href="{{ route('admin.forms.facilities.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
        <div>
          <h3>All Requests</h3>
          <p>Review and approve facility utilization submissions</p>
        </div>
      </a>
      <a href="{{ route('admin.bookings.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></span>
        <div>
          <h3>Bookings</h3>
          <p>View and adjust confirmed bookings</p>
        </div>
      </a>
      @endif
      @if ($showRepair)
      <a href="{{ route('admin.maintenance-tickets.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg></span>
        <div>
          <h3>Repair &amp; Maintenance</h3>
          <p>Open requests and completed tasks</p>
        </div>
      </a>
      <a href="{{ route('admin.repair-records.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg></span>
        <div>
          <h3>Repair Records</h3>
          <p>Monthly repair requests for the whole year</p>
        </div>
      </a>
      @endif
      @if (auth()->user()->isAdmin())
      <a href="{{ route('admin.users.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.9.4 5 2.8 5 5.8"/></svg></span>
        <div>
          <h3>User Management</h3>
          <p>Create and manage portal accounts</p>
        </div>
      </a>
      @endif
    </div>
  </section>
</div>

@if ($showFacilities)
<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" defer></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
window.addEventListener('load', function () {
const monthLabels = @json($monthLabels);
// Shared look for the monthly bar charts (matches the ticket status chart below)
const monthlyOptions = (legend) => ({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { grid: { display: false }, border: { color: '#E5E5E5' }, ticks: { color: '#525252', font: { family: 'Inter', size: 12 } } },
        y: { beginAtZero: true, grid: { color: '#F0F0F0' }, border: { display: false }, ticks: { precision: 0, color: '#737373', font: { family: 'Inter', size: 12 } } }
    },
    plugins: {
        legend: legend
            ? { display: true, position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, color: '#404040', font: { family: 'Inter', size: 12 } } }
            : { display: false },
        tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8, titleFont: { family: 'Inter', weight: '600' }, bodyFont: { family: 'Inter' } }
    }
});
const monthlyBar = { borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', maxBarThickness: 28 };

@if ($showFacilities)
new Chart(document.getElementById('reservationsMonthlyChart'), {
    type: 'bar',
    data: { labels: monthLabels, datasets: [{ label: 'Reservations', data: @json(array_values($reservationsByMonth)), backgroundColor: '#334155', hoverBackgroundColor: '#0f172a', ...monthlyBar }] },
    options: monthlyOptions(false)
});

// Facilities Requests donut (Plotly). Categorical slots 1–5 of the validated palette, in fixed order;
// slices keep visible labels because three slots sit below 3:1 contrast on white.
const facilitiesTotal = {{ (int) $facilitiesStats['total'] }};
const facilitiesData = [{
    values: [
        {{ $facilitiesStats['pending'] }},
        {{ $facilitiesStats['pending_payment'] }},
        {{ $facilitiesStats['approved'] }},
        {{ $facilitiesStats['reserved'] }},
        {{ $facilitiesStats['disapproved'] }}
    ],
    labels: [
        'Pending · {{ $facilitiesStats['pending'] }}',
        'Pending Payment · {{ $facilitiesStats['pending_payment'] }}',
        'Approved · {{ $facilitiesStats['approved'] }}',
        'Reserved · {{ $facilitiesStats['reserved'] }}',
        'Disapproved · {{ $facilitiesStats['disapproved'] }}'
    ],
    type: 'pie',
    hole: 0.58,
    domain: { x: [0, 0.6] }, // donut on the left, legend gets the right 40% so labels never collide
    sort: false,
    direction: 'clockwise',
    marker: {
        colors: ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4'],
        line: { color: '#ffffff', width: 2 } // 2px surface gap between slices
    },
    textinfo: 'percent',
    textposition: 'outside',
    textfont: { family: 'Inter, sans-serif', size: 12, color: '#404040' },
    hovertemplate: '<b>%{label}</b> request(s)<br>%{percent} of total<extra></extra>',
    automargin: true
}];

const facilitiesLayout = {
    height: 290,
    margin: { t: 16, b: 16, l: 16, r: 16 },
    paper_bgcolor: 'rgba(0,0,0,0)',
    plot_bgcolor: 'rgba(0,0,0,0)',
    font: { family: 'Inter, sans-serif', color: '#404040' },
    showlegend: true,
    legend: { orientation: 'v', x: 0.7, xanchor: 'left', y: 0.5, yanchor: 'middle', font: { size: 13 }, itemclick: false, itemdoubleclick: false },
    annotations: facilitiesTotal > 0 ? [{
        text: '<b style="font-size:26px">' + facilitiesTotal + '</b><br><span style="font-size:12px;color:#737373">requests</span>',
        x: 0.3, xref: 'paper', y: 0.5, yref: 'paper',
        showarrow: false,
        font: { family: 'Inter, sans-serif', color: '#111111' }
    }] : []
};

const facilitiesConfig = {
    responsive: true,
    displayModeBar: false
};

Plotly.newPlot('facilitiesChart3D', facilitiesData, facilitiesLayout, facilitiesConfig);
@endif

@if ($showRepair)
new Chart(document.getElementById('repairMonthlyChart'), {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [
            { label: 'Received', data: @json(array_values($ticketsByMonth)), backgroundColor: '#334155', hoverBackgroundColor: '#0f172a', ...monthlyBar },
            { label: 'Completed', data: @json(array_values($completedByMonth)), backgroundColor: '#1baf7a', hoverBackgroundColor: '#14845c', ...monthlyBar }
        ]
    },
    options: monthlyOptions(true)
});

// Maintenance tickets (Chart.js bar). Categories are named on the axis, so one slate hue carries magnitude.
const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
new Chart(maintenanceCtx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Notified', 'Ongoing', 'Success', 'Replacement'],
        datasets: [{
            label: 'Tickets',
            data: [
                {{ $maintenanceStats['pending'] }},
                {{ $maintenanceStats['notified'] }},
                {{ $maintenanceStats['ongoing'] }},
                {{ $maintenanceStats['success'] }},
                {{ $maintenanceStats['replacement'] }}
            ],
            backgroundColor: '#334155',
            hoverBackgroundColor: '#0f172a',
            borderRadius: { topLeft: 4, topRight: 4 },
            borderSkipped: 'bottom',
            maxBarThickness: 44
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: { display: false },
                border: { color: '#E5E5E5' },
                ticks: { color: '#525252', font: { family: 'Inter', size: 12 } }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#F0F0F0' },
                border: { display: false },
                ticks: { stepSize: 1, precision: 0, color: '#737373', font: { family: 'Inter', size: 12 } }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 10,
                cornerRadius: 8,
                titleFont: { family: 'Inter', weight: '600' },
                bodyFont: { family: 'Inter' },
                displayColors: false
            }
        }
    }
});
@endif
});
</script>
@endsection
