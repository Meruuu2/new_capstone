@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
{{-- Same building blocks as the College/Org Facilities Utilization Request (numbered steps, date picker,
     venue board, equipment tiles, summary ticket). Field names and both forms are unchanged. --}}
@php
    $statusTone = match ($booking->status) {
        'reserved'    => 'is-info',
        'rescheduled', 'pending' => 'is-warn',
        'completed'   => 'is-good',
        'cancelled'   => 'is-bad',
        default       => 'is-neutral',
    };
    $requesterName = optional($booking->requester)->name ?? 'Unknown';
    $initials = collect(preg_split('/\s+/', trim($requesterName)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $venueNames = $booking->facilities->pluck('name');

    $currentDate  = $booking->start_time ? $booking->start_time->toDateString() : now()->toDateString();
    $currentStart = $booking->start_time ? $booking->start_time->format('H:i') : '08:00';
    $currentEnd   = $booking->end_time ? $booking->end_time->format('H:i') : '17:00';

    $details = is_array($booking->additional_details)
        ? $booking->additional_details
        : ($booking->additional_details ? json_decode($booking->additional_details, true) : []);
    $equip = $details['equipment'] ?? [];

    $equipmentFields = [
        'qty_monobloc' => 'Monobloc Chair',
        'qty_table'    => 'Table',
        'qty_fan'      => 'Electric Fan',
        'qty_rostrum'  => 'Rostrum',
        'qty_flag'     => 'Flag & School Color',
        'qty_sound'    => 'Sound',
        'qty_led'      => 'LED Wall',
    ];
    $map = [
        'qty_monobloc' => 'monobloc_chair',
        'qty_table'    => 'table',
        'qty_fan'      => 'electric_fan',
        'qty_rostrum'  => 'rostrum',
        'qty_flag'     => 'flag',
        'qty_sound'    => 'sound',
        'qty_led'      => 'led',
    ];
    $equipmentIcons = [
        'qty_monobloc' => '<path d="M7 3h10v8H7z"/><path d="M5 11h14M7 11v10M17 11v10M7 16h10"/>',
        'qty_table'    => '<path d="M3 8h18M5 8v12M19 8v12M3 8l2-3h14l2 3"/>',
        'qty_fan'      => '<circle cx="12" cy="10" r="1.6"/><path d="M12 8.4c0-3 1-5 3-5s2 3-1.4 5.2M13.4 11c2.6 1.5 3.9 3.3 2.9 5s-3.4-.3-4-3.7M10.6 10.9c-2.6 1.5-4.8 1.6-5.8-.1s2-2.8 5.2-1.6"/><path d="M12 12v8M8 21h8"/>',
        'qty_rostrum'  => '<path d="M6 8h12l-1.5 13h-9z"/><path d="M4 5h16v3H4zM12 5V3"/>',
        'qty_flag'     => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'qty_sound'    => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="14" r="3"/><circle cx="12" cy="7.5" r="1"/>',
        'qty_led'      => '<rect x="2" y="4" width="20" height="13" rx="1.5"/><path d="M8 21h8M12 17v4M6 8h4M6 11h7"/>',
    ];
    $equipInUse = collect($equipmentFields)
        ->map(fn ($label, $field) => ['label' => $label, 'qty' => (int) ($equip[$map[$field]] ?? 0)])
        ->filter(fn ($e) => $e['qty'] > 0);
    $selectedVenues = array_map('intval', (array) old('facility_ids', $booking->facilities->pluck('id')->all()));
    $reasonTypes = ['Schedule conflict', 'Venue under repair', 'Requester asked', 'University event'];
    // For the live "Changed:" note and so this booking isn't shown as a clash with itself
    $original = [
        'date'    => $currentDate,
        'start'   => $currentStart,
        'end'     => $currentEnd,
        'venues'  => $venueNames->values()->all(),
        'purpose' => (string) $booking->purpose,
    ];
    $ignoreSelf = $booking->start_time ? [
        'date'   => $booking->start_time->toDateString(),
        'start'  => $booking->start_time->format('H:i'),
        'end'    => $booking->end_time?->format('H:i'),
        'venues' => $venueNames->values()->all(),
    ] : null;
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div class="u-minw-0">
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                Reservation · <span class="rqd-code">{{ $booking->booking_code }}</span>
            </p>
            <h1>{{ $venueNames->isNotEmpty() ? $venueNames->implode(', ') : 'Reservation' }}</h1>
            <p class="pk-hero-sub">{{ $requesterName }}{{ $booking->requester_unit ? ' · ' . $booking->requester_unit : '' }}</p>
            <div class="rqd-meta">
                <span class="rqd-status {{ $statusTone }}">{{ ucfirst($booking->status) }}</span>
                @if ($booking->start_time)
                    <span class="rqd-meta-item">{{ $booking->start_time->format('D, M j, Y · g:i A') }} – {{ $booking->end_time?->format('g:i A') }}</span>
                @endif
            </div>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.bookings.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                All reservations
            </a>
        </div>
    </section>

    <div class="drv-notice" role="note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
        <p>This booking was created from an approved and signed request.
            Any changes you make here represent a GSU decision and should be agreed upon by both parties offline.</p>
    </div>

    @if ($errors->any())
        <div class="pk-errors" role="alert">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pk-form-layout">
        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="pk-form-card" id="modify-form">
            @csrf
            @method('PUT')

            {{-- Schedule --}}
            <div class="pk-step">
                <span class="pk-step-num">01</span>
                <div>
                    <h2>Schedule</h2>
                    <p class="pk-step-intro">Move the reservation to another day or time. The current date is pre-selected.</p>
                    <div class="dp">
                        <div class="dp-cal">
                            <div class="dp-head">
                                <p class="dp-month" id="dp-month" aria-live="polite"></p>
                                <div class="dp-nav">
                                    <button type="button" id="dp-prev" aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
                                    <button type="button" id="dp-next" aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
                                </div>
                            </div>
                            <div class="dp-week" aria-hidden="true"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                            <div class="dp-grid" id="dp-grid" role="group" aria-label="Choose the date of activity"></div>
                            <ul class="dp-legend" aria-hidden="true">
                                <li><i class="u-bg-slate-200"></i>Other bookings</li>
                                <li><i class="u-bg-ink"></i>Selected</li>
                                <li><i class="u-ring-ink"></i>Today</li>
                            </ul>
                            <p class="dp-status" id="dp-status" aria-live="polite"></p>
                        </div>

                        <div class="dp-side">
                            <div class="dp-fields">
                                <div class="dp-dates is-single">
                                    <div class="pk-field">
                                        <label for="date_activity" class="pk-label">Date of activity <span class="pk-req">*</span></label>
                                        <input type="text" id="date_activity" name="date_activity" class="pk-input" required inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD" value="{{ old('date_activity', $currentDate) }}">
                                    </div>
                                </div>
                                <div class="dp-times">
                                    <div class="pk-field">
                                        <label for="start_time" class="pk-label">Start time <span class="pk-req">*</span></label>
                                        <input type="time" id="start_time" name="start_time" class="pk-input" min="06:00" max="19:00" value="{{ old('start_time', $currentStart) }}">
                                    </div>
                                    <div class="pk-field">
                                        <label for="end_time" class="pk-label">End time <span class="pk-req">*</span></label>
                                        <input type="time" id="end_time" name="end_time" class="pk-input" min="06:00" max="19:00" value="{{ old('end_time', $currentEnd) }}">
                                    </div>
                                </div>
                            </div>
                            <p class="pk-help">Pick a day on the calendar or type it. Reservations must be between 6:00 AM and 7:00 PM.</p>
                            <div class="dp-dayinfo" id="dp-dayinfo" aria-live="polite">
                                <h3>On this day</h3>
                                <p>Pick a date to see what's already booked.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Venues --}}
            <div class="pk-step">
                <span class="pk-step-num">02</span>
                <div>
                    <h2>Venues <span class="pk-req u-text-danger">*</span></h2>
                    <p class="pk-step-intro">Select one or more facilities. Each card shows other bookings on the date from step 1.</p>
                    <div class="vb-head">
                        <span class="vb-day" id="vb-day">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                            <span id="vb-day-text">Pick a date in step 1 to see booked times</span>
                        </span>
                        <span class="vb-key" aria-hidden="true">
                            <span><i class="u-bg-success"></i>Free</span>
                            <span><i class="u-bg-warn"></i>Partly booked</span>
                            <span><i class="u-bg-danger"></i>Fully booked</span>
                        </span>
                    </div>
                    <div class="vb-grid" role="group" aria-label="Venues">
                        @foreach($facilities as $facility)
                            @php $isCurrent = $booking->facilities->contains($facility->id); @endphp
                            <div class="vb-card" data-name="{{ $facility->name }}">
                                <input type="checkbox" name="facility_ids[]" id="venue-{{ $facility->id }}" value="{{ $facility->id }}"
                                       data-name="{{ $facility->name }}"
                                       {{ in_array($facility->id, $selectedVenues, true) ? 'checked' : '' }}>
                                <label for="venue-{{ $facility->id }}">
                                    <span class="vb-top">
                                        <span class="u-minw-0">
                                            <span class="vb-name">{{ $facility->name }}</span>
                                            <span class="vb-owner">{{ $facility->location }}@if ($isCurrent)<span class="vb-mine">Current</span>@endif</span>
                                            @include('partials.facility-seating-capacity', ['facility' => $facility])
                                        </span>
                                        <span class="vb-count"><strong class="vb-count-num">–</strong><span>booked</span></span>
                                    </span>
                                    <span class="vb-slots" aria-live="polite"><span class="vb-idle">Pick a date to see times</span></span>
                                    <span class="vb-clash">Overlaps another booking — pick another time or venue.</span>
                                    <span class="vb-foot">
                                        <span class="pk-pill is-good vb-status">Available</span>
                                        <span class="u-row-6">
                                            <span class="vb-pick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>Selected</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Purpose + reason --}}
            <div class="pk-step">
                <span class="pk-step-num">03</span>
                <div>
                    <h2>Purpose &amp; reason</h2>
                    <p class="pk-step-intro">Update the event description if needed, then tell the requester why GSU is changing it.</p>

                    <div class="pp-block">
                        <p class="pp-title"><span class="pp-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></span><label class="u-m-0" for="purpose">Purpose <span class="pk-req u-text-danger">*</span></label></p>
                        <p class="pp-sub">What the event is. Pre-filled with the requester's original purpose.</p>
                        <div class="pp-text">
                            <textarea id="purpose" name="purpose" rows="3" maxlength="500" class="pk-input" data-meter="purpose">{{ old('purpose', $booking->purpose) }}</textarea>
                            <div class="pp-meter" data-meter-for="purpose" aria-hidden="true">
                                <span class="pp-meter-bar"><span></span></span>
                                <span class="pp-meter-num">0 / 500</span>
                            </div>
                        </div>
                    </div>

                    <div class="pp-block">
                        <p class="pp-title"><span class="pp-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></span><label class="u-m-0" for="reason">Reason for change <span class="pk-req u-text-danger">*</span></label></p>
                        <p class="pp-sub">Shown in the requester's notifications. Tap a reason to start, then add details.</p>
                        <div class="pp-chips" role="group" aria-label="Common reasons">
                            @foreach ($reasonTypes as $type)
                                <button type="button" class="pp-chip" data-type="{{ $type }}" aria-pressed="false">{{ $type }}</button>
                            @endforeach
                        </div>
                        <div class="pp-text">
                            <textarea id="reason" name="reason" rows="3" maxlength="500" class="pk-input" data-meter="reason"
                                      placeholder="Explain why GSU is changing the facility / schedule. This will appear in the requester's notifications.">{{ old('reason') }}</textarea>
                            <div class="pp-meter" data-meter-for="reason" aria-hidden="true">
                                <span class="pp-meter-bar"><span></span></span>
                                <span class="pp-meter-num">0 / 500</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Equipment --}}
            <div class="pk-step">
                <span class="pk-step-num">04</span>
                <div>
                    <h2>Facilities / Equipment to be used</h2>
                    <p class="pk-step-intro">Tap + to add what's needed. Leave the rest at 0.</p>
                    <div class="eq-bar">
                        <div class="eq-total" aria-live="polite"><strong id="eq-total">0</strong><span id="eq-total-text">items selected</span></div>
                        <div class="eq-quick">
                            <span class="eq-quick-label">Quick start</span>
                            <button type="button" data-preset='{"qty_monobloc":20,"qty_table":2,"qty_fan":2}'>Small meeting</button>
                            <button type="button" data-preset='{"qty_monobloc":100,"qty_table":4,"qty_rostrum":1,"qty_flag":1,"qty_sound":1}'>Seminar</button>
                            <button type="button" data-preset='{"qty_monobloc":300,"qty_table":10,"qty_fan":4,"qty_rostrum":1,"qty_flag":1,"qty_sound":1,"qty_led":1}'>Big program</button>
                            <button type="button" data-preset='{}'>Clear</button>
                        </div>
                    </div>
                    <div class="eq-grid">
                        @foreach($equipmentFields as $field => $label)
                            <div class="eq-tile" data-field="{{ $field }}">
                                <div class="eq-head">
                                    <span class="eq-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $equipmentIcons[$field] !!}</svg></span>
                                    <label for="{{ $field }}" class="eq-name">{{ $label }}</label>
                                </div>
                                <div class="eq-ctrl">
                                    <button type="button" class="eq-round" onclick="adjustQty('{{ $field }}', -1)" aria-label="{{ $label }} minus 1">&minus;</button>
                                    <input type="number" class="eq-count" min="0" inputmode="numeric" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $equip[$map[$field]] ?? 0) }}" data-label="{{ $label }}">
                                    <button type="button" class="eq-round" onclick="adjustQty('{{ $field }}', 1)" aria-label="{{ $label }} plus 1">+</button>
                                </div>
                                <div class="eq-more">
                                    <button type="button" onclick="adjustQty('{{ $field }}', -10)" aria-label="{{ $label }} minus 10">&minus;10</button>
                                    <button type="button" onclick="adjustQty('{{ $field }}', 10)" aria-label="{{ $label }} plus 10">+10</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pk-form-actions">
                <a href="{{ route('admin.bookings.index') }}" class="pk-btn-outline">Back</a>
                <button type="submit" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    Save changes &amp; notify requester
                </button>
            </div>
        </form>

        <aside class="pk-aside" aria-label="Reservation summary">
            <p class="pk-aside-label">After your changes</p>
            <div class="pk-ticket">
                <div class="pk-ticket-top">
                    <p class="pk-ticket-date" id="sum-date">Pick a date</p>
                    <p class="pk-ticket-time" id="sum-time">Start – end time</p>
                </div>
                <div class="pk-ticket-cut" aria-hidden="true"></div>
                <div class="pk-ticket-body">
                    <div class="pk-ticket-row"><span>Venues</span><strong id="sum-venues" class="is-empty">None selected</strong></div>
                    <div class="pk-ticket-row"><span>Purpose</span><strong id="sum-purpose" class="is-empty">—</strong></div>
                    <div class="pk-ticket-row"><span>Equipment</span><strong id="sum-equip" class="is-empty">None</strong></div>
                </div>
            </div>
            <p class="bke-changed" id="sum-changed" hidden>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                <span>Changed: <strong id="sum-changed-list"></strong></span>
            </p>

            <section class="bke-current">
                <p class="pk-aside-label">Currently booked</p>
                <div class="bke-person">
                    <span class="pk-avatar" aria-hidden="true">{{ $initials }}</span>
                    <div class="u-minw-0">
                        <p class="bke-person-name">{{ $requesterName }}</p>
                        <p class="bke-person-sub">{{ $booking->requester_type }} – {{ $booking->requester_unit ?? 'N/A' }}</p>
                    </div>
                </div>
                <dl class="bke-rows">
                    <div><dt>Venues</dt><dd>{{ $venueNames->isNotEmpty() ? $venueNames->implode(', ') : 'Unknown' }}</dd></div>
                    <div><dt>When</dt><dd>{{ $booking->start_time?->format('M d, Y') ?? '—' }} · {{ $booking->start_time?->format('g:i A') ?? '—' }} – {{ $booking->end_time?->format('g:i A') ?? '—' }}</dd></div>
                    <div><dt>Equipment</dt><dd>{{ $equipInUse->isNotEmpty() ? $equipInUse->map(fn ($e) => $e['qty'] . ' × ' . $e['label'])->implode(', ') : 'None' }}</dd></div>
                    <div><dt>Status</dt><dd><span class="pk-pill {{ $statusTone }}">{{ ucfirst($booking->status) }}</span></dd></div>
                </dl>
            </section>
        </aside>
    </div>

    {{-- Cancel (GSU override) --}}
    <section class="bke-danger">
        <div class="bke-danger-intro">
            <span class="bke-danger-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></span>
            <div>
                <h2>Cancel booking</h2>
                <p>GSU override. The requester is notified and this cannot be undone.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="bke-danger-form"
              data-confirm="Cancel this booking? This cannot be undone." data-confirm-title="Cancel booking?" data-confirm-action="Cancel booking" data-confirm-tone="danger">
            @csrf
            <div class="pk-field">
                <label class="pk-label" for="cancel_reason">Cancellation reason</label>
                <textarea id="cancel_reason" name="reason" rows="2" class="pk-input"
                          placeholder="Reason for cancellation (visible to requester)"></textarea>
            </div>
            <button type="submit" class="rqd-cancel-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="16" height="16"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                Cancel booking
            </button>
        </form>
    </section>
</div>

<style>
    .bke-changed { display: flex; gap: 8px; align-items: flex-start; margin: 0; padding: 10px 12px; border-radius: 12px; border: 1px solid #FDE68A; background: #FFFBEB; color: #92400E; font-size: 12.5px; line-height: 1.45; }
    .bke-changed svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px; }
    .bke-current { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; padding: 16px; border-radius: 16px; border: 1px solid #E2E8F0; background: #fff; }
    .bke-person { display: flex; align-items: center; gap: 12px; }
    .bke-person .pk-avatar { width: 38px; height: 38px; font-size: 13px; flex-shrink: 0; }
    .bke-person-name { margin: 0; font-size: 14.5px; font-weight: 600; color: #0f172a; }
    .bke-person-sub { margin: 2px 0 0; font-size: 12.5px; color: #64748B; }
    .bke-rows { display: flex; flex-direction: column; margin: 0; }
    .bke-rows > div { display: grid; grid-template-columns: 82px minmax(0, 1fr); gap: 10px; padding: 9px 0; border-top: 1px solid #F1F5F9; }
    .bke-rows dt { font-size: 12px; font-weight: 600; color: #64748B; }
    .bke-rows dd { margin: 0; font-size: 13px; font-weight: 600; color: #0f172a; overflow-wrap: anywhere; }

    .bke-danger { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); gap: 20px; align-items: start; margin-top: 24px; padding: 20px 22px; border-radius: 18px; border: 1px dashed #FECACA; background: #FFFBFB; }
    .bke-danger-intro { display: flex; gap: 12px; }
    .bke-danger-icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; flex-shrink: 0; border-radius: 12px; background: #FEF2F2; color: #B91C1C; }
    .bke-danger-icon svg { width: 20px; height: 20px; }
    .bke-danger h2 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
    .bke-danger-intro p { margin: 4px 0 0; font-size: 13px; color: #64748B; }
    .bke-danger-form { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
    .bke-danger-form .pk-field { width: 100%; }
    .bke-danger-form textarea { resize: vertical; }
    @media (max-width: 760px) { .bke-danger { grid-template-columns: 1fr; } }
</style>

@include('admin.bookings.partials.booking-picker-script', ['ignore' => $ignoreSelf])
<script>
(function () {
    const form = document.getElementById('modify-form');
    const $ = (id) => document.getElementById(id);
    const { pickedVenues, pad } = window.bkPicker;
    const original = @json($original);

    // Character meters (server limit is 500)
    document.querySelectorAll('textarea[data-meter]').forEach(area => {
        const meter = document.querySelector('[data-meter-for="' + area.dataset.meter + '"]');
        const sync = () => {
            const len = area.value.length;
            meter.querySelector('.pp-meter-num').textContent = len + ' / 500';
            meter.querySelector('.pp-meter-bar span').style.width = Math.min(100, len / 5) + '%';
            meter.classList.toggle('is-near', len >= 450);
        };
        area.addEventListener('input', sync);
        sync();
    });

    // Reason chips: start the reason with a common phrase
    (function () {
        const area = $('reason');
        const chips = [...document.querySelectorAll('.pp-chip')];
        const types = chips.map(c => c.dataset.type);
        const sep = ' — ';
        const currentType = () => types.find(t => area.value.startsWith(t + sep) || area.value === t) || '';
        const sync = () => { const t = currentType(); chips.forEach(c => c.setAttribute('aria-pressed', c.dataset.type === t ? 'true' : 'false')); };
        chips.forEach(chip => chip.addEventListener('click', () => {
            const old = currentType();
            const rest = old ? area.value.slice(Math.min(area.value.length, old.length + sep.length)) : area.value;
            area.value = (chip.dataset.type === old ? '' : chip.dataset.type + sep) + rest.replace(/^\s+/, '');
            area.dispatchEvent(new Event('input', { bubbles: true }));
            area.focus();
            area.setSelectionRange(area.value.length, area.value.length);
        }));
        area.addEventListener('input', sync);
        sync();
    })();

    // Live summary + what changed (display only)
    const fmtTime = (v) => {
        if (!v) return '';
        const [h, m] = v.split(':').map(Number);
        return ((h % 12) || 12) + ':' + pad(m) + (h < 12 ? ' AM' : ' PM');
    };
    function setText(el, text, empty) { el.textContent = text || empty; el.classList.toggle('is-empty', !text); }
    function render() {
        const d = $('date_activity').value, st = $('start_time').value, et = $('end_time').value;
        $('sum-date').textContent = d
            ? new Date(d + 'T00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })
            : 'Pick a date';
        const s = fmtTime(st), e = fmtTime(et);
        $('sum-time').textContent = s || e ? (s || '…') + ' – ' + (e || '…') : 'Start – end time';
        const venues = pickedVenues();
        setText($('sum-venues'), venues.join(', '), 'None selected');
        const purpose = $('purpose').value.trim();
        setText($('sum-purpose'), purpose.length > 90 ? purpose.slice(0, 90) + '…' : purpose, '—');
        const equip = [...form.querySelectorAll('.eq-count')]
            .filter(i => parseInt(i.value || '0', 10) > 0)
            .map(i => i.value + ' × ' + i.dataset.label);
        setText($('sum-equip'), equip.join(', '), 'None');

        const changed = [];
        if (d !== original.date) changed.push('date');
        if (st !== original.start || et !== original.end) changed.push('time');
        if ([...venues].sort().join('|') !== [...original.venues].sort().join('|')) changed.push('venues');
        if (purpose !== original.purpose.trim()) changed.push('purpose');
        $('sum-changed').hidden = !changed.length;
        $('sum-changed-list').textContent = changed.join(', ');
    }
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    render();
})();
</script>
@endsection
