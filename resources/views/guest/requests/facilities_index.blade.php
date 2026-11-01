@extends('layouts.guest')

@section('guest-content')
@include('layouts.partials.page-kit')
@php
    $statusTabs = ['' => 'All', 'pending' => 'Pending', 'pending_payment' => 'Pending payment', 'approved' => 'Approved', 'reserved' => 'Reserved', 'disapproved' => 'Disapproved', 'cancelled' => 'Cancelled'];
    $currentStatus = (string) request('status', '');
    $hasFilters = request()->filled('search') || request()->filled('activity_date') || $currentStatus !== '';
    $steps = ['Submitted', 'In review', 'Approved', 'Reserved'];
    // pending / pending_payment are both "in review" (step 2); disapproved / cancelled stop the track
    $stepOf = fn ($s) => match ($s) { 'pending', 'pending_payment' => 2, 'approved' => 3, 'reserved' => 4, default => 0 };
    $toneOf = fn ($s) => match ($s) {
        'pending', 'pending_payment' => 'is-warn',
        'approved' => 'is-good',
        'reserved' => 'is-info',
        'disapproved' => 'is-bad',
        default => 'is-neutral',
    };
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg>
                Requests
            </p>
            <h1>My Facilities Requests</h1>
            <p class="pk-hero-sub">Follow each utilization request from submission to reservation.</p>
        </div>
        <div class="pk-hero-actions">
            <div class="pk-stat is-dark pk-stat--glass pk-stat--hero u-minw-140">
                <div>
                    <p class="pk-stat-label u-mb-6">{{ $hasFilters ? 'Matching' : 'Requests' }}</p>
                    <p class="pk-stat-value">{{ $submissions->total() }}</p>
                </div>
            </div>
            <a href="{{ route('guest.requests.facilities.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New Request
            </a>
        </div>
    </section>

    <nav class="creq-tabs" aria-label="Filter by status">
        @foreach ($statusTabs as $value => $label)
            @php $params = array_filter(array_merge(request()->except(['status', 'page']), ['status' => $value]), fn ($v) => $v !== '' && $v !== null); @endphp
            <a href="{{ route('guest.requests.facilities.index', $params) }}"
               class="creq-tab {{ $currentStatus === $value ? 'is-active' : '' }}"
               @if ($currentStatus === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('guest.requests.facilities.index') }}" class="creq-filter">
        <div class="pk-field">
            <label for="search" class="pk-label">Control Number</label>
            <div class="creq-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="search" name="search" class="pk-input" placeholder="BKG-..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="pk-field">
            <label for="status" class="pk-label">Status</label>
            <select id="status" name="status" class="pk-input">
                <option value="">All</option>
                @foreach (['pending', 'pending_payment', 'approved', 'reserved', 'disapproved', 'cancelled'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="pk-field">
            <label for="activity_date" class="pk-label">Activity Date</label>
            <input type="date" id="activity_date" name="activity_date" class="pk-input" value="{{ request('activity_date') }}">
        </div>
        <div class="creq-filter-actions">
            <button type="submit" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>
                Apply Filters
            </button>
            <a href="{{ route('guest.requests.facilities.index') }}" class="pk-btn-outline">Clear</a>
        </div>
    </form>

    @if ($submissions->count() > 0)
        <div class="pk-section-head">
            <h2 class="pk-section-title">Requests</h2>
            <span class="pk-section-meta">Showing {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }} of {{ $submissions->total() }}</span>
        </div>
        <div class="creq-list">
            @foreach ($submissions as $submission)
                @php
                    $step = $stepOf($submission->status);
                    $statusLabel = ucwords(str_replace('_', ' ', $submission->status));
                @endphp
                <article class="creq-card">
                    <div class="u-minw-0">
                        <p class="creq-id">
                            <span class="creq-num">{{ 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="creq-filed">Filed {{ $submission->created_at->format('M d, Y') }}</span>
                        </p>
                        <p class="creq-purpose">{{ Str::limit($submission->payload['purpose'] ?? '-', 80) }}</p>
                        <p class="creq-meta">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>{{ $submission->payload['date_activity'] ?? '-' }}@if (!empty($submission->payload['date_end'])) → {{ $submission->payload['date_end'] }}@endif</span>
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>{{ $submission->payload['time_range']['start'] ?? '-' }} - {{ $submission->payload['time_range']['end'] ?? '-' }}</span>
                        </p>
                    </div>

                    <div class="creq-progress">
                        @if ($step > 0)
                            <div class="creq-track" role="img" aria-label="Progress: {{ $statusLabel }}, step {{ $step }} of 4">
                                @foreach ($steps as $i => $label)
                                    @php
                                        // Only "In review" is a waiting state; every reached step up to the status is done
                                        $current = $step === 2 && $i === 1;
                                        $done = !$current && $i + 1 <= $step;
                                    @endphp
                                    <div class="creq-node {{ $done ? 'is-done' : '' }} {{ $current ? 'is-current' : '' }}">
                                        <span class="creq-dot">@if ($done)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>@endif</span>
                                        <span class="creq-node-label">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="creq-stopped">Request {{ strtolower($statusLabel) }} — no further steps</div>
                        @endif
                    </div>

                    <div class="creq-side">
                        <span class="pk-pill {{ $toneOf($submission->status) }}">{{ $statusLabel }}</span>
                        <div class="creq-actions">
                            @if (in_array($submission->status, ['pending', 'pending_payment']))
                                <form class="u-inline u-m-0" method="POST" action="{{ route('guest.requests.facilities.cancel', $submission) }}" data-confirm="Cancel this request?" data-confirm-title="Cancel request?" data-confirm-action="Cancel request" data-confirm-tone="danger">
                                    @csrf
                                    <button type="submit" class="creq-cancel">Cancel</button>
                                </form>
                            @endif
                            <a href="{{ route('guest.requests.facilities.show', $submission) }}" class="pk-row-link">
                                View
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pk-pagination">{{ $submissions->links() }}</div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg></span>
            <h3>{{ $hasFilters ? 'No matching requests' : 'No facility requests yet' }}</h3>
            <p>{{ $hasFilters ? 'Nothing matches these filters. Try clearing them.' : 'Submit an application to rent a University venue.' }}</p>
            <a href="{{ route('guest.requests.facilities.create') }}" class="pk-btn u-mt-6">Submit Your First Request</a>
        </div>
    @endif
</div>
@endsection
