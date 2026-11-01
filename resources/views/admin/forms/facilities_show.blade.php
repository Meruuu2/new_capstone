@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
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

    $venueTitle = $facilities->isNotEmpty() ? $facilities->pluck('name')->join(', ') : ($p['facility_name'] ?? 'Facility request');

    $equipLabels = ['monobloc_chair' => 'Monobloc chair', 'table' => 'Table', 'electric_fan' => 'Electric fan', 'rostrum' => 'Rostrum', 'flag' => 'Flag & school color', 'sound' => 'Sound system', 'led' => 'LED wall'];
    $equipment = collect($p['equipment'] ?? [])->filter(fn ($q) => (int) $q > 0)
        ->map(fn ($q, $k) => ['label' => $equipLabels[$k] ?? ucfirst(str_replace('_', ' ', $k)), 'qty' => (int) $q]);

    $isGuest       = !empty($submission->payload['client_name']);
    $requesterName = ($submission->payload['client_name'] ?? null) ?: (optional($submission->requester)->name ?? 'Unknown');

    $canDecide  = $submission->status === 'pending' || ($submission->status === 'pending_payment' && $submission->payment_status === 'payment_verified');
    $canPdf     = in_array($submission->status, ['approved', 'reserved'], true);

    // Back: return to wherever the admin came from (list, bookings…), else the requests list
    $previous = url()->previous();
    $backUrl  = ($previous && $previous !== url()->current() && str_starts_with($previous, url('/')))
        ? $previous
        : route('admin.forms.facilities.index');
@endphp

<div class="pk-page rqd">
    <a href="{{ $backUrl }}" class="rqd-return">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back to requests
    </a>

    {{-- Banner --}}
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div class="u-minw-0">
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/><path d="M13 5v2M13 11v2M13 17v2"/></svg>
                Request #{{ $submission->id }} · <span class="rqd-code">{{ $code }}</span>
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
            @if ($canPdf)
                <a href="{{ route('admin.forms.facilities.pdf', $submission) }}" target="_blank" rel="noopener" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                    View PDF
                </a>
            @endif
            <a href="{{ $backUrl }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="pk-flash u-m-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="pk-flash rqd-flash-bad u-m-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Progress --}}
    <section class="rqd-card rqd-progress" aria-label="Request progress">
        @if ($step > 0)
            <ol class="rqd-steps">
                @foreach ($steps as $i => $label)
                    @php
                        $current = $step === 2 && $i === 1;
                        $done    = !$current && $i + 1 <= $step;
                    @endphp
                    <li class="rqd-step {{ $done ? 'is-done' : '' }} {{ $current ? 'is-current' : '' }}">
                        <span class="rqd-dot" aria-hidden="true">
                            @if ($done)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>@else{{ $i + 1 }}@endif
                        </span>
                        <span class="rqd-step-label">{{ $label }}</span>
                        <span class="rqd-step-sub">
                            @if ($i === 0){{ $submission->created_at?->format('M j') }}
                            @elseif ($current){{ $submission->status === 'pending_payment' ? 'Checking payment' : 'Needs your decision' }}
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
                    <span>{{ $submission->status === 'cancelled' ? 'The requester cancelled this request.' : 'This request was not approved.' }}</span>
                </div>
            </div>
        @endif
    </section>

    <div class="rqd-grid">
        <div class="rqd-side">
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
                        <p class="rqd-muted">{{ $multi ? $days . ' days · same time each day' : ($start ? $start->format('Y') : '') }}</p>
                    </div>
                </div>

                <dl class="rqd-rows">
                    <div class="rqd-row">
                        <dt>Venue</dt>
                        <dd>
                            @if ($facilities->isNotEmpty())
                                {{ $facilities->map(fn ($venue) => $venue->name . ($venue->location ? ' · ' . $venue->location : ''))->join(', ') }}
                            @else
                                Unknown <span class="rqd-sub">ID: {{ $p['facility_id'] ?? 'N/A' }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Purpose</dt>
                        <dd>{{ $p['purpose'] ?? '—' }}</dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Date requested</dt>
                        <dd>{{ $p['date_request'] ?? '—' }}</dd>
                    </div>
                    @if (!empty($p['control_no']))
                        <div class="rqd-row">
                            <dt>Control no.</dt>
                            <dd class="rqd-mono">{{ $p['control_no'] }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Requester & signatories --}}
            <section class="rqd-card">
                <h2 class="rqd-card-title">Requester &amp; signatories</h2>
                <dl class="rqd-rows">
                    <div class="rqd-row">
                        <dt>Requester</dt>
                        <dd>
                            {{ $requesterName }}
                            <span class="rqd-sub">{{ $submission->requester_type }} · {{ $submission->requester_unit ?? 'N/A' }}</span>
                            @if ($isGuest)<span class="rqd-tag">Walk-in guest · no account</span>@endif
                        </dd>
                    </div>
                    @if (!empty($submission->payload['contact_number']) || !empty($submission->payload['email']) || !empty($submission->payload['address']))
                        <div class="rqd-row">
                            <dt>Guest contact</dt>
                            <dd>
                                {{ $submission->payload['contact_number'] ?? '—' }}
                                @if (!empty($submission->payload['email']))<a href="mailto:{{ $submission->payload['email'] }}" class="rqd-link">{{ $submission->payload['email'] }}</a>@endif
                                @if (!empty($submission->payload['address']))<span class="rqd-sub">{{ $submission->payload['address'] }}</span>@endif
                            </dd>
                        </div>
                    @endif
                    <div class="rqd-row">
                        <dt>Noted by</dt>
                        <dd>
                            {{ ($p['noted_signatory_name'] ?? null) ? $p['noted_signatory_name'] . (!empty($p['noted_signatory_position']) ? ' · ' . $p['noted_signatory_position'] : '') : 'Not set' }}
                            @if (!empty($p['noted_datetime']))<span class="rqd-sub">{{ $p['noted_datetime'] }}</span>@endif
                        </dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Head of Office (GSU)</dt>
                        <dd>
                            {{ $p['approved_head_name'] ?? 'Not set' }}
                            @if (!empty($p['approved_datetime']))<span class="rqd-sub">{{ $p['approved_datetime'] }}</span>@endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="rqd-side">
            {{-- Decision / documents --}}
            <section class="rqd-card rqd-actions">
                <h2 class="rqd-card-title">{{ $canDecide ? 'Decision' : 'Documents' }}</h2>

                @if ($submission->status === 'pending_payment' && ! $canDecide)
                    <p class="rqd-note is-warn">Payment has been uploaded. Verify it below before approving.</p>
                @endif

                @if ($canDecide)
                    <p class="rqd-muted">Approve to confirm the venue, or disapprove to decline this request.</p>
                    <div class="rqd-btn-row">
                        <form method="POST" action="{{ route('admin.forms.facilities.approve', $submission) }}" data-confirm="Approve this facility request?" data-confirm-title="Approve request?" data-confirm-action="Approve request">
                            @csrf
                            <button type="submit" class="rqd-btn is-good">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                Approve
                            </button>
                        </form>
                        <form class="rqd-disapprove-form" method="POST" action="{{ route('admin.forms.facilities.disapprove', $submission) }}" data-confirm="Disapprove this facility request? The reason will be sent to the requester." data-confirm-title="Disapprove request?" data-confirm-action="Disapprove request" data-confirm-tone="danger">
                            @csrf
                            <label class="rqd-muted" for="disapproval-reason">Reason for disapproval (required)</label>
                            <textarea class="pk-input" id="disapproval-reason" name="reason" rows="3" maxlength="1000" required>{{ old('reason') }}</textarea>
                            <button type="submit" class="rqd-btn is-bad">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                Disapprove
                            </button>
                        </form>
                    </div>
                @endif

                @if ($submission->status === 'approved')
                    <p class="rqd-note is-good">This request has been approved.</p>
                @elseif ($submission->status === 'reserved')
                    <p class="rqd-note is-info">This request is reserved on the calendar.</p>
                @endif

                @if ($canPdf)
                    <a href="{{ route('admin.forms.facilities.pdf', $submission) }}" target="_blank" rel="noopener" class="rqd-doc">
                        <span class="rqd-doc-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5"/></svg>
                        </span>
                        <span class="u-minw-0">
                            <strong>Facilities &amp; Utilization Form</strong>
                            <span>Opens the filled-in PDF in a new tab</span>
                        </span>
                        <svg class="rqd-doc-go" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
                    </a>
                @elseif (! $canDecide)
                    <p class="rqd-muted">The PDF form is available once the request is approved.</p>
                @endif
            </section>

            @if ($submission->status === 'disapproved' && !empty($p['disapproval_reason']))
                <section class="rqd-card">
                    <h2 class="rqd-card-title">Disapproval reason</h2>
                    <p>{{ $p['disapproval_reason'] }}</p>
                </section>
            @endif

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

            {{-- Payment --}}
            @if ($submission->payment_attachment)
                <section class="rqd-card">
                    <h2 class="rqd-card-title">Payment</h2>
                    <dl class="rqd-rows">
                        <div class="rqd-row">
                            <dt>Status</dt>
                            <dd>
                                @if ($submission->payment_status === 'payment_uploaded')
                                    <span class="pk-pill is-warn">Awaiting verification</span>
                                @elseif ($submission->payment_status === 'payment_verified')
                                    <span class="pk-pill is-good">Verified</span>
                                @else
                                    <span class="pk-pill is-neutral">{{ ucfirst(str_replace('_', ' ', $submission->payment_status)) }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rqd-row">
                            <dt>Receipt</dt>
                            <dd><a href="{{ route('payment-receipts.show', $submission) }}" class="rqd-link">Download receipt</a></dd>
                        </div>
                    </dl>
                    @if ($submission->payment_status === 'payment_uploaded')
                        <form method="POST" action="{{ route('admin.forms.facilities.verify-payment', $submission) }}" class="rqd-btn-row rqd-payment-form">
                            @csrf
                            <button type="submit" class="rqd-btn is-good">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                                Verify payment
                            </button>
                        </form>
                    @endif
                </section>
            @endif

            @if (!empty($payload['attachments']))
                <section class="rqd-card">
                    <h2 class="rqd-card-title">Supporting documents</h2>
                    <ul class="rqd-equip">
                        @foreach ($payload['attachments'] as $index => $attachment)
                            <li>
                                <span>Attachment {{ $index + 1 }} ({{ strtoupper(pathinfo($attachment, PATHINFO_EXTENSION)) }})</span>
                                <a href="{{ route('payment-attachments.show', [$submission, $index]) }}" class="rqd-link">Download</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection
