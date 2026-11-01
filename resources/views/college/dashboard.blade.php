@extends('layouts.college')

@section('college-content')
@include('layouts.partials.page-kit')
@php
    $now      = \Carbon\Carbon::now();
    $hour     = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $user     = auth()->user();
    $college  = optional($user)->college_name ?: 'Your college';

    // Request journey: the order a facility request moves through
    $journey = [
        ['key' => 'pending',         'label' => 'Pending',         'hint' => 'Waiting for GSU review',  'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
        ['key' => 'approved',        'label' => 'Approved',        'hint' => 'Cleared by GSU',          'icon' => '<path d="M20 6L9 17l-5-5"/>'],
        ['key' => 'reserved',        'label' => 'Reserved',        'hint' => 'On the calendar',         'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M9 15l2 2 4-4"/>'],
    ];

    $repairRows = [
        ['label' => 'Pending',     'count' => $maintenanceStats['pending'] ?? 0],
        ['label' => 'Notified',    'count' => $maintenanceStats['notified'] ?? 0],
        ['label' => 'Ongoing',     'count' => $maintenanceStats['ongoing'] ?? 0],
        ['label' => 'Success',     'count' => $maintenanceStats['success'] ?? 0],
        ['label' => 'Failed',      'count' => $maintenanceStats['failed'] ?? 0],
    ];
    $repairMax = max(1, collect($repairRows)->max('count'));
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">{{ $now->format('l, F j, Y') }}</p>
            <h1>{{ $greeting }}, {{ optional($user)->name ?? 'there' }}</h1>
            <p class="pk-hero-sub">Manage your college facilities, bookings, and requests from one place.</p>
            <div class="cd-hero-meta">
                <span class="cd-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/></svg>
                    {{ $college }}
                </span>
                <span class="cd-chip">{{ $facilitiesStats['total'] }} {{ \Illuminate\Support\Str::plural('request', $facilitiesStats['total']) }} · {{ $maintenanceStats['total'] }} repair {{ \Illuminate\Support\Str::plural('ticket', $maintenanceStats['total']) }}</span>
            </div>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('college.requests.facilities.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New request
            </a>
            <a href="{{ route('college.calendar') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                Calendar
            </a>
        </div>
    </section>

    <div class="pk-section-head">
        <h2 class="pk-section-title">My facility requests</h2>
        <a href="{{ route('college.requests.index') }}" class="pk-section-meta pk-section-link">View all →</a>
    </div>
    <section class="cd-journey is-3" aria-label="Facility requests by status">
        @foreach ($journey as $i => $step)
            @php $count = $facilitiesStats[$step['key']] ?? 0; @endphp
            <div class="cd-step {{ $count > 0 ? 'is-lit' : '' }}">
                <div class="cd-step-top">
                    <span class="cd-step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $step['icon'] !!}</svg></span>
                    <span class="cd-step-num">STEP {{ $i + 1 }}</span>
                </div>
                <p class="cd-step-value">{{ $count }}</p>
                <p class="cd-step-label">{{ $step['label'] }}</p>
                <p class="cd-step-hint">{{ $step['hint'] }}</p>
            </div>
        @endforeach
        <div class="cd-step is-off">
            <div class="cd-step-top">
                <span class="cd-step-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></span>
            </div>
            <p class="cd-step-value">{{ $facilitiesStats['disapproved'] }}</p>
            <p class="cd-step-label">Disapproved</p>
            <p class="cd-step-hint">Not approved by GSU</p>
        </div>
    </section>

    <div class="cd-grid">
        <section class="cd-card" aria-label="Repair requests by status">
            <div class="cd-card-head">
                <h3>My Repair Requests</h3>
                <a href="{{ route('college.maintenance-tickets.index') }}">View tickets →</a>
            </div>
            <div class="cd-repair">
                @foreach ($repairRows as $row)
                    <div class="cd-repair-row {{ $row['count'] > 0 ? '' : 'is-zero' }}">
                        <span class="cd-repair-label">{{ $row['label'] }}</span>
                        <span class="cd-repair-track" aria-hidden="true"><span class="cd-repair-fill" style="width: {{ round($row['count'] / $repairMax * 100) }}%"></span></span>
                        <span class="cd-repair-count">{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="cd-card-foot">Total Tickets: <strong>{{ $maintenanceStats['total'] }}</strong></div>
        </section>

        <section class="cd-card" aria-label="Quick access">
            <div class="cd-card-head"><h3>Quick access</h3></div>
            <div class="cd-actions">
                <a href="{{ route('college.requests.facilities.create') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M12 12v6M9 15h6"/></svg></span>
                    <span><strong>Utilization request</strong><span class="cd-action-sub">Submit a request to GSU</span></span>
                </a>
                <a href="{{ route('college.requests.index') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg></span>
                    <span><strong>My requests</strong><span class="cd-action-sub">Track request status</span></span>
                </a>
                <a href="{{ route('college.bookings.index') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg></span>
                    <span><strong>Reservations</strong><span class="cd-action-sub">Your confirmed bookings</span></span>
                </a>
                <a href="{{ route('college.facilities.index') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
                    <span><strong>My facilities</strong><span class="cd-action-sub">Facilities owned by your college</span></span>
                </a>
                <a href="{{ route('college.maintenance-tickets.create') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg></span>
                    <span><strong>Report a repair</strong><span class="cd-action-sub">Send a maintenance ticket</span></span>
                </a>
                <a href="{{ route('college.calendar') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></span>
                    <span><strong>Reservation calendar</strong><span class="cd-action-sub">See what's booked</span></span>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
