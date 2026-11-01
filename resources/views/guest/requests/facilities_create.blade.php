@extends('layouts.guest')

@section('guest-content')
@include('layouts.partials.page-kit')
@php
    $minActivityDate = now()->addDays(30)->toDateString(); // same rule as StoreFacilitiesUtilizationRequest
    // Official per-day rates by venue name (from the shared rate sheet; facilities have no fee column)
    $normName = fn ($n) => preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string) $n)));
    $venueRates = collect(\App\Support\VenueRates::all());
    $rateByName = $venueRates
        ->mapWithKeys(fn ($v) => [$normName($v['name']) => (float) str_replace(',', '', preg_replace('/^PhP\s*([\d,.]+).*$/', '$1', $v['rate']))]);
    $rateLabelByName = $venueRates->mapWithKeys(function ($venue) use ($normName) {
        $rate = str_replace('PhP', '₱', $venue['rate']);
        if (! preg_match('/(?:\/\s*day|in a day)/i', $rate)) {
            $rate .= ' / day';
        }

        return [$normName($venue['name']) => $rate];
    });
    $equipmentList = [
        ['name' => 'qty_monobloc', 'label' => 'Monobloc Chair'],
        ['name' => 'qty_table', 'label' => 'Table'],
        ['name' => 'qty_fan', 'label' => 'Electric Fan'],
        ['name' => 'qty_rostrum', 'label' => 'Rostrum'],
        ['name' => 'qty_flag', 'label' => 'Flag & School Color'],
        ['name' => 'qty_sound', 'label' => 'Sound System'],
        ['name' => 'qty_led', 'label' => 'LED Wall'],
    ];
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M12 12v6M9 15h6"/></svg>
                New request
            </p>
            <h1>Application for Facilities Rental</h1>
            <p class="pk-hero-sub">Fill in your details, pick a venue, and attach your payment receipt. Payment is required for all guest bookings.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('guest.rates') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                Venue rates
            </a>
        </div>
    </section>

    {{-- Official UA Header --}}
    <div class="ga-mast">
        <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="University of Antique seal">
        <div>
            <p>Republic of the Philippines</p>
            <strong>UNIVERSITY OF ANTIQUE</strong>
            <p>Sibalom, Antique</p>
            <p class="ga-form-title">APPLICATION FORM for FACILITIES RENTAL</p>
        </div>
        <p class="ga-code">GSU-FM-004<br>Rev.0 / 03-26-26</p>
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
        <form method="POST" action="{{ route('guest.requests.facilities.store') }}" enctype="multipart/form-data" class="pk-form-card" id="rental-form">
            @csrf

            {{-- Applicant --}}
            <div class="pk-step">
                <span class="pk-step-num">01</span>
                <div>
                    <h2>Applicant</h2>
                    <p class="pk-step-intro">Control number and date are filled in for you.</p>
                    <div class="pk-grid-2">
                        <div class="pk-field">
                            <label class="pk-label" for="ga-ctrl">Ctrl. No.</label>
                            <input type="text" id="ga-ctrl" readonly class="pk-input" value="(Auto-generated after submission)">
                        </div>
                        <div class="pk-field">
                            <label class="pk-label" for="ga-date">Date of Application</label>
                            <input type="text" id="ga-date" readonly class="pk-input" value="{{ date('F d, Y') }}">
                        </div>
                        <div class="pk-field">
                            <label class="pk-label" for="client_name">Name <span class="pk-req">*</span></label>
                            <input type="text" name="client_name" id="client_name" required class="pk-input" value="{{ auth()->user()->name }}" readonly>
                        </div>
                        <div class="pk-field">
                            <label class="pk-label" for="contact_number">Contact No. <span class="pk-req">*</span></label>
                            <input type="tel" name="contact_number" id="contact_number" required inputmode="numeric" maxlength="13" pattern="09[0-9]{2}-[0-9]{3}-[0-9]{4}" title="Enter a mobile number in the format 09XX-XXX-XXXX." class="pk-input" autocomplete="tel" value="{{ old('contact_number', auth()->user()->contact_number ?? '') }}" placeholder="09XX-XXX-XXXX" aria-describedby="contact-number-help">
                            <p class="pk-help" id="contact-number-help">Numbers only, in the format 09XX-XXX-XXXX.</p>
                        </div>
                    </div>
                    <div class="u-stack-14 u-mt-14">
                        <div class="pk-field">
                            <label class="pk-label" for="address">Address <span class="pk-req">*</span></label>
                            <input type="text" name="address" id="address" required class="pk-input" value="{{ old('address') }}" placeholder="Enter your complete address">
                        </div>
                        <div class="pk-field">
                            <label class="pk-label" for="organization">Organization/Agency <span class="pk-req">*</span></label>
                            <input type="text" name="organization" id="organization" required class="pk-input" value="{{ old('organization', auth()->user()->organization_name ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="pk-step">
                <span class="pk-step-num">02</span>
                <div>
                    <h2>Schedule</h2>
                    <p class="pk-step-intro">When the activity takes place.</p>
                    <div class="dp-mode" role="group" aria-label="Event length">
                        <button type="button" data-mode="single" aria-pressed="true">One day</button>
                        <button type="button" data-mode="multi" aria-pressed="false">Several days</button>
                    </div>
                    <div class="dp" data-calendar-url="{{ route('calendar.data') }}">
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
                                <li><i class="u-sw-unavailable"></i>Unavailable</li>
                            </ul>
                            <p class="dp-status" id="dp-status" aria-live="polite"></p>
                        </div>

                        <div class="dp-side">
                            <div class="dp-fields">
                                <div class="dp-dates is-single" id="dp-dates">
                                    <div class="pk-field">
                                        <label for="date_activity" class="pk-label" id="dp-start-label">Date of Activity <span class="pk-req">*</span></label>
                                        <input type="text" name="date_activity" id="date_activity" required inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD" class="pk-input" min="{{ $minActivityDate }}" value="{{ old('date_activity') }}">
                                    </div>
                                    <div class="pk-field dp-end">
                                        <label for="date_end" class="pk-label">End date <span class="pk-req">*</span></label>
                                        {{-- Only sent for multi-day events (disabled in One day mode) --}}
                                        <input type="text" name="date_end" id="date_end" inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="YYYY-MM-DD" class="pk-input" min="{{ $minActivityDate }}" value="{{ old('date_end') }}" disabled>
                                    </div>
                                </div>
                                <div class="dp-times">
                                    <div class="pk-field">
                                        <label for="start_time" class="pk-label">Start Time <span class="pk-req">*</span></label>
                                        <input type="time" name="start_time" id="start_time" required min="06:00" max="19:00" class="pk-input" value="{{ old('start_time') }}">
                                    </div>
                                    <div class="pk-field">
                                        <label for="end_time" class="pk-label">End Time <span class="pk-req">*</span></label>
                                        <input type="time" name="end_time" id="end_time" required min="06:00" max="19:00" class="pk-input" value="{{ old('end_time') }}">
                                    </div>
                                </div>
                            </div>
                            <p class="pk-help">Reservations must start and end between 6:00 AM and 7:00 PM.</p>
                            <div id="time-availability" class="time-availability is-neutral" role="status" aria-live="polite" data-conflict="false">
                                <strong>Availability check</strong>
                                <p>Choose a date, facility and time to check for conflicts.</p>
                            </div>
                            <p class="dp-span" id="dp-span" hidden></p>
                            <p class="pk-help" id="dp-help">Pick a day on the calendar or type it. Requests need at least 30 days' notice and must be between 6:00 AM and 7:00 PM.</p>
                            <div class="dp-dayinfo" id="dp-dayinfo" aria-live="polite">
                                <h3>On this day</h3>
                                <p>Pick a date to see what's already booked.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Facility / Venue --}}
            <div class="pk-step">
                <span class="pk-step-num">03</span>
                <div>
                    <h2>Venues &amp; purpose <span class="pk-req u-text-danger">*</span></h2>
                    <p class="pk-step-intro">Select facilities for the same schedule. Existing reservations block overlapping times; non-overlapping times remain available.</p>
                    @php
                        // Same order the controller gave (alphabetical)
                        $boardFacilities = collect($coreFacilities ?? [])->values();
                    @endphp
                    @if ($boardFacilities->isEmpty())
                        <p class="u-mb-4 u-rounded-xl u-border u-border-amber-200 u-bg-amber-50 u-p-4 u-text-sm" role="status">
                            No venues are currently available to request. Please contact GSU Facility Management.
                        </p>
                    @endif
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
                    @php $selectedFacilityIds = array_map('intval', (array) old('facility_ids', old('facility_id') ? [old('facility_id')] : [])); @endphp
                    <div class="vb-grid" role="group" aria-label="Venues">
                        @foreach($boardFacilities as $facility)
                            @php
                                $isUnavailable = in_array($facility->availability_status, ['unavailable', 'maintenance'], true);
                                $statusLabel = $facility->availability_status === 'maintenance' ? 'Under Maintenance' : 'Unavailable';
                                $owner = match ($facility->owner_type) { 'gsu' => 'GSU', 'college' => 'College (' . ($facility->owner_college ?? 'N/A') . ')', 'org' => 'Organization', default => ucfirst($facility->owner_type ?? '') };
                                $fee = (float) ($rateByName[$normName($facility->name)] ?? 0);
                                $rateLabel = $rateLabelByName[$normName($facility->name)] ?? 'Rate set by GSU';
                            @endphp
                            <div class="vb-card" data-name="{{ $facility->name }}">
                                <input type="checkbox" name="facility_ids[]" id="venue-{{ $facility->id }}" value="{{ $facility->id }}"
                                       data-name="{{ $facility->name }}"
                                       data-rental-fee="{{ $fee }}"
                                       {{ in_array($facility->id, $selectedFacilityIds, true) ? 'checked' : '' }}
                                       {{ $isUnavailable ? 'disabled' : '' }}>
                                <label for="venue-{{ $facility->id }}">
                                    <span class="vb-top">
                                        <span class="u-minw-0">
                                            <span class="vb-name">{{ $facility->name }}</span>
                                            <span class="vb-owner">{{ $owner }}<span class="vb-rate">{{ $rateLabel }}</span></span>
                                            @include('partials.facility-seating-capacity', ['facility' => $facility])
                                        </span>
                                        <span class="vb-count"><strong class="vb-count-num">–</strong><span>booked</span></span>
                                    </span>
                                    <span class="vb-slots" aria-live="polite"><span class="vb-idle">{{ $isUnavailable ? 'Not available for booking' : 'Pick a date to see times' }}</span></span>
                                    <span class="vb-clash">This time conflicts with an existing reservation.</span>
                                    <span class="vb-foot">
                                        <span class="pk-pill {{ $isUnavailable ? ($facility->availability_status === 'maintenance' ? 'is-warn' : 'is-bad') : 'is-good' }} vb-status">{{ $isUnavailable ? $statusLabel : 'Available' }}</span>
                                        <span class="u-row-6">
                                            <span class="vb-pick"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>Selected</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            @error('facility_ids')<p class="pr-err">{{ $message }}</p>@enderror
                            @error('facility_ids.*')<p class="pr-err">{{ $message }}</p>@enderror
                            <p class="pr-err" id="facility-selection-error" role="alert" hidden>Select at least one facility.</p>
                        @endforeach
                    </div>
                    <div class="pk-field ga-purpose">
                        <label class="pk-label" for="purpose">Purpose <span class="pk-req">*</span></label>
                        <textarea name="purpose" id="purpose" required rows="2" class="pk-input" placeholder="State the purpose of facility rental">{{ old('purpose') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Facilities/Equipment Table --}}
            <div class="pk-step">
                <span class="pk-step-num">04</span>
                <div>
                    <h2>Facilities / Equipment to be used</h2>
                    <p class="pk-step-intro">The venue fee is filled in from the venue and days you pick; add equipment quantities below.</p>
                    <div class="ga-fees">
                        <table>
                            <thead>
                                <tr>
                                    <th class="u-text-left">Facility</th>
                                    <th class="ga-c">Qty</th>
                                    <th class="ga-r">Rental Fee</th>
                                    <th class="ga-c">No. of Days</th>
                                    <th class="ga-c">No. of Hours</th>
                                    <th class="ga-r">Total</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentTableBody">
                                {{-- Main Facility Row --}}
                                <tr id="mainFacilityRow">
                                    <td id="facilityNameCell">-</td>
                                    <td class="ga-c">1</td>
                                    <td class="ga-r" id="rentalFeeCell">₱ 0.00</td>
                                    <td class="ga-c" id="daysCell">1</td>
                                    <td class="ga-c" id="hoursCell">0</td>
                                    <td class="ga-r" id="totalCell">₱ 0.00</td>
                                </tr>

                                {{-- Equipment Rows --}}
                                @foreach($equipmentList as $equipment)
                                <tr>
                                    <td><label for="{{ $equipment['name'] }}">{{ $equipment['label'] }}</label></td>
                                    <td class="ga-c">
                                        <input type="number" name="{{ $equipment['name'] }}" id="{{ $equipment['name'] }}" min="0" value="0">
                                    </td>
                                    <td class="ga-r">-</td>
                                    <td class="ga-c">-</td>
                                    <td class="ga-c">-</td>
                                    <td class="ga-r">-</td>
                                </tr>
                                @endforeach

                                {{-- Total Row --}}
                                <tr class="ga-total">
                                    <td colspan="5" class="ga-r u-uppercase">TOTAL</td>
                                    <td class="ga-r" id="grandTotalCell">₱ 0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Certification + Signatories --}}
            <div class="pk-step">
                <span class="pk-step-num">05</span>
                <div>
                    <h2>Certification &amp; approval</h2>
                    <p class="pk-step-intro">Confirm the use of the venue and name your immediate head.</p>
                    <div class="ga-certify">
                        <p>I hereby certify that the venue shall be used solely for the purpose stated herein.</p>
                        <div class="pk-field">
                            <label class="pk-label" for="ga-rep">Client/Representative</label>
                            <input type="text" id="ga-rep" readonly class="pk-input" value="{{ auth()->user()->name }}">
                        </div>
                    </div>
                    <div class="pk-field u-mt-14">
                        <label class="pk-label">Noted by (Immediate Head) <span class="pk-req">*</span></label>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="notary_type" value="custom" checked style="width: auto;">
                                <span>Others (Specify Name)</span>
                            </label>
                        </div>
                        <input type="text" name="noted_signatory_custom" id="noted_signatory_custom" required class="pk-input" value="{{ old('noted_signatory_custom') }}" placeholder="Enter name of organization representative/immediate head" style="margin-top: 12px;">
                        <p class="pk-help">Enter the name of your organization's representative or immediate head.</p>
                    </div>
                    <p class="ga-small">Director/Unit Head and OIC-VP for Administration and Finance approval will be processed by the GSU office.</p>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="pk-form-actions">
                <a href="{{ route('guest.dashboard') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" id="submitBtn" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    <span>Submit Application</span>
                </button>
            </div>
        </form>

        <aside class="pk-aside" aria-label="Application summary">
            <p class="pk-aside-label">Application summary</p>
            <div class="pk-ticket">
                <div class="pk-ticket-top">
                    <p class="pk-ticket-date" id="sum-date">Pick a date</p>
                    <p class="pk-ticket-time" id="sum-time">Start – end time</p>
                </div>
                <div class="pk-ticket-cut" aria-hidden="true"></div>
                <div class="pk-ticket-body">
                    <div class="pk-ticket-row"><span>Facility</span><strong id="sum-venue" class="is-empty">None selected</strong></div>
                    <div class="pk-ticket-row"><span>Purpose</span><strong id="sum-purpose" class="is-empty">—</strong></div>
                </div>
            </div>
            <div class="ga-estimate">
                <span>Estimated venue total</span>
                <strong id="sum-total">₱ 0.00</strong>
                <p>Final amount is confirmed by GSU.</p>
            </div>
        </aside>
    </div>
</div>

<script>
// Rental fee estimate: picked venue card × number of days in the chosen range (final amount is confirmed by GSU)
const form = document.getElementById('rental-form');
function selectedVenues() { return Array.from(form.querySelectorAll('input[name="facility_ids[]"]:checked')); }
function bookingDays() {
    const s = form.querySelector('input[name="date_activity"]').value;
    const endInput = form.querySelector('input[name="date_end"]');
    const e = endInput && !endInput.disabled ? endInput.value : '';
    if (!s) return 1;
    if (!e || e <= s) return 1;
    return Math.round((new Date(e + 'T00:00') - new Date(s + 'T00:00')) / 864e5) + 1;
}
const peso = (n) => '₱ ' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function calculateTotal() {
    const venues = selectedVenues();
    const rentalFee = venues.reduce((total, venue) => total + (parseFloat(venue.dataset.rentalFee) || 0), 0);
    const startTime = form.querySelector('input[name="start_time"]').value;
    const endTime = form.querySelector('input[name="end_time"]').value;
    let hours = 0;
    if (startTime && endTime) {
        hours = Math.abs(new Date('2000-01-01 ' + endTime) - new Date('2000-01-01 ' + startTime)) / 36e5;
    }
    const days = bookingDays();
    const total = rentalFee * days;
    document.getElementById('facilityNameCell').textContent = venues.map(venue => venue.dataset.name).join(', ') || '-';
    document.getElementById('rentalFeeCell').textContent = peso(rentalFee);
    document.getElementById('daysCell').textContent = days;
    document.getElementById('hoursCell').textContent = hours.toFixed(1);
    document.getElementById('totalCell').textContent = peso(total);
    document.getElementById('grandTotalCell').textContent = peso(total);
}
form.addEventListener('input', calculateTotal);
form.addEventListener('change', calculateTotal);
calculateTotal();

// Prevent double submission (target the application form; the sidebar's logout form comes first in the page)
const submitBtn = document.getElementById('submitBtn');
const submitLabel = submitBtn.querySelector('span');

form.addEventListener('submit', function() {
    submitBtn.disabled = true;
    submitLabel.textContent = 'Submitting...';
    submitBtn.style.opacity = '.6';
    submitBtn.style.cursor = 'not-allowed';

    setTimeout(function() {
        submitBtn.disabled = false;
        submitLabel.textContent = 'Submit Application';
        submitBtn.style.opacity = '';
        submitBtn.style.cursor = '';
    }, 5000);
});

// Show chosen file names inside the drop zones
document.querySelectorAll('.pk-drop input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
        const sub = input.closest('.pk-drop').querySelector('.pk-drop-sub');
        const names = Array.from(input.files).map(f => f.name);
        sub.textContent = names.length ? names.join(', ') : input.dataset.empty;
    });
});

// Live summary (display only; reads the same values the fee table uses)
(function () {
    const $ = (id) => document.getElementById(id);
    const fmtTime = (v) => {
        if (!v) return '';
        const [h, m] = v.split(':').map(Number);
        return ((h % 12) || 12) + ':' + String(m).padStart(2, '0') + (h < 12 ? ' AM' : ' PM');
    };
    function setText(el, text, empty) { el.textContent = text || empty; el.classList.toggle('is-empty', !text); }
    function render() {
        const d = form.querySelector('input[name="date_activity"]').value;
        const endInput = form.querySelector('input[name="date_end"]');
        const e = endInput && !endInput.disabled ? endInput.value : '';
        const fmt = (k, o) => new Date(k + 'T00:00').toLocaleDateString(undefined, o);
        $('sum-date').textContent = !d ? 'Pick a date'
            : (e && e > d ? fmt(d, { month: 'short', day: 'numeric' }) + ' → ' + fmt(e, { month: 'short', day: 'numeric', year: 'numeric' })
                          : fmt(d, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' }));
        const s = fmtTime(form.querySelector('input[name="start_time"]').value), t = fmtTime(form.querySelector('input[name="end_time"]').value);
        $('sum-time').textContent = s || t ? (s || '…') + ' – ' + (t || '…') : 'Start – end time';
        const venues = selectedVenues();
        setText($('sum-venue'), venues.map(venue => venue.dataset.name).join(', '), 'None selected');
        const purpose = $('purpose').value.trim();
        setText($('sum-purpose'), purpose.length > 90 ? purpose.slice(0, 90) + '…' : purpose, '—');
        $('sum-total').textContent = $('grandTotalCell').textContent;
    }
    // Run after the fee calculator's own change handlers
    form.addEventListener('input', () => setTimeout(render));
    form.addEventListener('change', () => setTimeout(render));
    render();
})();

// Booking: calendar date picker + venue availability board (same as the College / Org forms)
(function () {
    // ---- Date picker: one day, or a start → end range for multi-day events ----
    (function () {
        const input = document.getElementById('date_activity');
        const endInput = document.getElementById('date_end');
        const grid = document.getElementById('dp-grid');
        if (!input || !grid) return;
        const minDate = input.min;                      // today + 30 days (server rule)
        const MAX_DAYS = 14;                            // server rule for date_end
        const todayKey = new Date().toLocaleDateString('en-CA');
        const calendarUrl = @json(route('calendar.data'));
        const cache = {};                               // 'YYYY-MM' -> { counts: {date: n}, items: {date: [{time, facility}]} }
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const pad = (n) => String(n).padStart(2, '0');
        const keyOf = (y, m, d) => y + '-' + pad(m + 1) + '-' + pad(d);
        const addDays = (key, n) => { const d = new Date(key + 'T00:00'); d.setDate(d.getDate() + n); return d.toLocaleDateString('en-CA'); };
        const daysBetween = (a, b) => Math.round((new Date(b + 'T00:00') - new Date(a + 'T00:00')) / 864e5);
        const fmt = (key, opts) => new Date(key + 'T00:00').toLocaleDateString(undefined, opts || { weekday: 'short', month: 'short', day: 'numeric' });
        const esc = (t) => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

        // Whole day = 6+ hours; anything shorter is a half day (morning if it ends by 1 PM, afternoon if it starts at noon or later)
        const mins = (hm) => { if (!hm) return null; const [h, m] = hm.split(':').map(Number); return h * 60 + m; };
        const clock12 = (hm) => { const [h, m] = hm.split(':').map(Number); return ((h % 12) || 12) + ':' + pad(m) + (h < 12 ? ' AM' : ' PM'); };
        function describe(it) {
            const a = mins(it.start), b = mins(it.end);
            if (a !== null && b !== null && b > a) {
                const len = b - a, hrs = Math.round(len / 30) / 2;
                it.hours = hrs + (hrs === 1 ? ' hr' : ' hrs');
                it.until = clock12(it.end);
                it.whole = len >= 360;
                it.span = it.whole ? 'Whole day' : 'Half day' + (b <= 780 ? ' · Morning' : (a >= 720 ? ' · Afternoon' : ''));
            }
            return it;
        }
        const clockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
        // One booking: time + Booked, facility (panel only), event, who, whole/half day + until when
        const personIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>';
        window.dpBookingHtml = function (it, opts) {
            opts = opts || {};
            let h = '<span class="bk">'
                + '<span class="bk-row"><span class="bk-time">' + clockIcon + (opts.date ? '<b>' + esc(opts.date) + '</b>&nbsp;·&nbsp;' : '') + esc(it.time || 'Time not set') + '</span>'
                + '<span class="bk-tag is-booked">Booked</span></span>';
            if (opts.facility) h += '<span class="bk-facility">' + esc(it.facility) + '</span>';
            if (it.event) h += '<span class="bk-event">' + esc(it.event) + '</span>';
            if (it.who) h += '<span class="bk-who">' + personIcon + esc(it.who) + '</span>';
            if (it.span || it.until) {
                h += '<span class="bk-row bk-foot">'
                    + (it.span ? '<span class="bk-tag ' + (it.whole ? 'is-whole' : 'is-half') + '">' + esc(it.span) + '</span>' : '<span></span>')
                    + (it.until ? '<span class="bk-until">Until <strong>' + esc(it.until) + '</strong> · ' + esc(it.hours) + '</span>' : '')
                    + '</span>';
            }
            return h + '</span>';
        };

        let mode = endInput.value ? 'multi' : 'single';
        let view = (input.value || minDate).slice(0, 7).split('-').map(Number);
        view = { y: view[0], m: view[1] - 1 };
        let hoverKey = '';

        const startKey = () => input.value;
        const endKey = () => (mode === 'multi' && endInput.value) ? endInput.value : '';
        const rangeDays = () => {
            const s = startKey(); if (!s) return [];
            const e = endKey() || s; const out = [];
            for (let k = s; k <= e && out.length < MAX_DAYS; k = addDays(k, 1)) out.push(k);
            return out;
        };

        function setValues(start, end) {
            input.value = start || '';
            endInput.value = end || '';
            [input, endInput].forEach(el => { el.dispatchEvent(new Event('input', { bubbles: true })); });
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Booked days come from the existing guest calendar page (same data the Reservation calendar shows)
        function loadMonth(y, m) {
            const ym = y + '-' + pad(m + 1);
            if (cache[ym]) return Promise.resolve(cache[ym]);
            return window.loadReservationCalendarMonth(calendarUrl, ym)
                .then(data => {
                    Object.keys(data.items).forEach(date => {
                        data.items[date] = data.items[date].map(describe);
                    });
                    return (cache[ym] = data);
                });
        }
        const loadKey = (key) => { const [yy, mm] = key.split('-').map(Number); return loadMonth(yy, mm - 1).catch(() => null); };

        function setMode(next) {
            mode = next;
            document.querySelectorAll('.dp-mode button').forEach(b => b.setAttribute('aria-pressed', b.dataset.mode === mode ? 'true' : 'false'));
            document.getElementById('dp-dates').classList.toggle('is-single', mode === 'single');
            endInput.disabled = mode === 'single';
            endInput.required = mode === 'multi';
            document.getElementById('dp-start-label').innerHTML = (mode === 'multi' ? 'Start date' : 'Date of Activity') + ' <span class="pk-req">*</span>';
            document.getElementById('dp-help').textContent = mode === 'multi'
                ? 'Click the first and last day (up to ' + MAX_DAYS + ' days). The times apply to every day.'
                : 'Pick a day on the calendar or type it. Requests need at least 30 days\' notice.';
            grid.setAttribute('aria-label', mode === 'multi' ? 'Choose the first and last day of the event' : 'Choose the date of activity');
            if (mode === 'single') setValues(startKey(), '');
            else { paintGrid(); announce(); }
        }

        function render() {
            const { y, m } = view;
            document.getElementById('dp-month').textContent = monthNames[m] + ' ' + y;
            document.getElementById('dp-prev').disabled = (y + '-' + pad(m + 1)) <= minDate.slice(0, 7);
            grid.classList.add('is-loading');
            document.getElementById('dp-status').textContent = 'Loading booked dates…';
            loadMonth(y, m).then(data => draw(data), () => {
                draw({ counts: {}, items: {} });
                document.getElementById('dp-status').textContent = "Couldn't load booked dates. You can still pick a date.";
            });
        }

        function draw(data) {
            const { y, m } = view;
            const first = new Date(y, m, 1).getDay();
            const days = new Date(y, m + 1, 0).getDate();
            grid.innerHTML = '';
            for (let i = 0; i < first; i++) {
                const blank = document.createElement('span'); blank.className = 'dp-day is-out'; grid.append(blank);
            }
            for (let d = 1; d <= days; d++) {
                const key = keyOf(y, m, d);
                const count = data.counts[key] || 0;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dp-day' + (count ? ' has-events' : '') + (key === todayKey ? ' is-today' : '');
                btn.dataset.date = key;
                btn.dataset.count = count;
                btn.disabled = key < minDate;
                btn.innerHTML = d + (count ? '<span class="dp-count">' + count + '</span>' : '');
                if (count) btn.title = (data.items[key] || []).map(it => it.facility + ' · ' + it.time + (it.span ? ' (' + it.span + ')' : '') + (it.event ? ' — ' + it.event : '')).join('\n') || count + ' reservation(s)';
                btn.addEventListener('click', () => pick(key));
                btn.addEventListener('mouseenter', () => { if (mode === 'multi' && startKey() && !endKey()) { hoverKey = key; paintGrid(); } });
                grid.append(btn);
            }
            grid.onmouseleave = () => { if (hoverKey) { hoverKey = ''; paintGrid(); } };
            grid.classList.remove('is-loading');
            const booked = Object.keys(data.counts).length;
            document.getElementById('dp-status').textContent = booked ? booked + ' day' + (booked > 1 ? 's' : '') + ' this month already have reservations.' : 'No reservations yet this month.';
            paintGrid();
            announce();
        }

        // Selected / in-range / hover-preview styling + accessible labels
        function paintGrid() {
            const s = startKey(), e = endKey();
            const previewEnd = (mode === 'multi' && s && !e && hoverKey > s && daysBetween(s, hoverKey) < MAX_DAYS) ? hoverKey : '';
            grid.querySelectorAll('.dp-day[data-date]').forEach(btn => {
                const k = btn.dataset.date, count = +btn.dataset.count;
                const isEdge = k === s || (e && k === e);
                const inRange = e && k > s && k < e;
                const preview = previewEnd && k > s && k <= previewEnd;
                btn.classList.toggle('is-selected', !!isEdge);
                btn.classList.toggle('is-inrange', !!inRange);
                btn.classList.toggle('is-preview', !!preview && !isEdge);
                btn.setAttribute('aria-pressed', (isEdge || inRange) ? 'true' : 'false');
                btn.setAttribute('aria-label', fmt(k, { weekday: 'long', month: 'long', day: 'numeric' })
                    + (count ? ', ' + count + ' reservation' + (count > 1 ? 's' : '') : ', no reservations')
                    + (btn.disabled ? ', not available' : '') + (k === s && e ? ', first day' : '') + (k === e ? ', last day' : ''));
            });
        }

        function pick(key) {
            const s = startKey(), e = endKey();
            const status = document.getElementById('dp-status');
            if (mode === 'single') return setValues(key, '');
            if (!s || e || key < s) return setValues(key, '');          // start a new range
            if (key === s) return setValues(key, '');                    // same day twice = one day
            if (daysBetween(s, key) >= MAX_DAYS) {                       // too long
                status.textContent = 'A multi-day event can last at most ' + MAX_DAYS + ' days — pick an earlier last day.';
                return;
            }
            hoverKey = '';
            setValues(s, key);
        }

        // Tell the "On these days" panel, venue board and summary which days are chosen
        function announce() {
            const days = rangeDays();
            const span = document.getElementById('dp-span');
            if (mode === 'multi' && days.length > 1) {
                span.hidden = false;
                span.textContent = fmt(days[0]) + ' → ' + fmt(days[days.length - 1]) + ' · ' + days.length + ' days';
            } else if (mode === 'multi' && startKey()) {
                span.hidden = false;
                span.textContent = 'Start: ' + fmt(startKey()) + ' — now click the last day';
            } else {
                span.hidden = true;
            }
            Promise.all([...new Set(days.map(k => k.slice(0, 7)))].map(ym => loadKey(ym + '-01'))).then(() => {
                const perDay = days.map(k => ({ date: k, items: (cache[k.slice(0, 7)] || { items: {} }).items[k] || [], count: (cache[k.slice(0, 7)] || { counts: {} }).counts[k] || 0 }));
                showDays(perDay);
                document.dispatchEvent(new CustomEvent('dp:range', { detail: { start: days[0] || '', end: days[days.length - 1] || '', days: perDay } }));
            });
        }

        // "On this day" / "On these days": what's already booked, flagging the venue you picked
        function showDays(perDay) {
            const box = document.getElementById('dp-dayinfo');
            if (!perDay.length) { box.innerHTML = '<h3>On this day</h3><p>Pick a date to see what\'s already booked.</p>'; return; }
            const venues = Array.from(document.querySelectorAll('input[name="facility_ids[]"]:checked')).map(input => input.dataset.name);
            const multi = perDay.length > 1;
            let html = '<h3>' + (multi ? 'On these days' : fmt(perDay[0].date, { weekday: 'short', month: 'long', day: 'numeric' })) + '</h3>';
            const busy = perDay.filter(d => d.count || d.items.length);
            if (!busy.length) {
                html += '<p class="dp-free"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>No reservations yet — ' + (multi ? 'all ' + perDay.length + ' days are open.' : 'this date is open.') + '</p>';
            } else {
                let clashDays = 0;
                busy.forEach(d => {
                    if (multi) html += '<span class="dp-date-head">' + esc(fmt(d.date)) + '</span>';
                    const list = d.items.length ? d.items : [{ time: '', facility: d.count + ' reservation(s)' }];
                    html += '<ul>' + list.map(it => {
                        const bookedFacilities = it.facility.split(',').map(name => name.trim().toUpperCase());
                        const clash = venues.some(venue => bookedFacilities.includes(venue.toUpperCase()));
                        if (clash) clashDays++;
                        return '<li class="bk-item' + (clash ? ' is-clash' : '') + '">' + window.dpBookingHtml(it, { facility: true }) + '</li>';
                    }).join('') + '</ul>';
                });
                if (multi) html += '<p class="u-mt-8">' + (perDay.length - busy.length) + ' of ' + perDay.length + ' days have no reservations.</p>';
                if (clashDays) html += '<p class="dp-warn">A selected facility has an existing reservation in this schedule. GSU will review availability before approval.</p>';
            }
            box.innerHTML = html;
        }

        document.querySelectorAll('.dp-mode button').forEach(b => b.addEventListener('click', () => { if (b.dataset.mode !== mode) setMode(b.dataset.mode); }));
        document.getElementById('dp-prev').addEventListener('click', () => { view.m--; if (view.m < 0) { view.m = 11; view.y--; } render(); });
        document.getElementById('dp-next').addEventListener('click', () => { view.m++; if (view.m > 11) { view.m = 0; view.y++; } render(); });
        // Typing a date moves the calendar to it
        function onTyped() {
            if (endInput.value && input.value && endInput.value < input.value) endInput.value = '';
            const key = input.value;
            if (key) {
                const [yy, mm] = key.split('-').map(Number);
                if (yy !== view.y || mm - 1 !== view.m) { view = { y: yy, m: mm - 1 }; render(); return; }
            }
            paintGrid(); announce();
        }
        input.addEventListener('change', onTyped);
        endInput.addEventListener('change', onTyped);
        document.querySelectorAll('input[name="facility_ids[]"]').forEach(input => input.addEventListener('change', announce));

        // Restore "Several days" after a validation error that kept old('date_end')
        if (mode === 'multi') setMode('multi');
        render();
    })();

    // ---- Venue availability board (display only) ----
    (function () {
        const cards = Array.from(document.querySelectorAll('.vb-card'));
        if (!cards.length) return;
        const esc = (t) => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
        const clock = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
        const shortDate = (k) => new Date(k + 'T00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        let current = { start: '', end: '', days: [] };

        // "8:00 AM – 12:00 PM" -> minutes since midnight
        const toMin = (t) => { const m = /(\d{1,2}):(\d{2})\s*(AM|PM)/i.exec(t || ''); if (!m) return null; let h = +m[1] % 12; if (/pm/i.test(m[3])) h += 12; return h * 60 + +m[2]; };
        const rangeOf = (txt) => { const parts = String(txt).split(/[–-]/); return [toMin(parts[0]), toMin(parts[1])]; };
        const hhmm = (v) => { if (!v) return null; const [h, m] = v.split(':').map(Number); return h * 60 + m; };

        function paint() {
            const start = hhmm(document.getElementById('start_time').value);
            const end = hhmm(document.getElementById('end_time').value);
            const text = document.getElementById('vb-day-text');
            const pill = document.getElementById('vb-day');
            const multi = current.days.length > 1;
            if (current.start) {
                text.textContent = multi
                    ? 'Availability ' + shortDate(current.start) + ' → ' + shortDate(current.end) + ', ' + current.end.slice(0, 4) + ' (' + current.days.length + ' days)'
                    : 'Availability on ' + new Date(current.start + 'T00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
                pill.classList.add('is-set');
            } else {
                text.textContent = 'Pick a date in step 1 to see booked times';
                pill.classList.remove('is-set');
            }
            cards.forEach(card => {
                const name = card.dataset.name.toUpperCase();
                const input = card.querySelector('input');
                const slotsBox = card.querySelector('.vb-slots');
                const status = card.querySelector('.vb-status');
                // A booking row can list several facilities ("A, B"), so match on each name
                const mine = [];
                current.days.forEach(d => d.items.forEach(it => {
                    if (it.facility.split(',').map(x => x.trim().toUpperCase()).includes(name)) mine.push(Object.assign({ date: d.date }, it));
                }));
                const bookedDays = new Set(mine.map(x => x.date)).size;
                card.classList.toggle('is-booked', mine.length > 0);
                card.querySelector('.vb-count-num').textContent = current.start ? mine.length : '–';
                if (input.disabled) { card.classList.remove('is-clash'); return; }
                if (!current.start) {
                    slotsBox.innerHTML = '<span class="vb-idle">Pick a date to see times</span>';
                } else if (!mine.length) {
                    slotsBox.innerHTML = '<span class="vb-free">' + (multi ? 'Free all ' + current.days.length + ' days' : 'Free all day') + '</span>';
                } else {
                    slotsBox.innerHTML = mine.map(it => window.dpBookingHtml(it, { date: multi ? shortDate(it.date) : '' })).join('');
                }
                const label = !current.start || !mine.length ? 'Available'
                    : (multi ? 'Booked ' + bookedDays + ' of ' + current.days.length + ' days' : (mine.some(it => it.whole) ? 'Booked whole day' : 'Partly booked'));
                status.textContent = label;
                const fullyTaken = !multi && mine.some(it => it.whole);
                status.className = 'pk-pill vb-status ' + (!current.start || !mine.length ? 'is-good' : (fullyTaken ? 'is-bad' : 'is-warn'));
                // Flag venues whose bookings overlap the start/end time on any chosen day
                const clash = start !== null && end !== null && mine.some(it => { const [a, b] = rangeOf(it.time); return a !== null && b !== null && a < end && b > start; });
                card.classList.toggle('is-clash', clash);
            });
        }
        document.addEventListener('dp:range', (e) => { current = { start: e.detail.start || '', end: e.detail.end || '', days: e.detail.days || [] }; paint(); });
        ['start_time', 'end_time'].forEach(id => document.getElementById(id).addEventListener('input', paint));
        paint();
    })();

})();
</script>
@include('partials.facility-time-availability')
@include('partials.phone-number-mask', ['inputId' => 'contact_number'])
@endsection
