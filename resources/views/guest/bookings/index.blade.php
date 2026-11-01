@extends('layouts.guest')

@section('guest-content')
@include('layouts.partials.page-kit')
@php
    $statusTabs = ['' => 'All', 'reserved' => 'Reserved', 'rescheduled' => 'Rescheduled', 'cancelled' => 'Cancelled'];
    $currentStatus = (string) request('status', '');
    $hasFilters = request()->filled('search') || request()->filled('month') || $currentStatus !== '';
    $byMonth = $bookings->getCollection()->groupBy(fn ($b) => $b->start_time->format('F Y'));
    $now = now();
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                Reservations
            </p>
            <h1>My Reservations</h1>
            <p class="pk-hero-sub">Your bookings and their status, newest first.</p>
        </div>
        <div class="pk-hero-actions">
            <div class="pk-stat is-dark pk-stat--glass pk-stat--hero u-minw-150">
                <div>
                    <p class="pk-stat-label u-mb-6">{{ $hasFilters ? 'Matching' : 'Bookings' }}</p>
                    <p class="pk-stat-value">{{ $bookings->total() }}</p>
                </div>
            </div>
        </div>
    </section>

    <nav class="crsv-tabs" aria-label="Filter by status">
        @foreach ($statusTabs as $value => $label)
            @php $params = array_filter(array_merge(request()->except(['status', 'page']), ['status' => $value]), fn ($v) => $v !== '' && $v !== null); @endphp
            <a href="{{ route('guest.bookings.index', $params) }}"
               class="crsv-tab {{ $currentStatus === $value ? 'is-active' : '' }}"
               @if ($currentStatus === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('guest.bookings.index') }}" class="crsv-filter crsv-filter--guest">
        <div class="pk-field">
            <label for="search" class="pk-label">Search</label>
            <div class="crsv-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="search" name="search" class="pk-input" placeholder="Facility or purpose..." value="{{ request('search') }}" />
            </div>
        </div>
        <div class="pk-field">
            <label for="status" class="pk-label">Status</label>
            <select id="status" name="status" class="pk-input">
                <option value="">All Statuses</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="pk-field">
            <label for="month" class="pk-label">Month</label>
            <input type="month" id="month" name="month" class="pk-input" value="{{ request('month') }}" />
        </div>
        <div class="crsv-filter-actions">
            <button type="submit" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>
                Filter
            </button>
            <a href="{{ route('guest.bookings.index') }}" class="pk-btn-outline">Clear</a>
        </div>
    </form>

    @if ($bookings->count() > 0)
        @foreach ($byMonth as $month => $items)
            <section class="crsv-month" aria-label="{{ $month }}">
                <h2 class="crsv-month-title">{{ $month }}</h2>
                <div class="crsv-list">
                    @foreach ($items as $booking)
                        @php
                            $past = $booking->end_time && $booking->end_time->lt($now);
                            $tone = match ($booking->status) { 'rescheduled' => 'is-warn', 'cancelled' => 'is-bad', 'reserved' => 'is-info', default => 'is-neutral' };
                        @endphp
                        <article class="crsv-item {{ $past ? 'is-past' : '' }}">
                            <span class="crsv-date" aria-hidden="true">
                                <span class="crsv-date-dow">{{ $booking->start_time->format('D') }}</span>
                                <span class="crsv-date-day">{{ $booking->start_time->format('d') }}</span>
                            </span>
                            <div class="u-minw-0">
                                <p class="crsv-code">{{ $booking->booking_code }}</p>
                                <p class="crsv-time">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                    {{ $booking->start_time->format('M d, Y') }} · {{ $booking->start_time->format('H:i') }} – {{ $booking->end_time->format('H:i') }}
                                </p>
                                <p class="crsv-purpose">{{ Str::limit($booking->purpose ?? '-', 80) }}</p>
                                <div class="crsv-venues">
                                    @forelse ($booking->facilities as $venue)
                                        <span class="crsv-venue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>{{ $venue->name }}</span>
                                    @empty
                                        <span class="crsv-venue">Unknown</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="crsv-side">
                                <span class="pk-pill {{ $tone }}">{{ ucfirst($booking->status) }}</span>
                                <span class="crsv-when">{{ $past ? 'Done' : $booking->start_time->diffForHumans() }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="pk-pagination">{{ $bookings->links() }}</div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg></span>
            <h3>{{ $hasFilters ? 'No matching reservations' : 'No reservations found.' }}</h3>
            <p>{{ $hasFilters ? 'Nothing matches these filters. Try clearing them.' : 'Once your facility requests are approved, they will appear here.' }}</p>
            <a href="{{ route('guest.requests.facilities.create') }}" class="pk-btn u-mt-6">Request a facility</a>
        </div>
    @endif
</div>
@endsection
