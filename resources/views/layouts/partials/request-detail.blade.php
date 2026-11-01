{{-- Facility request detail page body, shared by College and Org "My requests".
     $submission, $payload, $facilities : from the controller
     $backUrl, $cancelUrl               : portal routes --}}
@php
    $p    = $payload ?? [];
    $code = 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT);

    $statusMeta = [
        'pending'         => ['Pending review',         'is-warn'],
        'pending_payment' => ['Payment being verified', 'is-warn'],
        'approved'        => ['Approved',               'is-good'],
        'reserved'        => ['Reserved',               'is-info'],
        'disapproved'     => ['Disapproved',            'is-bad'],
        'cancelled'       => ['Cancelled',              'is-neutral'],
    ];
    [$statusLabel, $statusTone] = $statusMeta[$submission->status] ?? [ucfirst(str_replace('_', ' ', (string) $submission->status)), 'is-neutral'];

    // Progress: same steps as the "My requests" list
    $steps = ['Submitted', 'In review', 'Approved', 'Reserved'];
    $step  = match ($submission->status) { 'pending', 'pending_payment' => 2, 'approved' => 3, 'reserved' => 4, default => 0 };

    $parse = function ($d) { try { return $d ? \Carbon\Carbon::parse($d) : null; } catch (\Throwable $e) { return null; } };
    $start = $parse($p['date_activity'] ?? null);
    $end   = $parse($p['date_end'] ?? null);
    $multi = $start && $end && $end->gt($start);
    $days  = $multi ? $start->diffInDays($end) + 1 : 1;

    $tr    = $p['time_range'] ?? [];
    $clock = function ($t) { try { return $t ? \Carbon\Carbon::createFromFormat('H:i', substr($t, 0, 5)) : null; } catch (\Throwable $e) { return null; } };
    $tFrom = $clock($tr['start'] ?? null);
    $tTo   = $clock($tr['end'] ?? null);
    $timeLabel = $tFrom ? $tFrom->format('g:i A') . ($tTo ? ' – ' . $tTo->format('g:i A') : '') : '—';
    $duration  = null;
    if ($tFrom && $tTo && $tTo->gt($tFrom)) {
        $mins     = $tFrom->diffInMinutes($tTo);
        $hrs      = round($mins / 30) / 2;
        $duration = ($mins >= 360 ? 'Whole day' : 'Half day') . ' · ' . rtrim(rtrim(number_format($hrs, 1), '0'), '.') . ' ' . ($hrs == 1 ? 'hr' : 'hrs');
    }

    // Venue: linked facilities if the request has them, otherwise the venue name saved with the request
    $venues = $facilities->isNotEmpty()
        ? $facilities->pluck('name')
        : collect([$p['facility_name'] ?? null, $p['facility_name_custom'] ?? null])->filter()->values();
    $venueTitle = $venues->isNotEmpty() ? $venues->join(', ') : 'Facility request';

    $equipLabels = ['monobloc_chair' => 'Monobloc chair', 'table' => 'Table', 'electric_fan' => 'Electric fan', 'rostrum' => 'Rostrum', 'flag' => 'Flag & school color', 'sound' => 'Sound system', 'led' => 'LED wall'];
    $equipment = collect($p['equipment'] ?? [])->filter(fn ($q) => (int) $q > 0)
        ->map(fn ($q, $k) => ['label' => $equipLabels[$k] ?? ucfirst(str_replace('_', ' ', $k)), 'qty' => (int) $q]);

    $requester = trim(($p['requester_name'] ?? '') . (!empty($p['requester_unit']) ? ' · ' . $p['requester_unit'] : ''));
@endphp

<div class="pk-page rqd">
    {{-- Banner: what, when, where it stands --}}
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div class="u-minw-0">
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/><path d="M13 5v2M13 11v2M13 17v2"/></svg>
                Facility request · <span class="rqd-code">{{ $code }}</span>
            </p>
            <h1>{{ $venueTitle }}</h1>
            <p class="pk-hero-sub">
                {{ $start ? ($multi ? $start->format('D, M j') . ' → ' . $end->format('D, M j, Y') : $start->format('l, F j, Y')) : 'Date not set' }}
                · {{ $timeLabel }}
            </p>
            <div class="rqd-meta">
                <span class="rqd-status {{ $statusTone }}">{{ $statusLabel }}</span>
                <span class="rqd-meta-item">Submitted {{ $submission->created_at?->format('M j, Y · g:i A') ?? '—' }}</span>
            </div>
        </div>
        <div class="pk-hero-actions">
            <button type="button" class="pk-btn" data-ticket-open>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/><path d="M13 5v2M13 11v2M13 17v2"/></svg>
                View ticket
            </button>
            <a href="{{ $backUrl }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                My requests
            </a>
        </div>
    </section>

    {{-- Progress --}}
    <section class="rqd-card rqd-progress" aria-label="Request progress">
        @if ($step > 0)
            <ol class="rqd-steps">
                @foreach ($steps as $i => $label)
                    @php
                        $current = $step === 2 && $i === 1;          // "In review" is the only waiting state
                        $done    = !$current && $i + 1 <= $step;
                    @endphp
                    <li class="rqd-step {{ $done ? 'is-done' : '' }} {{ $current ? 'is-current' : '' }}">
                        <span class="rqd-dot" aria-hidden="true">
                            @if ($done)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>@else{{ $i + 1 }}@endif
                        </span>
                        <span class="rqd-step-label">{{ $label }}</span>
                        <span class="rqd-step-sub">
                            @if ($i === 0){{ $submission->created_at?->format('M j') }}
                            @elseif ($current){{ $submission->status === 'pending_payment' ? 'Checking payment' : 'Waiting for GSU' }}
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
                    <strong>Request {{ strtolower($statusLabel) }}</strong>
                    <span>{{ $submission->status === 'cancelled' ? 'This request was cancelled. Submit a new request if you still need the venue.' : 'GSU did not approve this request. You can submit a new one with a different date or venue.' }}</span>
                </div>
            </div>
        @endif
    </section>

    @if ($submission->status === 'disapproved' && !empty($p['disapproval_reason']))
        <section class="rqd-card rqd-flash-bad">
            <h2 class="rqd-card-title">Reason for disapproval</h2>
            <p>{{ $p['disapproval_reason'] }}</p>
        </section>
    @endif

    <div class="rqd-grid">
        {{-- Event details --}}
        <section class="rqd-card">
            <h2 class="rqd-card-title">Event details</h2>

            <div class="rqd-when">
                <div class="rqd-dates">
                    @if ($start)
                        <div class="rqd-date">
                            <span class="rqd-date-m">{{ $start->format('M') }}</span>
                            <span class="rqd-date-d">{{ $start->format('j') }}</span>
                            <span class="rqd-date-w">{{ $start->format('D') }}</span>
                        </div>
                        @if ($multi)
                            <span class="rqd-date-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                            <div class="rqd-date">
                                <span class="rqd-date-m">{{ $end->format('M') }}</span>
                                <span class="rqd-date-d">{{ $end->format('j') }}</span>
                                <span class="rqd-date-w">{{ $end->format('D') }}</span>
                            </div>
                        @endif
                    @else
                        <p class="rqd-muted">Date not set</p>
                    @endif
                </div>
                <div class="rqd-when-text">
                    <p class="rqd-time">{{ $timeLabel }}</p>
                    <p class="rqd-muted">
                        {{ $multi ? $days . ' days · same time each day' : ($start ? $start->format('Y') : '') }}
                    </p>
                    @if ($duration)<span class="rqd-chip">{{ $duration }}</span>@endif
                </div>
            </div>

            <dl class="rqd-rows">
                <div class="rqd-row">
                    <dt>Venue</dt>
                    <dd>{{ $venues->isNotEmpty() ? $venues->join(', ') : 'Not specified' }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Purpose</dt>
                    <dd>{{ $p['purpose'] ?? '—' }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Noted by</dt>
                    <dd>{{ ($p['noted_signatory_name'] ?? '') ? $p['noted_signatory_name'] . (!empty($p['noted_signatory_position']) ? ' · ' . $p['noted_signatory_position'] : '') : 'No signatory' }}</dd>
                </div>
                <div class="rqd-row">
                    <dt>Requested by</dt>
                    <dd>{{ $requester ?: '—' }}</dd>
                </div>
                @if (!empty($p['control_no']))
                    <div class="rqd-row">
                        <dt>Control no.</dt>
                        <dd class="rqd-mono">{{ $p['control_no'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <div class="rqd-side">
            {{-- Equipment --}}
            <section class="rqd-card">
                <div class="rqd-card-head">
                    <h2 class="rqd-card-title">Equipment</h2>
                    <span class="rqd-count">{{ $equipment->sum('qty') }} {{ \Illuminate\Support\Str::plural('item', $equipment->sum('qty')) }}</span>
                </div>
                @if ($equipment->isNotEmpty())
                    <ul class="rqd-equip">
                        @foreach ($equipment as $item)
                            <li><span>{{ $item['label'] }}</span><strong>× {{ $item['qty'] }}</strong></li>
                        @endforeach
                    </ul>
                @else
                    <p class="rqd-muted">No equipment requested.</p>
                @endif
            </section>

            @if (!empty($p['attachments']))
                <section class="rqd-card">
                    <h2 class="rqd-card-title">Attachments</h2>
                    <ul class="rqd-equip">
                        @foreach ($p['attachments'] as $index => $attachment)
                            <li>
                                <span>Attachment {{ $index + 1 }} ({{ strtoupper(pathinfo($attachment, PATHINFO_EXTENSION)) }})</span>
                                <a href="{{ route('payment-attachments.show', [$submission, $index]) }}" class="rqd-link">Download</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Cancel (pending only) --}}
            @if ($submission->status === 'pending' && $cancelUrl)
                <section class="rqd-card rqd-cancel">
                    <h2 class="rqd-card-title">Need to change something?</h2>
                    <p class="rqd-muted">You can cancel while GSU hasn't reviewed it yet, then submit a new request.</p>
                    <form method="POST" action="{{ $cancelUrl }}" data-confirm="Cancel this request? This cannot be undone." data-confirm-title="Cancel request?" data-confirm-action="Cancel request" data-confirm-tone="danger">
                        @csrf
                        <button type="submit" class="rqd-cancel-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                            Cancel request
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>
</div>

@include('layouts.partials.request-ticket', ['submission' => $submission])
