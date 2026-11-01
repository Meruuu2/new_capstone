@extends('layouts.college')

@section('college-content')
@include('layouts.partials.page-kit')
@php
    $monthLabel  = $currentMonth->format('F Y');
    $items       = collect($facilityCounts ?? []);
    $monthTotal  = $items->sum('count');
    $booked      = $items->filter(fn ($i) => $i['count'] > 0)->count();
    $collegeKey  = strtolower(trim($collegeName ?? ''));
    $isOurs      = fn ($f) => $collegeKey !== '' && strtolower(trim((string) $f->owner_college)) === $collegeKey;
    $ours        = $items->filter(fn ($i) => $isOurs($i['facility']));
    // Your college's facilities first, then busiest first
    $board       = $items->sortBy([fn ($a, $b) => $isOurs($b['facility']) <=> $isOurs($a['facility']), fn ($a, $b) => $b['count'] <=> $a['count']])->values();
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/tiripunan-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Reservation calendar
            </p>
            <h1>{{ $monthLabel }}</h1>
            <p class="pk-hero-sub">Every reserved and rescheduled booking across campus, so you can plan around what's taken. All times are in Philippine Standard Time.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('college.requests.facilities.create') }}" class="pk-btn">
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
                <p class="pk-stat-label">Facilities booked</p>
                <p class="pk-stat-value">{{ $booked }} <span class="pk-stat-of">/ {{ $items->count() }}</span></p>
                <p class="pk-stat-hint">At least one reservation</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">{{ $collegeName ?: 'Your college' }} facilities</p>
                <p class="pk-stat-value">{{ $ours->sum('count') }}</p>
                <p class="pk-stat-hint">{{ $ours->count() }} {{ \Illuminate\Support\Str::plural('facility', $ours->count()) }} · reservations this month</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/></svg></span>
        </div>
    </section>

    <div class="ccal-block">
        {{-- exposeEventDetails: lets the college New request form show each booking's event and requesting unit --}}
        @include('public.calendar', ['calendarRoute' => 'college.calendar', 'hideCalendarHeading' => true, 'exposeEventDetails' => true])
    </div>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Availability board · {{ $monthLabel }}</h2>
        <span class="pk-section-meta">Your college's facilities first</span>
    </div>
    <div class="ccal-board">
        @foreach ($board as $item)
            @php
                $facility = $item['facility'];
                $count    = $item['count'];
                $status   = $facility->availability_status ?? 'available';
                $tone     = match ($status) { 'available' => 'is-good', 'maintenance' => 'is-warn', 'unavailable' => 'is-bad', default => 'is-neutral' };
                $owner    = match ($facility->owner_type) { 'gsu' => 'GSU', 'college' => 'College (' . ($facility->owner_college ?? 'N/A') . ')', 'org' => 'Organization', default => ucfirst($facility->owner_type ?? 'Unknown') };
                $mine     = $isOurs($facility);
            @endphp
            <article class="ccal-tile {{ $count > 0 ? '' : 'is-zero' }} {{ $mine ? 'is-ours' : '' }}">
                <div class="ccal-tile-top">
                    <div class="u-minw-0">
                        <p class="ccal-name">{{ $facility->name }}</p>
                        <p class="ccal-owner">{{ $owner }}</p>
                    </div>
                    <div class="ccal-count"><strong>{{ $count }}</strong><span>booked</span></div>
                </div>
                <div class="ccal-tile-foot">
                    <span class="pk-pill {{ $tone }}">{{ ucfirst($status) }}</span>
                    @if ($mine)
                        <span class="ccal-ours"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>Your college</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
