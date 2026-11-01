@extends('layouts.guest')

@section('guest-content')
@include('layouts.partials.page-kit')
@php
    $monthLabel = $currentMonth->format('F Y');
    $dayList    = collect($days ?? [])->map(fn ($list, $date) => ['date' => \Carbon\Carbon::parse($date), 'bookings' => collect($list)]);
    $monthTotal = $dayList->sum(fn ($d) => $d['bookings']->count());
    $busiest    = $dayList->sortByDesc(fn ($d) => $d['bookings']->count())->first();
    $maxPerDay  = max(1, (int) optional($busiest)['bookings']?->count());
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Reservation calendar
            </p>
            <h1>{{ $monthLabel }}</h1>
            <p class="pk-hero-sub">Check which dates and venues are already taken before you apply. All times are in Philippine Standard Time.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('guest.requests.facilities.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Request a facility
            </a>
        </div>
    </section>

    <section class="pk-stats is-3" aria-label="Summary for {{ $monthLabel }}">
        <div class="pk-stat is-dark">
            <div>
                <p class="pk-stat-label">Reservations</p>
                <p class="pk-stat-value">{{ $monthTotal }}</p>
                <p class="pk-stat-hint">Campus-wide this month</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Booked days</p>
                <p class="pk-stat-value">{{ $dayList->count() }} <span class="pk-stat-of">/ {{ $currentMonth->daysInMonth }}</span></p>
                <p class="pk-stat-hint">Days with at least one booking</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></span>
        </div>
        <div class="pk-stat">
            <div class="u-minw-0">
                <p class="pk-stat-label">Busiest day</p>
                <p class="pk-stat-value pk-stat-value--text">{{ $busiest ? $busiest['date']->format('D, M j') : '—' }}</p>
                <p class="pk-stat-hint">{{ $busiest ? $busiest['bookings']->count() . ' ' . \Illuminate\Support\Str::plural('booking', $busiest['bookings']->count()) . ' — plan around it' : 'No bookings yet' }}</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></span>
        </div>
    </section>

    @if (!empty($unavailableFacilities) && $unavailableFacilities->isNotEmpty())
        <div class="gcal-alert" role="note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div>
                <strong>Not available for booking right now</strong>
                <div class="gcal-chips">
                    @foreach ($unavailableFacilities as $facility)
                        <span class="pk-pill {{ $facility->availability_status === 'maintenance' ? 'is-warn' : 'is-bad' }}">{{ $facility->name }} · {{ $facility->availability_status === 'maintenance' ? 'Under Maintenance' : 'Unavailable' }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="ocal-block">
        @include('public.calendar', ['calendarRoute' => 'guest.bookings.calendar', 'hideCalendarHeading' => true])
    </div>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Booked days · {{ $monthLabel }}</h2>
        <span class="pk-section-meta">In date order</span>
    </div>
    @if ($dayList->isNotEmpty())
        <div class="ocal-days">
            @foreach ($dayList->sortBy(fn ($d) => $d['date']) as $d)
                @php $n = $d['bookings']->count(); @endphp
                <article class="ocal-day">
                    <span class="ocal-date" aria-hidden="true">
                        <span class="ocal-dow">{{ $d['date']->format('D') }}</span>
                        <span class="ocal-num">{{ $d['date']->format('d') }}</span>
                    </span>
                    <div class="u-minw-0">
                        <p class="ocal-count"><span>{{ $d['date']->format('M j') }}</span><span>{{ $n }} {{ \Illuminate\Support\Str::plural('booking', $n) }}</span></p>
                        <div class="ocal-bar" aria-hidden="true"><span style="width: {{ round($n / $maxPerDay * 100) }}%"></span></div>
                        <p class="ocal-venues" title="{{ $d['bookings']->flatMap(fn ($b) => $b->facilities->pluck('name'))->unique()->join(', ') }}">
                            {{ $d['bookings']->flatMap(fn ($b) => $b->facilities->pluck('name'))->unique()->join(', ') ?: 'Venue not set' }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
            <h3>Wide open this month</h3>
            <p>No facilities are booked in {{ $monthLabel }} yet.</p>
        </div>
    @endif
</div>
@endsection
