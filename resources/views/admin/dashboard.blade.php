@extends('layouts.admin')

@section('admin-content')
@php
  $now        = \Carbon\Carbon::now();
  $hour       = (int) $now->format('G');
  $greeting   = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $adminName  = optional(auth()->user())->name ?? 'Admin';
  $openTickets = ($maintenanceStats['pending'] ?? 0) + ($maintenanceStats['notified'] ?? 0) + ($maintenanceStats['ongoing'] ?? 0);
@endphp

<div class="dash">

  {{-- Welcome banner --}}
  <section class="dash-hero" style="--dash-hero-image: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
    <div>
      <p class="dash-hero-date">{{ $now->format('l, F j, Y') }}</p>
      <h1>{{ $greeting }}, {{ $adminName }}</h1>
      <p>Here's what's happening across University of Antique facilities today.</p>
    </div>
    <div class="dash-hero-actions">
      <a href="{{ route('admin.forms.facilities.index') }}" class="dash-hero-btn primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        All requests
      </a>
      <a href="{{ route('admin.bookings.create-direct') }}" class="dash-hero-btn ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        New reservation
      </a>
    </div>
  </section>

  {{-- KPI tiles --}}
  <section class="dash-stats" aria-label="Key figures">
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
    <div class="dash-stat">
      <div class="dash-stat-top">
        <span class="dash-stat-label">Open maintenance</span>
        <span class="dash-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg></span>
      </div>
      <span class="dash-stat-value">{{ $openTickets }}</span>
      <span class="dash-stat-hint">Pending, notified &amp; in progress</span>
    </div>
  </section>

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

  {{-- Charts --}}
  <section class="dash-charts">
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
  </section>

  {{-- Quick access --}}
  <section class="quick-access-section">
    <h2>Quick access</h2>
    <div class="admin-grid">
      @php
        $arrow = '<svg class="admin-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>';
      @endphp
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
      <a href="{{ route('admin.users.index') }}" class="admin-card">
        {!! $arrow !!}
        <span class="admin-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.9.4 5 2.8 5 5.8"/></svg></span>
        <div>
          <h3>User Management</h3>
          <p>Create and manage portal accounts</p>
        </div>
      </a>
    </div>
  </section>
</div>

<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
window.addEventListener('load', function () {
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

// Maintenance tickets (Chart.js bar). Categories are named on the axis, so one slate hue carries magnitude.
const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
new Chart(maintenanceCtx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Notified', 'Ongoing', 'Success', 'Failed'],
        datasets: [{
            label: 'Tickets',
            data: [
                {{ $maintenanceStats['pending'] }},
                {{ $maintenanceStats['notified'] }},
                {{ $maintenanceStats['ongoing'] }},
                {{ $maintenanceStats['success'] }},
                {{ $maintenanceStats['failed'] }}
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
});
</script>
@endsection
