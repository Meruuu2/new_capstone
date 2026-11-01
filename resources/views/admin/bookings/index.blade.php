@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $statusTabs = [
        ''            => 'All',
        'reserved'    => 'Reserved',
        'rescheduled' => 'Rescheduled',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ];
    $bookingTone = fn ($s) => match ($s) {
        'reserved'    => 'is-info',
        'rescheduled' => 'is-warn',
        'pending'     => 'is-warn',
        'completed'   => 'is-good',
        'cancelled'   => 'is-bad',
        default       => 'is-neutral',
    };
    $currentStatus = (string) request('status', '');
    $hasFilters = request()->filled('search') || request()->filled('facility_id') || request()->filled('month') || $currentStatus !== '';
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                Reservations
            </p>
            <h1>Reservations</h1>
            <p class="pk-hero-sub">GSU overview of every booking: who reserved what, when, and where it stands.</p>
        </div>
        <div class="pk-hero-actions">
            <div class="pk-stat is-dark pk-stat--glass pk-stat--hero u-minw-150">
                <div>
                    <p class="pk-stat-label u-mb-6">{{ $hasFilters ? 'Matching' : 'Total' }}</p>
                    <p class="pk-stat-value">{{ $bookings->total() }}</p>
                </div>
            </div>
            <a href="{{ route('admin.bookings.create-direct') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Create Direct Booking
            </a>
        </div>
    </section>

    <!-- Quick status tabs (same ?status= filter as the form below) -->
    <nav class="rsv-tabs" aria-label="Filter by status">
        @foreach ($statusTabs as $value => $label)
            @php $params = array_filter(array_merge(request()->except(['status', 'page']), ['status' => $value]), fn ($v) => $v !== '' && $v !== null); @endphp
            <a href="{{ route('admin.bookings.index', $params) }}"
               class="rsv-tab {{ $currentStatus === $value ? 'is-active' : '' }}"
               @if ($currentStatus === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="rsv-filter">
        <!-- Search by Requester or Purpose -->
        <div class="pk-field">
            <label for="search" class="pk-label">Search</label>
            <div class="rsv-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="search" name="search" class="pk-input" placeholder="Requester, facility, or purpose..." value="{{ request('search') }}" />
            </div>
        </div>

        <!-- Status Filter -->
        <div class="pk-field">
            <label for="status" class="pk-label">Status</label>
            <select id="status" name="status" class="pk-input">
                <option value="">All Statuses</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- Facility Filter -->
        <div class="pk-field">
            <label for="facility_id" class="pk-label">Facility</label>
            <select id="facility_id" name="facility_id" class="pk-input">
                <option value="">All Facilities</option>
                @foreach ($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                        {{ $facility->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Month Filter -->
        <div class="pk-field">
            <label for="month" class="pk-label">Month</label>
            <input type="month" id="month" name="month" class="pk-input" value="{{ request('month') }}" />
        </div>

        <!-- Action Buttons -->
        <div class="rsv-filter-actions">
            <button type="submit" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>
                Filter
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="pk-btn-outline">Clear</a>
        </div>
    </form>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Bookings</h2>
        @if ($bookings->count())
            <span class="pk-section-meta">Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} of {{ $bookings->total() }}</span>
        @endif
    </div>

    @if($bookings->count())
        <div class="pk-table-wrap">
            <table class="pk-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Code</th>
                        <th>Requester</th>
                        <th>Facility</th>
                        <th>Status</th>
                        <th><span class="pk-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        @php
                            $requesterName = optional($booking->requester)->name ?? 'Unknown';
                            $initials = collect(preg_split('/\s+/', trim($requesterName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                            $submissionId = $booking->additional_details['form_submission_id'] ?? null;
                        @endphp
                        <tr>
                            <td>
                                @if ($booking->start_time)
                                    <div class="rsv-when">
                                        <span class="rsv-date" aria-hidden="true">
                                            <span class="rsv-date-mon">{{ $booking->start_time->format('M') }}</span>
                                            <span class="rsv-date-day">{{ $booking->start_time->format('d') }}</span>
                                        </span>
                                        <span>
                                            <span class="rsv-time">{{ $booking->start_time->format('H:i') }} – {{ $booking->end_time?->format('H:i') }}</span>
                                            <span class="rsv-year">{{ $booking->start_time->format('M d, Y') }}</span>
                                        </span>
                                    </div>
                                @else
                                    –
                                @endif
                            </td>
                            <td><span class="pk-code">{{ $booking->booking_code }}</span></td>
                            <td>
                                <span class="pk-person">
                                    <span class="pk-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
                                    <span>
                                        <span class="pk-strong">{{ $requesterName }}</span>
                                        <span class="rsv-sub">{{ $booking->requester_type }} – {{ $booking->requester_unit ?? 'N/A' }}</span>
                                    </span>
                                </span>
                            </td>
                            <td>
                                @if($booking->facilities->isNotEmpty())
                                    <span class="rsv-venues">
                                        @foreach ($booking->facilities as $venue)
                                            <span class="rsv-venue">{{ $venue->name }}</span>
                                        @endforeach
                                    </span>
                                @else
                                    Unknown
                                @endif
                            </td>
                            <td><span class="pk-pill {{ $bookingTone($booking->status) }}">{{ ucfirst($booking->status) }}</span></td>
                            <td>
                                <span class="rsv-actions">
                                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="pk-row-link">
                                        View / Modify
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                    @if($submissionId)
                                        <a href="{{ route('admin.forms.facilities.pdf', $submissionId) }}" target="_blank" rel="noopener" class="rsv-pdf">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M12 12v6M9 15l3 3 3-3"/></svg>
                                            Generate PDF
                                        </a>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pk-pagination">
            {{ $bookings->links() }}
        </div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg></span>
            <h3>{{ $hasFilters ? 'No matching bookings' : 'No bookings yet' }}</h3>
            <p>{{ $hasFilters ? 'Nothing matches these filters. Try clearing them.' : 'Approved requests and direct reservations will appear here.' }}</p>
        </div>
    @endif
</div>
@endsection
