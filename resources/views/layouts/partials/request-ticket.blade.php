{{-- Facility request ticket, shown as a pop-up.
     $submission : FormSubmission (facilities_utilization)
     $autoOpen   : true right after submitting (opens on load); otherwise a [data-ticket-open] button opens it
     $showUrl    : optional link to the request's detail page --}}
@php
    $p        = $submission->payload ?? [];
    $autoOpen = $autoOpen ?? false;
    $showUrl  = $showUrl ?? null;
    // Ticket code: FM-<year submitted>-<request no.>, the format the landing page tracker takes
    $ticketNo = 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT);

    $statusMeta = [
        'pending'         => ['Pending review',         'is-warn', 'GSU will review your request. You\'ll get a notification when it\'s approved.'],
        'pending_payment' => ['Payment being verified', 'is-warn', 'GSU is checking your payment receipt before reviewing the request.'],
        'approved'        => ['Approved',               'is-good', 'GSU approved your request. It will appear on the calendar once reserved.'],
        'reserved'        => ['Reserved',               'is-good', 'Your venue is reserved and on the calendar.'],
        'disapproved'     => ['Disapproved',            'is-bad',  'GSU did not approve this request.'],
        'cancelled'       => ['Cancelled',              'is-muted', 'This request was cancelled.'],
    ];
    [$statusLabel, $statusTone, $statusNote] = $statusMeta[$submission->status] ?? [ucfirst(str_replace('_', ' ', (string) $submission->status)), 'is-muted', ''];

    $parse = function ($d) { try { return $d ? \Carbon\Carbon::parse($d) : null; } catch (\Throwable $e) { return null; } };
    $start = $parse($p['date_activity'] ?? null);
    $end   = $parse($p['date_end'] ?? null);
    if ($start && $end && $end->gt($start)) {
        $days      = $start->diffInDays($end) + 1;
        $dateLabel = $start->format('D, M j') . ' → ' . $end->format('D, M j, Y');
        $dateSub   = $days . ' days · same time each day';
    } else {
        $dateLabel = $start ? $start->format('l, F j, Y') : '—';
        $dateSub   = null;
    }
    $clock = function ($t) { try { return $t ? \Carbon\Carbon::createFromFormat('H:i', substr($t, 0, 5))->format('g:i A') : ''; } catch (\Throwable $e) { return $t; } };
    $tr        = $p['time_range'] ?? [];
    $timeLabel = trim($clock($tr['start'] ?? '') . ' – ' . $clock($tr['end'] ?? ''), ' –') ?: '—';

    $venue = collect($p['facility_names'] ?? [$p['facility_name'] ?? null, $p['facility_name_custom'] ?? null])
        ->filter()
        ->unique()
        ->values()
        ->join(', ') ?: '—';

    $equipLabels = ['monobloc_chair' => 'Monobloc chair', 'table' => 'Table', 'electric_fan' => 'Electric fan', 'rostrum' => 'Rostrum', 'flag' => 'Flag & school color', 'sound' => 'Sound', 'led' => 'LED wall'];
    $equipment = collect($p['equipment'] ?? [])->filter(fn ($q) => (int) $q > 0)
        ->map(fn ($q, $k) => (int) $q . ' × ' . ($equipLabels[$k] ?? ucfirst(str_replace('_', ' ', $k))))->values();

    $requester = trim(($p['requester_name'] ?? '') . (!empty($p['requester_unit']) ? ' · ' . $p['requester_unit'] : '')) ?: '—';
@endphp

<dialog class="rq-dialog" id="rq-ticket" aria-labelledby="rq-ticket-title" @if ($autoOpen) data-autoopen @endif>
    <div class="rq-head">
        @if ($autoOpen)
            <span class="rq-head-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
            <div>
                <h2 id="rq-ticket-title">Request submitted</h2>
                <p>Here's your ticket. Use the ticket code to track it from the home page.</p>
            </div>
        @else
            <div>
                <h2 id="rq-ticket-title">Your request ticket</h2>
                <p>Use the ticket code to track it from the home page.</p>
            </div>
        @endif
        <button type="button" class="rq-close" data-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <article class="rq-ticket">
        <header class="rq-ticket-top">
            <div class="rq-brand">
                <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="">
                <span>University of Antique<br><small>Facility request ticket</small></span>
            </div>
            <div class="rq-no">
                <span>Ticket code</span>
                <strong>{{ $ticketNo }}</strong>
            </div>
        </header>

        <div class="rq-status">
            <span class="pk-pill {{ $statusTone }}">{{ $statusLabel }}</span>
            @if (!empty($p['control_no']))<span class="rq-control">Control no. <b>{{ $p['control_no'] }}</b></span>@endif
        </div>

        <div class="rq-cut" aria-hidden="true"></div>

        <dl class="rq-rows">
            <div class="rq-row rq-row--wide"><dt>Venue</dt><dd class="rq-venue">{{ $venue }}</dd></div>
            <div class="rq-row"><dt>Date</dt><dd>{{ $dateLabel }}@if ($dateSub)<small>{{ $dateSub }}</small>@endif</dd></div>
            <div class="rq-row"><dt>Time</dt><dd>{{ $timeLabel }}</dd></div>
            <div class="rq-row rq-row--wide"><dt>Purpose</dt><dd>{{ $p['purpose'] ?? '—' }}</dd></div>
            <div class="rq-row rq-row--wide"><dt>Equipment</dt><dd>{{ $equipment->isNotEmpty() ? $equipment->join(', ') : 'None requested' }}</dd></div>
            <div class="rq-row"><dt>Noted by</dt><dd>{{ ($p['noted_signatory_name'] ?? '') ? $p['noted_signatory_name'] . (!empty($p['noted_signatory_position']) ? ' · ' . $p['noted_signatory_position'] : '') : '—' }}</dd></div>
            <div class="rq-row"><dt>Requested by</dt><dd>{{ $requester }}</dd></div>
        </dl>

        <div class="rq-cut" aria-hidden="true"></div>

        <footer class="rq-stub">
            <p>{{ $statusNote }}</p>
            <span>Submitted {{ $submission->created_at?->format('M j, Y · g:i A') }}</span>
        </footer>
    </article>

    <div class="rq-actions">
        <button type="button" class="pk-btn-outline" data-print>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
            Print ticket
        </button>
        @if ($showUrl)
            <a href="{{ $showUrl }}" class="pk-btn-outline">View request</a>
        @endif
        <button type="button" class="pk-btn" data-close autofocus>Done</button>
    </div>
</dialog>

<script>
(function () {
    const dialog = document.getElementById('rq-ticket');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    const open = () => { if (!dialog.open) dialog.showModal(); };
    document.querySelectorAll('[data-ticket-open]').forEach(btn => btn.addEventListener('click', open));
    dialog.addEventListener('click', e => { if (e.target === dialog || e.target.closest('[data-close]')) dialog.close(); });
    dialog.querySelector('[data-print]').addEventListener('click', () => window.print());
    if (dialog.hasAttribute('data-autoopen')) open();
})();
</script>
