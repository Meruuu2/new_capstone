{{-- Facility request ticket, shown as a pop-up.
     $submission : FormSubmission (facilities_utilization)
     $autoOpen   : true right after submitting (opens on load); otherwise a [data-ticket-open] button opens it
     $showUrl    : optional link to the request's detail page --}}
@php
    $autoOpen = $autoOpen ?? false;
    $showUrl  = $showUrl ?? null;
    extract($submission->ticketData()); // $p, $ticketNo, $statusLabel, $statusTone, $statusNote, $dateLabel, $dateSub, $timeLabel, $venue, $equipment, $requester, $notedBy
    // Signed link: whoever is shown this ticket can download it, in any portal or on the public page
    $downloadUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('request-ticket.download', now()->addHours(12), $submission);
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
            @if (!empty($p['number_of_persons']))<div class="rq-row"><dt>Persons</dt><dd>{{ number_format($p['number_of_persons']) }}</dd></div>@endif
            <div class="rq-row rq-row--wide"><dt>Equipment</dt><dd>{{ $equipment->isNotEmpty() ? $equipment->join(', ') : 'None requested' }}</dd></div>
            <div class="rq-row"><dt>Noted by</dt><dd>{{ $notedBy }}</dd></div>
            <div class="rq-row"><dt>Requested by</dt><dd>{{ $requester }}</dd></div>
        </dl>

        <div class="rq-cut" aria-hidden="true"></div>

        <footer class="rq-stub">
            <p>{{ $statusNote }}</p>
            <span>Submitted {{ $submission->created_at?->format('M j, Y · g:i A') }}</span>
        </footer>
    </article>

    <div class="rq-actions">
        <a href="{{ $downloadUrl }}" class="pk-btn-outline" download>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/></svg>
            Download ticket
        </a>
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
    if (dialog.hasAttribute('data-autoopen')) open();
})();
</script>
