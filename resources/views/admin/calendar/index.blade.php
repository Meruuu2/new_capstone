@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    use Carbon\Carbon;
    $monthLabel = $currentMonth->format('F Y');
    $monthTotal = collect($facilityCounts)->sum('count');
    $venuesBooked = collect($facilityCounts)->filter(fn ($i) => $i['count'] > 0)->count();
    $busiest = collect($facilityCounts)->sortByDesc('count')->first();
    $busiest = ($busiest && $busiest['count'] > 0) ? $busiest : null;
    $rankedFacilities = collect($facilityCounts)->sortByDesc('count')->values();
    $maxCount = max(1, (int) $rankedFacilities->max('count'));
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/tiripunan-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Reservation calendar
            </p>
            <h1>{{ $monthLabel }}</h1>
            <p class="pk-hero-sub">Month view of all reserved and rescheduled reservations with facility and purpose details. All times are in Philippine Standard Time.</p>
        </div>
    </section>

    <section class="pk-stats is-3" aria-label="Summary for {{ $monthLabel }}">
        <div class="pk-stat is-dark">
            <div>
                <p class="pk-stat-label">Reservations</p>
                <p class="pk-stat-value">{{ $monthTotal }}</p>
                <p class="pk-stat-hint">Reserved or rescheduled</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Facilities booked</p>
                <p class="pk-stat-value">{{ $venuesBooked }} <span class="pk-stat-of">/ {{ count($facilityCounts) }}</span></p>
                <p class="pk-stat-hint">With at least one reservation</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
        </div>
        <div class="pk-stat">
            <div class="u-minw-0">
                <p class="pk-stat-label">Busiest facility</p>
                <p class="pk-stat-value pk-stat-value--text u-break">{{ $busiest ? $busiest['facility']->name : '—' }}</p>
                <p class="pk-stat-hint">{{ $busiest ? $busiest['count'] . ' ' . \Illuminate\Support\Str::plural('reservation', $busiest['count']) : 'No reservations yet' }}</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg></span>
        </div>
    </section>

    {{-- Shared monochrome calendar partial; navigation stays on /admin/calendar --}}
    <div class="cal-block">
        @include('public.calendar', ['calendarRoute' => 'admin.calendar', 'hideCalendarHeading' => true, 'exposeEventDetails' => true])
    </div>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Facility reservation overview ({{ $monthLabel }})</h2>
        <span class="pk-section-meta">Busiest first</span>
    </div>

    <div class="cal-rank" role="table" aria-label="Reservations this month per facility">
        <div class="cal-rank-row cal-rank-head" role="row">
            <span role="columnheader">#</span>
            <span role="columnheader">Facility</span>
            <span role="columnheader">Owner</span>
            <span role="columnheader">Status</span>
            <span role="columnheader">Reservations this month</span>
        </div>
        @foreach ($rankedFacilities as $i => $item)
            @php
                $facility = $item['facility'];
                $count    = $item['count'];
                $status   = $facility->availability_status ?? 'available';
                $tone = match ($status) { 'available' => 'is-good', 'maintenance' => 'is-warn', 'unavailable' => 'is-bad', default => 'is-neutral' };
            @endphp
            <div class="cal-rank-row {{ $count > 0 ? '' : 'is-zero' }}" role="row">
                <span class="cal-rank-pos" role="cell">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <p class="cal-rank-name" role="cell" title="{{ $facility->name }}">{{ $facility->name }}</p>
                <span class="cal-rank-owner" role="cell">
                    @if ($facility->owner_type === 'gsu')
                        GSU
                    @elseif ($facility->owner_type === 'college')
                        College ({{ $facility->owner_college ?? 'N/A' }})
                    @elseif ($facility->owner_type === 'org')
                        Organization
                    @else
                        {{ ucfirst($facility->owner_type ?? 'Unknown') }}
                    @endif
                </span>
                <span class="pk-pill {{ $tone }}" role="cell">{{ ucfirst($status) }}</span>
                <span class="cal-rank-bar" role="cell">
                    <span class="cal-rank-track" aria-hidden="true"><span class="cal-rank-fill" style="display:block;width: {{ round($count / $maxCount * 100) }}%"></span></span>
                    <span class="cal-rank-count">{{ $count }}</span>
                </span>
            </div>
        @endforeach
    </div>
</div>
@endsection
