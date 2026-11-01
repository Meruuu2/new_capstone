@extends('layouts.admin')
@section('title', 'Create Direct Booking')

@section('admin-content')
@include('layouts.partials.page-kit')
{{-- Same building blocks as the College/Org Facilities Utilization Request (date picker, venue board,
     equipment tiles, summary ticket). Fields and names are unchanged from the original direct form. --}}
@php
    $equipmentFields = [
        'qty_monobloc' => 'Monobloc Chair',
        'qty_table'    => 'Long Table',
        'qty_fan'      => 'Electric Fan',
        'qty_rostrum'  => 'Rostrum',
        'qty_flag'     => 'Flag & School Color',
        'qty_sound'    => 'Sound',
        'qty_led'      => 'LED Wall',
    ];
    // Line icons for the equipment tiles (same set as the college form)
    $equipmentIcons = [
        'qty_monobloc' => '<path d="M7 3h10v8H7z"/><path d="M5 11h14M7 11v10M17 11v10M7 16h10"/>',
        'qty_table'    => '<path d="M3 8h18M5 8v12M19 8v12M3 8l2-3h14l2 3"/>',
        'qty_fan'      => '<circle cx="12" cy="10" r="1.6"/><path d="M12 8.4c0-3 1-5 3-5s2 3-1.4 5.2M13.4 11c2.6 1.5 3.9 3.3 2.9 5s-3.4-.3-4-3.7M10.6 10.9c-2.6 1.5-4.8 1.6-5.8-.1s2-2.8 5.2-1.6"/><path d="M12 12v8M8 21h8"/>',
        'qty_rostrum'  => '<path d="M6 8h12l-1.5 13h-9z"/><path d="M4 5h16v3H4zM12 5V3"/>',
        'qty_flag'     => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'qty_sound'    => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="14" r="3"/><circle cx="12" cy="7.5" r="1"/>',
        'qty_led'      => '<rect x="2" y="4" width="20" height="13" rx="1.5"/><path d="M8 21h8M12 17v4M6 8h4M6 11h7"/>',
    ];
    $oldVenues = array_map('intval', (array) old('facility_ids', []));
@endphp

<div class="pk-page drv-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9z"/></svg>
                Priority booking
            </p>
            <h1>Create Direct Reservation</h1>
            <p class="pk-hero-sub">Book a facility right away for urgent, high-priority events. It's reserved as soon as you submit.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.bookings.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                All reservations
            </a>
        </div>
    </section>

    <div class="drv-notice" role="note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
        <p>Use this only for urgent, high-priority bookings (emergency meetings, high-profile events).
            Conflicting bookings will be set to <strong>Pending</strong> and their requesters notified.</p>
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
        <form action="{{ route('admin.bookings.store-direct') }}" method="POST" class="pk-form-card" id="direct-form">
            @csrf

            {{-- Date & Time --}}
            <div class="pk-step">
                <span class="pk-step-num">01</span>
                <div>
                    <h2>Schedule</h2>
                    <p class="pk-step-intro">When the activity takes place.</p>
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
                                <li><i class="u-bg-slate-200"></i>Booked</li>
                                <li><i class="u-bg-ink"></i>Selected</li>
                                <li><i class="u-ring-ink"></i>Today</li>
                            </ul>
                            <p class="dp-status" id="dp-status" aria-live="polite"></p>
                        </div>

                        <div class="dp-side">
                            <div class="dp-fields">
                                <div class="dp-dates is-single">
                                    <div class="pk-field">
                                        <label for="date_activity" class="pk-label">Date of Activity <span class="pk-req">*</span></label>
                                        <input type="text" name="date_activity" id="date_activity" class="pk-input" required inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD" value="{{ old('date_activity') }}">
                                    </div>
                                </div>
                                <div class="dp-times">
                                    <div class="pk-field">
                                        <label for="start_time" class="pk-label">Start Time <span class="pk-req">*</span></label>
                                        <input type="time" name="start_time" id="start_time" class="pk-input" required min="06:00" max="19:00" value="{{ old('start_time') }}">
                                    </div>
                                    <div class="pk-field">
                                        <label for="end_time" class="pk-label">End Time <span class="pk-req">*</span></label>
                                        <input type="time" name="end_time" id="end_time" class="pk-input" required min="06:00" max="19:00" value="{{ old('end_time') }}">
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

            {{-- Venues (multi-select) --}}
            <div class="pk-step">
                <span class="pk-step-num">02</span>
                <div>
                    <h2>Venues to be utilized <span class="pk-req u-text-danger">*</span></h2>
                    <p class="pk-step-intro">Pick one or more venues. Venues that are unavailable or under maintenance can't be selected.</p>
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
                    <div class="vb-grid" role="group" aria-label="Venues to be utilized">
                        @foreach($facilities as $facility)
                            @php
                                $isUnavailable = in_array($facility->availability_status, ['unavailable', 'maintenance'], true);
                                $statusLabel = $facility->availability_status === 'maintenance' ? 'Under Maintenance' : 'Unavailable';
                            @endphp
                            <div class="vb-card" data-name="{{ $facility->name }}">
                                <input type="checkbox" name="facility_ids[]" id="venue-{{ $facility->id }}" value="{{ $facility->id }}"
                                       data-name="{{ $facility->name }}"
                                       {{ in_array($facility->id, $oldVenues, true) ? 'checked' : '' }}
                                       {{ $isUnavailable ? 'disabled' : '' }}>
                                <label for="venue-{{ $facility->id }}">
                                    <span class="vb-top">
                                        <span class="u-minw-0">
                                            <span class="vb-name">{{ $facility->name }}</span>
                                            @if ($facility->location)<span class="vb-owner">{{ $facility->location }}</span>@endif
                                            @include('partials.facility-seating-capacity', ['facility' => $facility])
                                        </span>
                                        <span class="vb-count"><strong class="vb-count-num">–</strong><span>booked</span></span>
                                    </span>
                                    <span class="vb-slots" aria-live="polite"><span class="vb-idle">{{ $isUnavailable ? 'Not available for booking' : 'Pick a date to see times' }}</span></span>
                                    <span class="vb-clash">Overlaps an existing booking — it will be set to Pending.</span>
                                    <span class="vb-foot">
                                        <span class="pk-pill {{ $isUnavailable ? ($facility->availability_status === 'maintenance' ? 'is-warn' : 'is-bad') : 'is-good' }} vb-status">{{ $isUnavailable ? $statusLabel : 'Available' }}</span>
                                        <span class="u-row-6">
                                            <span class="vb-pick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>Selected</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="pk-error drv-venue-error" id="venue-error" role="alert">Select at least one venue.</p>
                </div>
            </div>

            {{-- Requester + purpose (same fields as before, restyled) --}}
            <div class="pk-step">
                <span class="pk-step-num">03</span>
                <div>
                    <h2>Requester &amp; purpose</h2>
                    <p class="pk-step-intro">Who this urgent booking is for and why.</p>

                    <div class="drv-fields">
                        <div class="pk-field">
                            <label class="pk-label" for="requester_name">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>Requester Name / Unit <span class="pk-req">*</span></span>
                            </label>
                            <input type="text" name="requester_name" id="requester_name" class="pk-input" required value="{{ old('requester_name') }}" placeholder="e.g. CCIS Dean" aria-describedby="requester_help">
                            <p class="pk-help" id="requester_help">Ex: "CCIS Dean", "CEA Program Head", "GSU Office", or specific office/organization.</p>
                        </div>

                        <div class="pk-field">
                            <label class="pk-label" for="purpose">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                                <span>Purpose <span class="pk-req">*</span></span>
                            </label>
                            <textarea name="purpose" id="purpose" rows="3" class="pk-input" required aria-describedby="purpose_help">{{ old('purpose') }}</textarea>
                            <p class="pk-help" id="purpose_help">What the event is, in a sentence or two.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Equipment --}}
            <div class="pk-step">
                <span class="pk-step-num">04</span>
                <div>
                    <h2>Facilities / Equipment to be used</h2>
                    <p class="pk-step-intro">Tap + to add what you need. Leave the rest at 0.</p>
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
                        @foreach($equipmentFields as $name => $label)
                            <div class="eq-tile" data-field="{{ $name }}">
                                <div class="eq-head">
                                    <span class="eq-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $equipmentIcons[$name] !!}</svg></span>
                                    <label for="{{ $name }}" class="eq-name">{{ $label }}</label>
                                </div>
                                <div class="eq-ctrl">
                                    <button type="button" class="eq-round" onclick="adjustQty('{{ $name }}', -1)" aria-label="{{ $label }} minus 1">&minus;</button>
                                    <input type="number" class="eq-count" name="{{ $name }}" id="{{ $name }}" min="0" inputmode="numeric" value="{{ old($name, 0) }}" data-label="{{ $label }}">
                                    <button type="button" class="eq-round" onclick="adjustQty('{{ $name }}', 1)" aria-label="{{ $label }} plus 1">+</button>
                                </div>
                                <div class="eq-more">
                                    <button type="button" onclick="adjustQty('{{ $name }}', -10)" aria-label="{{ $label }} minus 10">&minus;10</button>
                                    <button type="button" onclick="adjustQty('{{ $name }}', 10)" aria-label="{{ $label }} plus 10">+10</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pk-form-actions">
                <a href="{{ route('admin.bookings.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9z"/></svg>
                    Create Priority Booking
                </button>
            </div>
        </form>

        <aside class="pk-aside" aria-label="Booking summary">
            <p class="pk-aside-label">Booking summary</p>
            <div class="pk-ticket">
                <div class="pk-ticket-top">
                    <p class="pk-ticket-date" id="sum-date">Pick a date</p>
                    <p class="pk-ticket-time" id="sum-time">Start – end time</p>
                </div>
                <div class="pk-ticket-cut" aria-hidden="true"></div>
                <div class="pk-ticket-body">
                    <div class="pk-ticket-row"><span>Venues</span><strong id="sum-venues" class="is-empty">None selected</strong></div>
                    <div class="pk-ticket-row"><span>Requester</span><strong id="sum-requester" class="is-empty">—</strong></div>
                    <div class="pk-ticket-row"><span>Purpose</span><strong id="sum-purpose" class="is-empty">—</strong></div>
                    <div class="pk-ticket-row"><span>Equipment</span><strong id="sum-equip" class="is-empty">None</strong></div>
                </div>
            </div>
        </aside>
    </div>
</div>

@include('admin.bookings.partials.booking-picker-script')
<script>
(function () {
    const form = document.getElementById('direct-form');
    const $ = (id) => document.getElementById(id);
    const { pickedVenues, venueBoxes, pad } = window.bkPicker;

    // ---- Live summary (display only) ----
    const fmtTime = (v) => {
        if (!v) return '';
        const [h, m] = v.split(':').map(Number);
        return ((h % 12) || 12) + ':' + pad(m) + (h < 12 ? ' AM' : ' PM');
    };
    function setText(el, text, empty) {
        el.textContent = text || empty;
        el.classList.toggle('is-empty', !text);
    }
    function render() {
        const d = $('date_activity').value;
        $('sum-date').textContent = d
            ? new Date(d + 'T00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })
            : 'Pick a date';
        const s = fmtTime($('start_time').value), e = fmtTime($('end_time').value);
        $('sum-time').textContent = s || e ? (s || '…') + ' – ' + (e || '…') : 'Start – end time';
        setText($('sum-venues'), pickedVenues().join(', '), 'None selected');
        setText($('sum-requester'), $('requester_name').value.trim(), '—');
        const purpose = $('purpose').value.trim();
        setText($('sum-purpose'), purpose.length > 90 ? purpose.slice(0, 90) + '…' : purpose, '—');
        const equip = [...form.querySelectorAll('.eq-count')]
            .filter(i => parseInt(i.value || '0', 10) > 0)
            .map(i => i.value + ' × ' + i.dataset.label);
        setText($('sum-equip'), equip.join(', '), 'None');
        if (venueBoxes().some(b => b.checked)) $('venue-error').classList.remove('is-shown');
    }
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    // The old multi-select was "required"; keep that check for the checkbox list.
    form.addEventListener('submit', function (ev) {
        if (!venueBoxes().some(b => b.checked)) {
            ev.preventDefault();
            $('venue-error').classList.add('is-shown');
            $('venue-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    render();
})();
</script>
@endsection
