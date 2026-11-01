@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $ticketTone = fn ($s) => match ($s) {
        'pending' => 'is-warn',
        'ongoing' => 'is-info',
        'success' => 'is-good',
        'failed'  => 'is-bad',
        default   => 'is-neutral',
    };
    $initialsOf = fn ($name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $openCount = $activeTickets->total();
    $doneCount = $completedTickets->total();
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/></svg>
                Requests
            </p>
            <h1>Repair &amp; maintenance</h1>
            <p class="pk-hero-sub">Review college requests and update their progress.</p>
        </div>
    </section>

    <section class="pk-stats is-3" aria-label="Ticket summary">
        <div class="pk-stat is-dark">
            <div>
                <p class="pk-stat-label">Open requests</p>
                <p class="pk-stat-value">{{ $openCount }}</p>
                <p class="pk-stat-hint">Notified, pending or ongoing</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">Completed tasks</p>
                <p class="pk-stat-value">{{ $doneCount }}</p>
                <p class="pk-stat-hint">Marked success or failed</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
        </div>
        <div class="pk-stat">
            <div>
                <p class="pk-stat-label">All tickets</p>
                <p class="pk-stat-value">{{ $openCount + $doneCount }}</p>
                <p class="pk-stat-hint">Received from colleges</p>
            </div>
            <span class="pk-stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/></svg></span>
        </div>
    </section>

    <section class="mt-section">
        <div class="pk-section-head">
            <h2 class="pk-section-title">Open requests</h2>
            <span class="pk-section-meta">{{ $openCount }} active</span>
        </div>
        @if ($activeTickets->isNotEmpty())
            <div class="pk-table-wrap">
                <table class="pk-table">
                    <thead>
                        <tr><th>Code</th><th>Subject</th><th>Sender</th><th>Facility</th><th>Status</th><th>Sent</th><th><span class="pk-sr">Actions</span></th></tr>
                    </thead>
                    <tbody>
                        {{-- Pending first; stable sort keeps newest-first order within each group --}}
                        @foreach ($activeTickets->getCollection()->sortBy(fn ($t) => $t->status === 'pending' ? 0 : 1) as $ticket)
                            @php $sender = $ticket->requester?->name ?? 'Unknown'; @endphp
                            <tr>
                                <td><span class="pk-code">{{ $ticket->ticket_code }}</span></td>
                                <td><span class="mt-subject">{{ $ticket->subject }}</span></td>
                                <td><span class="pk-person"><span class="pk-avatar" aria-hidden="true">{{ $initialsOf($sender) }}</span>{{ $sender }}</span></td>
                                <td>
                                    <span class="mt-facility {{ ($ticket->facility || $ticket->facility_other) ? '' : 'is-removed' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                                        {{ $ticket->facility?->name ?? $ticket->facility_other ?? 'Facility removed' }}
                                    </span>
                                </td>
                                <td><span class="pk-pill {{ $ticketTone($ticket->status) }}">{{ ucfirst($ticket->status) }}</span></td>
                                <td class="pk-nowrap">{{ $ticket->requested_at?->format('M d, Y') ?? $ticket->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a class="pk-row-link" href="{{ route('admin.maintenance-tickets.show', $ticket) }}">
                                        Review
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pk-pagination">{{ $activeTickets->links() }}</div>
        @else
            <div class="pk-empty">
                <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/></svg></span>
                <h3>No open requests</h3>
                <p>Repair requests sent by colleges will show up here.</p>
            </div>
        @endif
    </section>

    <section class="mt-section is-done">
        <div class="pk-section-head">
            <h2 class="pk-section-title">Completed tasks</h2>
            <span class="pk-section-meta">{{ $doneCount }} completed</span>
        </div>
        @if ($completedTickets->isNotEmpty())
            <div class="pk-table-wrap">
                <table class="pk-table">
                    <thead>
                        <tr><th>Code</th><th>Subject</th><th>Sender</th><th>Facility</th><th>Outcome</th><th>Completed</th><th><span class="pk-sr">Actions</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($completedTickets as $ticket)
                            @php $sender = $ticket->requester?->name ?? 'Unknown'; @endphp
                            <tr>
                                <td><span class="pk-code">{{ $ticket->ticket_code }}</span></td>
                                <td><span class="mt-subject">{{ $ticket->subject }}</span></td>
                                <td><span class="pk-person"><span class="pk-avatar" aria-hidden="true">{{ $initialsOf($sender) }}</span>{{ $sender }}</span></td>
                                <td>
                                    <span class="mt-facility {{ ($ticket->facility || $ticket->facility_other) ? '' : 'is-removed' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                                        {{ $ticket->facility?->name ?? $ticket->facility_other ?? 'Facility removed' }}
                                    </span>
                                </td>
                                <td><span class="pk-pill {{ $ticketTone($ticket->status) }}">{{ ucfirst($ticket->status) }}</span></td>
                                <td class="pk-nowrap">{{ $ticket->completed_at?->format('M d, Y') ?? '-' }}</td>
                                <td>
                                    <a class="pk-row-link" href="{{ route('admin.maintenance-tickets.show', $ticket) }}">
                                        Review
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pk-pagination">{{ $completedTickets->links() }}</div>
        @else
            <div class="pk-empty">
                <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
                <h3>No completed tasks yet</h3>
                <p>Tickets marked success or failed move here.</p>
            </div>
        @endif
    </section>
</div>
@endsection
