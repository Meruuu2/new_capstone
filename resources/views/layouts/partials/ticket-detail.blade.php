{{-- Repair & maintenance ticket detail, shared by College and Org. Same layout as the facility
     request detail (layouts/partials/request-detail.blade.php).
     $ticket  : from the controller
     $backUrl : portal "Repair & maintenance" list --}}
@php
    $statusMeta = [
        'notified' => ['Admins notified',   'is-neutral'],
        'pending'  => ['Waiting to start',  'is-warn'],
        'ongoing'  => ['In progress',       'is-info'],
        'success'  => ['Completed',         'is-good'],
        'failed'   => ['Not completed',     'is-bad'],
    ];
    [$statusLabel, $statusTone] = $statusMeta[$ticket->status] ?? [ucfirst(str_replace('_', ' ', (string) $ticket->status)), 'is-neutral'];

    // Progress: Sent → Queued → In progress → Completed
    $steps   = ['Sent', 'Queued', 'In progress', 'Completed'];
    $flow    = [
        'notified' => ['done' => 1, 'current' => 1, 'sub' => 'Admins notified'],
        'pending'  => ['done' => 2, 'current' => 2, 'sub' => 'Waiting for a team'],
        'ongoing'  => ['done' => 2, 'current' => 2, 'sub' => 'Team on it'],
        'success'  => ['done' => 4, 'current' => null, 'sub' => null],
    ];
    $state   = $flow[$ticket->status] ?? null;
    $sentAt  = $ticket->requested_at ?? $ticket->created_at;
    $facilityName = $ticket->facility?->name ?? $ticket->facility_other ?? 'Facility removed';
@endphp

<div class="pk-page rqd">
    {{-- Banner: what, where, where it stands --}}
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/tiripunan-hall.jpg') }}')">
        <div class="u-minw-0">
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/></svg>
                Repair &amp; maintenance · <span class="rqd-code">{{ $ticket->ticket_code }}</span>
            </p>
            <h1>{{ $ticket->subject }}</h1>
            <p class="pk-hero-sub">{{ $facilityName }}</p>
            <div class="rqd-meta">
                <span class="rqd-status {{ $statusTone }}">{{ $statusLabel }}</span>
                <span class="rqd-meta-item">Sent {{ $sentAt?->format('M j, Y · g:i A') ?? '—' }}</span>
            </div>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ $backUrl }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                All requests
            </a>
        </div>
    </section>

    {{-- Progress --}}
    <section class="rqd-card rqd-progress" aria-label="Repair progress">
        @if ($state)
            <ol class="rqd-steps">
                @foreach ($steps as $i => $label)
                    @php
                        $current = $state['current'] === $i;
                        $done    = !$current && $i < $state['done'];
                    @endphp
                    <li class="rqd-step {{ $done ? 'is-done' : '' }} {{ $current ? 'is-current' : '' }}">
                        <span class="rqd-dot" aria-hidden="true">
                            @if ($done)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>@else{{ $i + 1 }}@endif
                        </span>
                        <span class="rqd-step-label">{{ $label }}</span>
                        <span class="rqd-step-sub">
                            @if ($i === 0){{ $sentAt?->format('M j') }}
                            @elseif ($current){{ $state['sub'] }}
                            @elseif ($i === 3 && $ticket->completed_at){{ $ticket->completed_at->format('M j') }}
                            @elseif ($done)Done
                            @else Next @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        @else
            <div class="rqd-stopped {{ $statusTone }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                <div>
                    <strong>Repair {{ strtolower($statusLabel) }}</strong>
                    <span>{{ $ticket->status === 'failed' ? 'The repair couldn\'t be completed. GSU will contact you about next steps, or you can send a new request.' : 'This request is no longer active.' }}</span>
                </div>
            </div>
        @endif
    </section>

    <div class="rqd-grid">
        <div class="rqd-side">
        {{-- Issue details --}}
        <section class="rqd-card">
            <h2 class="rqd-card-title">Issue details</h2>
            <dl class="rqd-rows">
                <div class="rqd-row">
                    <dt>Facility</dt>
                    <dd>{{ $facilityName }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Subject</dt>
                    <dd>{{ $ticket->subject }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Message</dt>
                    <dd class="rqd-pre">{{ $ticket->issue_description }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Sent by</dt>
                    <dd>{{ $ticket->requester_id === auth()->id() ? 'You' : ($ticket->requester?->name ?? 'Deleted account') }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Sent</dt>
                    <dd>{{ $sentAt?->format('M j, Y · g:i A') ?? '—' }}</dd>
                </div>
                @if ($ticket->completed_at)
                    <div class="rqd-row">
                        <dt>Completed</dt>
                        <dd>{{ $ticket->completed_at->format('M j, Y · g:i A') }}</dd>
                    </div>
                @endif
                <div class="rqd-row">
                    <dt>Tracking code</dt>
                    <dd class="rqd-mono">{{ $ticket->ticket_code }}</dd>
                </div>
            </dl>
        </section>

        {{-- Conversation with the admins (College only; Org pages don't pass $conversation) --}}
        @isset($conversation)
            @include('layouts.partials.ticket-conversation', $conversation + ['ticket' => $ticket])
        @endisset
        </div>

        <div class="rqd-side">
            {{-- Administrator update --}}
            <section class="rqd-card">
                <h2 class="rqd-card-title">Administrator update</h2>
                @if ($ticket->admin_remarks)
                    <p class="rqd-pre rqd-remark">{{ $ticket->admin_remarks }}</p>
                @else
                    <p class="rqd-muted">No update yet. The facility administrators will post progress here.</p>
                @endif
            </section>

            {{-- Tracking --}}
            <section class="rqd-card">
                <h2 class="rqd-card-title">Track it anytime</h2>
                <p class="rqd-muted">Use this code on the home page's ticket tracker to check the status without signing in.</p>
                <p class="rqd-track-code">{{ $ticket->ticket_code }}</p>
            </section>
        </div>
    </div>
</div>
