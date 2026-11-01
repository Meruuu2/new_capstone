@extends('layouts.guest')

@section('guest-content')
@include('layouts.partials.page-kit')
@php
    $now      = \Carbon\Carbon::now();
    $hour     = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $user     = auth()->user();
    $unit     = optional($user)->organization_name ?: 'Guest account';

    // Request journey: the order a facility request moves through
    $journey = [
        ['key' => 'pending',         'label' => 'Pending',         'hint' => 'Waiting for GSU review',  'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
        ['key' => 'pending_payment', 'label' => 'Pending payment', 'hint' => 'Receipt being verified',  'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>'],
        ['key' => 'approved',        'label' => 'Approved',        'hint' => 'Cleared by GSU',          'icon' => '<path d="M20 6L9 17l-5-5"/>'],
        ['key' => 'reserved',        'label' => 'Reserved',        'hint' => 'On the calendar',         'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M9 15l2 2 4-4"/>'],
    ];

@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">{{ $now->format('l, F j, Y') }}</p>
            <h1>{{ $greeting }}, {{ optional($user)->name ?? 'there' }}</h1>
            <p class="pk-hero-sub">Welcome to the Guest Portal. Submit facility requests with payment and track your reservations.</p>
            <div class="cd-hero-meta">
                <span class="cd-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>
                    {{ $unit }}
                </span>
                <span class="cd-chip">Total Requests: {{ $facilitiesStats['total'] }}</span>
            </div>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('guest.requests.facilities.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New request
            </a>
            <a href="{{ route('guest.rates') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                Venue rates
            </a>
        </div>
    </section>

    <div class="pk-section-head">
        <h2 class="pk-section-title">My Facilities Requests</h2>
        <a href="{{ route('guest.requests.facilities.index') }}" class="pk-section-meta pk-section-link">View all →</a>
    </div>
    <section class="cd-journey" aria-label="Facility requests by status">
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
        <section class="cd-card" aria-label="Upcoming events">
            <div class="cd-card-head">
                <h3>Upcoming Events</h3>
                <a href="{{ route('guest.bookings.calendar') }}">View Full Calendar →</a>
            </div>
            @if($upcomingEvents->count() > 0)
                <div class="gd-events">
                    @foreach($upcomingEvents as $event)
                        <div class="gd-event">
                            <span class="gd-date" aria-hidden="true">
                                <span class="gd-mon">{{ $event->start_time->format('M') }}</span>
                                <span class="gd-day">{{ $event->start_time->format('d') }}</span>
                            </span>
                            <div class="u-minw-0">
                                <p class="gd-venue">{{ strtoupper($event->facilities->first()->name ?? 'N/A') }}</p>
                                <p class="gd-time">{{ $event->start_time->format('M d, Y') }} · {{ $event->start_time->format('h:i A') }} - {{ $event->end_time->format('h:i A') }}</p>
                                <p class="gd-purpose">{{ $event->purpose }}</p>
                            </div>
                            @if($event->requester_id == auth()->id())
                                <span class="gd-mine">Your Booking</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="gd-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    No upcoming events scheduled.
                </div>
            @endif
        </section>

        <section class="cd-card" aria-label="Quick access">
            <div class="cd-card-head"><h3>Quick access</h3></div>
            <div class="cd-actions">
                <a href="{{ route('guest.rates') }}" class="cd-action is-featured">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg></span>
                    <span><strong>Venue Rates</strong><span class="cd-action-sub">View official pricing and facilities packages</span></span>
                </a>
                <a href="{{ route('guest.requests.facilities.create') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M12 12v6M9 15h6"/></svg></span>
                    <span><strong>New Request</strong><span class="cd-action-sub">Submit a facility booking request with payment</span></span>
                </a>
                <a href="{{ route('guest.requests.facilities.index') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg></span>
                    <span><strong>My Requests</strong><span class="cd-action-sub">View and track your facility requests</span></span>
                </a>
                <a href="{{ route('guest.bookings.calendar') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg></span>
                    <span><strong>Calendar</strong><span class="cd-action-sub">View all facility bookings and availability</span></span>
                </a>
                <a href="{{ route('guest.bookings.index') }}" class="cd-action">
                    <span class="cd-action-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg></span>
                    <span><strong>My Bookings</strong><span class="cd-action-sub">View your confirmed reservations</span></span>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
