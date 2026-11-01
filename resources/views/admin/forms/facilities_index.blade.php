@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $statusTabs = [
        ''            => 'All',
        'pending'     => 'Pending',
        'approved'    => 'Approved',
        'reserved'    => 'Reserved',
        'cancelled'   => 'Cancelled',
        'disapproved' => 'Disapproved',
    ];
    $statusTone = fn ($s) => match ($s) {
        'pending'     => 'is-warn',
        'reserved'    => 'is-info',
        'approved'    => 'is-good',
        'disapproved' => 'is-bad',
        default       => 'is-neutral',
    };
    $currentStatus = (string) request('status', '');
    $hasFilters = request()->filled('search') || request()->filled('facility_id') || request()->filled('month') || $currentStatus !== '';
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/grand-stand.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Requests
            </p>
            <h1>All Requests</h1>
            <p class="pk-hero-sub">Review and approve facility requests from colleges and organizations.</p>
        </div>
        <div class="pk-stat is-dark pk-stat--glass u-minw-180">
            <div>
                <p class="pk-stat-label">{{ $hasFilters ? 'Matching requests' : 'Total requests' }}</p>
                <p class="pk-stat-value">{{ $submissions->total() }}</p>
            </div>
        </div>
    </section>

    <!-- Quick status tabs (same ?status= filter as the form below) -->
    <nav class="appr-tabs" aria-label="Filter by status">
        @foreach ($statusTabs as $value => $label)
            @php $params = array_filter(array_merge(request()->except(['status', 'page']), ['status' => $value]), fn ($v) => $v !== '' && $v !== null); @endphp
            <a href="{{ route('admin.forms.facilities.index', $params) }}"
               class="appr-tab {{ $currentStatus === $value ? 'is-active' : '' }}"
               @if ($currentStatus === $value) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.forms.facilities.index') }}" class="appr-filter">
        <!-- Search by Control Number -->
        <div class="pk-field">
            <label for="search" class="pk-label">Control Number</label>
            <div class="appr-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" id="search" name="search" class="pk-input" placeholder="BKG-123..." value="{{ request('search') }}" />
            </div>
        </div>

        <!-- Status Filter -->
        <div class="pk-field">
            <label for="status" class="pk-label">Status</label>
            <select id="status" name="status" class="pk-input">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="disapproved" {{ request('status') === 'disapproved' ? 'selected' : '' }}>Disapproved</option>
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
            <label for="month" class="pk-label">Activity Month</label>
            <input type="month" id="month" name="month" class="pk-input" value="{{ request('month') }}" />
        </div>

        <!-- Action Buttons -->
        <div class="appr-filter-actions">
            <button type="submit" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg>
                Filter
            </button>
            <a href="{{ route('admin.forms.facilities.index') }}" class="pk-btn-outline">Clear</a>
        </div>
    </form>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Requests</h2>
        @if ($submissions->count() > 0)
            <span class="pk-section-meta">Showing {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }} of {{ $submissions->total() }}</span>
        @endif
    </div>

    @if($submissions->count() > 0)
        <div class="pk-table-wrap">
            <table class="pk-table">
                <thead>
                    <tr>
                        <th>Control No.</th>
                        <th>Requester</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th><span class="pk-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $submission)
                        @php
                            // Walk-in guests share one system account; their own name is saved with the request
                            $requesterName = ($submission->payload['client_name'] ?? null) ?: (optional($submission->requester)->name ?? 'Unknown');
                            $initials = collect(preg_split('/\s+/', trim($requesterName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        @endphp
                        <tr>
                            <td><span class="pk-code">{{ !empty($submission->payload['control_no']) ? $submission->payload['control_no'] : 'Not assigned' }}</span></td>
                            <td>
                                <span class="pk-person">
                                    <span class="pk-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
                                    <span class="pk-strong">{{ $requesterName }}</span>
                                </span>
                            </td>
                            <td>{{ $submission->requester_unit ?? '-' }}</td>
                            <td>
                                <span class="pk-pill {{ $statusTone($submission->status) }}">{{ $submission->status === 'reserved' ? 'Reserved' : ucfirst($submission->status) }}</span>
                            </td>
                            <td class="pk-nowrap">{{ $submission->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.forms.facilities.show', $submission) }}" class="pk-row-link">
                                    View
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pk-pagination">
            {{ $submissions->links() }}
        </div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></span>
            <h3>No requests found</h3>
            <p>{{ $hasFilters ? 'Nothing matches these filters. Try clearing them.' : 'New facility requests will appear here.' }}</p>
        </div>
    @endif
</div>
@endsection
