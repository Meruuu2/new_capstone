@extends('layouts.org')

@section('org-content')
@include('layouts.partials.page-kit')
@php
    $minActivityDate = now()->addDays(30)->toDateString(); // same rule as StoreFacilitiesUtilizationRequest
    $equipmentFields = [
        'qty_monobloc' => 'Monobloc Chair',
        'qty_table'    => 'Table',
        'qty_fan'      => 'Electric Fan',
        'qty_rostrum'  => 'Rostrum',
        'qty_flag'     => 'Flag & School Color',
        'qty_sound'    => 'Sound',
        'qty_led'      => 'LED Wall',
    ];
    // Line icons for the equipment tiles
    $equipmentIcons = [
        'qty_monobloc' => '<path d="M7 3h10v8H7z"/><path d="M5 11h14M7 11v10M17 11v10M7 16h10"/>',
        'qty_table'    => '<path d="M3 8h18M5 8v12M19 8v12M3 8l2-3h14l2 3"/>',
        'qty_fan'      => '<circle cx="12" cy="10" r="1.6"/><path d="M12 8.4c0-3 1-5 3-5s2 3-1.4 5.2M13.4 11c2.6 1.5 3.9 3.3 2.9 5s-3.4-.3-4-3.7M10.6 10.9c-2.6 1.5-4.8 1.6-5.8-.1s2-2.8 5.2-1.6"/><path d="M12 12v8M8 21h8"/>',
        'qty_rostrum'  => '<path d="M6 8h12l-1.5 13h-9z"/><path d="M4 5h16v3H4zM12 5V3"/>',
        'qty_flag'     => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'qty_sound'    => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="14" r="3"/><circle cx="12" cy="7.5" r="1"/>',
        'qty_led'      => '<rect x="2" y="4" width="20" height="13" rx="1.5"/><path d="M8 21h8M12 17v4M6 8h4M6 11h7"/>',
    ];
    // "Noted by" (required): a college Dean or Program Head, or someone else with their position
    $notedOld = (string) old('noted_signatory_id', '');
    $eventTypes = ['General assembly', 'Seminar', 'Meeting', 'Workshop', 'Sports event', 'Program', 'Outreach'];
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M12 12v6M9 15h6"/></svg>
                New request
            </p>
            <h1>Facilities Utilization Request</h1>
            <p class="pk-hero-sub">Submit a request for facility and equipment usage. Date of request will be recorded automatically after submission.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('org.requests.facilities.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg>
                My requests
            </a>
        </div>
    </section>

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
        <form method="POST" action="{{ route('org.requests.facilities.store') }}" class="pk-form-card" id="request-form" enctype="multipart/form-data">
            @csrf

            {{-- Date & Time --}}
            <div class="pk-step">
                <span class="pk-step-num">01</span>
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
                <span class="pk-step-num">02</span>
                <div>
                    <h2>Venues <span class="pk-req u-text-danger">*</span></h2>
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
                            @endphp
                            <div class="vb-card" data-name="{{ $facility->name }}">
                                <input type="checkbox" name="facility_ids[]" id="venue-{{ $facility->id }}" value="{{ $facility->id }}"
                                       data-name="{{ $facility->name }}"
                                       {{ in_array($facility->id, $selectedFacilityIds, true) ? 'checked' : '' }}
                                       {{ $isUnavailable ? 'disabled' : '' }}>
                                <label for="venue-{{ $facility->id }}">
                                    <span class="vb-top">
                                        <span class="u-minw-0">
                                            <span class="vb-name">{{ $facility->name }}</span>
                                            <span class="vb-owner">{{ $owner }}</span>
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
                </div>
            </div>

            {{-- Purpose + Noted by --}}
            <div class="pk-step">
                <span class="pk-step-num">03</span>
                <div>
                    <h2>Purpose &amp; signatory</h2>
                    <p class="pk-step-intro">Tell GSU what the event is, then choose the Dean or Program Head who will note your request.</p>

                    <div class="pp-block">
                        <p class="pp-title"><span class="pp-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></span><label class="u-m-0" for="purpose">What's the event? <span class="pk-req u-text-danger">*</span></label></p>
                        <p class="pp-sub">Tap a type to start, then add the details.</p>
                        <div class="pp-chips" role="group" aria-label="Event type">
                            @foreach ($eventTypes as $type)
                                <button type="button" class="pp-chip" data-type="{{ $type }}" aria-pressed="false">{{ $type }}</button>
                            @endforeach
                        </div>
                        <div class="pp-text">
                            <textarea name="purpose" id="purpose" rows="3" required maxlength="500" class="pk-input" placeholder="e.g., General assembly — Orientation for new members" aria-describedby="pp-count">{{ old('purpose') }}</textarea>
                            <div class="pp-meter" id="pp-meter" aria-hidden="true">
                                <span class="pp-meter-bar"><span id="pp-meter-fill"></span></span>
                                <span class="pp-meter-num" id="pp-count">0 / 500</span>
                            </div>
                        </div>
                    </div>

                    <div class="pp-block">
                        <p class="pp-title"><span class="pp-title-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>Who will note it? <span class="pk-req u-text-danger">*</span></p>
                        <p class="pp-sub">Organization requests must be noted by a college <strong>Dean</strong> or <strong>Program Head</strong>, even if you're the Governor or another officer. Pick one and type their name.</p>

                        @error('noted_signatory_id')<p class="pk-error sg-error">{{ $message }}</p>@enderror
                        @error('noted_signatory_name')<p class="pk-error sg-error">{{ $message }}</p>@enderror
                        @error('noted_signatory_position')<p class="pk-error sg-error">{{ $message }}</p>@enderror

                        @php
                            $notedCards = [
                                'dean'         => ['Dean', 'DN', 'Name of the Dean'],
                                'program_head' => ['Program Head', 'PH', 'Name of the Program Head'],
                            ];
                        @endphp
                        <div class="sg-grid sg-grid--noted" role="radiogroup" aria-label="Noted by" aria-required="true">
                            @foreach ($notedCards as $value => [$title, $initials, $placeholder])
                                <div class="sg-card">
                                    <input type="radio" name="noted_signatory_id" id="sg-{{ $value }}" value="{{ $value }}" required {{ $notedOld === $value ? 'checked' : '' }}>
                                    <label for="sg-{{ $value }}">
                                        <span class="sg-avatar" aria-hidden="true">{{ $initials }}</span>
                                        <span class="u-minw-0"><span class="sg-name">{{ $title }}</span><span class="sg-role">College {{ strtolower($title) }}</span></span>
                                        <span class="sg-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                                        <span class="sg-type">
                                            <input type="text" name="noted_signatory_name" class="sg-type-input" data-noted-for="{{ $value }}" maxlength="255" autocomplete="name"
                                                   placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
                                                   value="{{ $notedOld === $value ? old('noted_signatory_name') : '' }}" {{ $notedOld === $value ? 'required' : 'disabled' }}>
                                        </span>
                                    </label>
                                </div>
                            @endforeach

                            <div class="sg-card">
                                <input type="radio" name="noted_signatory_id" id="sg-custom" value="custom" required {{ $notedOld === 'custom' ? 'checked' : '' }}>
                                <label for="sg-custom">
                                    <span class="sg-avatar" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></span>
                                    <span class="u-minw-0"><span class="sg-name">Someone else</span><span class="sg-role">Type position &amp; name</span></span>
                                    <span class="sg-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                                    <span class="sg-type sg-type--pair">
                                        <input type="text" name="noted_signatory_position" class="sg-type-input" data-noted-for="custom" maxlength="120" autocomplete="organization-title"
                                               placeholder="Position, e.g. OIC Dean" aria-label="Their position"
                                               value="{{ $notedOld === 'custom' ? old('noted_signatory_position') : '' }}" {{ $notedOld === 'custom' ? 'required' : 'disabled' }}>
                                        <input type="text" name="noted_signatory_name" class="sg-type-input" data-noted-for="custom" maxlength="255" autocomplete="name"
                                               placeholder="Their name" aria-label="Their name"
                                               value="{{ $notedOld === 'custom' ? old('noted_signatory_name') : '' }}" {{ $notedOld === 'custom' ? 'required' : 'disabled' }}>
                                    </span>
                                </label>
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

            {{-- Attachments (optional) --}}
            <div class="pk-step">
                <span class="pk-step-num">05</span>
                <div>
                    <h2>Documents <span class="u-optional">(optional)</span></h2>
                    <p class="pk-step-intro">Supporting files for your request.</p>
                    <div class="nreq-uploads is-single">
                        <div>
                            <label class="pk-drop" title="Select up to 10 JPG, PNG, or PDF files, max 10MB each">
                                <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf" data-empty="JPG, PNG, or PDF, max 10 files, 10MB each">
                                <span class="pk-drop-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.4 11.1l-9.2 9.2a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg></span>
                                <span><strong>Attachments</strong><span class="pk-drop-sub">JPG, PNG, or PDF; max 10 files, 10MB each</span></span>
                            </label>
                            <p class="nreq-note">Upload supporting documents.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pk-form-actions">
                <a href="{{ url()->previous() }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" id="submitBtn" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    <span>Submit Request</span>
                </button>
            </div>
        </form>

        <aside class="pk-aside" aria-label="Request summary">
            <p class="pk-aside-label">Request summary</p>
            <div class="pk-ticket">
                <div class="pk-ticket-top">
                    <p class="pk-ticket-date" id="sum-date">Pick a date</p>
                    <p class="pk-ticket-time" id="sum-time">Start – end time</p>
                </div>
                <div class="pk-ticket-cut" aria-hidden="true"></div>
                <div class="pk-ticket-body">
                    <div class="pk-ticket-row"><span>Venue</span><strong id="sum-venue" class="is-empty">None selected</strong></div>
                    <div class="pk-ticket-row"><span>Purpose</span><strong id="sum-purpose" class="is-empty">—</strong></div>
                    <div class="pk-ticket-row"><span>Equipment</span><strong id="sum-equip" class="is-empty">None</strong></div>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
function adjustQty(fieldId, delta) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    let value = parseInt(input.value || '0', 10);
    value += delta;
    if (value < 0) value = 0;
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

// Prevent double submission
(function() {
    // Target the request form by id (the sidebar's logout form comes first in the page)
    const form = document.getElementById('request-form');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        const label = submitBtn.querySelector('span');
        form.addEventListener('submit', function(e) {
            // Disable button and show loading state
            submitBtn.disabled = true;
            label.textContent = 'Submitting...';
            submitBtn.style.opacity = '.6';
            submitBtn.style.cursor = 'not-allowed';

            // Re-enable after 5 seconds as fallback (in case of validation errors)
            setTimeout(function() {
                submitBtn.disabled = false;
                label.textContent = 'Submit Request';
                submitBtn.style.opacity = '';
                submitBtn.style.cursor = '';
            }, 5000);
        });
    }

    const notedRadios = document.querySelectorAll('input[name="noted_signatory_id"]');

    // Only the picked card's boxes are sent (and required); focus its first box
    function updateNotedUI(focus) {
        const picked = document.querySelector('input[name="noted_signatory_id"]:checked');
        document.querySelectorAll('[data-noted-for]').forEach(box => {
            const on = !!picked && box.dataset.notedFor === picked.value;
            box.disabled = !on;
            box.required = on;
        });
        const first = picked && picked.closest('.sg-card').querySelector('.sg-type-input');
        if (focus && first) first.focus({ preventScroll: true });
    }
    notedRadios.forEach(r => r.addEventListener('change', () => updateNotedUI(true)));
    updateNotedUI(false);

    // Purpose: event-type chips + character meter (server limit is 500)
    (function () {
        const area = document.getElementById('purpose');
        const chips = Array.from(document.querySelectorAll('.pp-chip'));
        const types = chips.map(c => c.dataset.type);
        const sep = ' — ';
        function currentType() { const t = types.find(t => area.value.startsWith(t + sep) || area.value === t); return t || ''; }
        function sync() {
            const len = area.value.length;
            document.getElementById('pp-count').textContent = len + ' / 500';
            document.getElementById('pp-meter-fill').style.width = Math.min(100, len / 5) + '%';
            document.getElementById('pp-meter').classList.toggle('is-near', len >= 450);
            const t = currentType();
            chips.forEach(c => c.setAttribute('aria-pressed', c.dataset.type === t ? 'true' : 'false'));
        }
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

    // Equipment: highlight tiles in use, running total, quick-start sets
    (function () {
        const tiles = Array.from(document.querySelectorAll('.eq-tile'));
        if (!tiles.length) return;
        function sync() {
            let pieces = 0, kinds = 0;
            tiles.forEach(tile => {
                const input = tile.querySelector('.eq-count');
                let v = parseInt(input.value || '0', 10);
                if (isNaN(v) || v < 0) { v = 0; }
                tile.classList.toggle('is-on', v > 0);
                pieces += v; if (v > 0) kinds++;
            });
            document.getElementById('eq-total').textContent = pieces;
            document.getElementById('eq-total-text').textContent = pieces
                ? (pieces === 1 ? 'item' : 'items') + ' · ' + kinds + ' ' + (kinds === 1 ? 'type' : 'types')
                : 'items selected';
        }
        tiles.forEach(t => t.querySelector('.eq-count').addEventListener('input', sync));
        document.querySelectorAll('.eq-quick [data-preset]').forEach(btn => btn.addEventListener('click', () => {
            const preset = JSON.parse(btn.dataset.preset || '{}');
            tiles.forEach(tile => {
                const input = tile.querySelector('.eq-count');
                input.value = preset[tile.dataset.field] || 0;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }));
        sync();
    })();

    // Show chosen file names inside the drop zones
    document.querySelectorAll('.pk-drop input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const sub = input.closest('.pk-drop').querySelector('.pk-drop-sub');
            const names = Array.from(input.files).map(f => f.name);
            sub.textContent = names.length ? names.join(', ') : input.dataset.empty;
        });
    });

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

        // Booked days come from the existing org calendar page (same data the Reservation calendar shows)
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

    // Live summary (display only)
    if (!form) return;
    const $ = (id) => document.getElementById(id);
    const fmtTime = (v) => {
        if (!v) return '';
        const [h, m] = v.split(':').map(Number);
        return ((h % 12) || 12) + ':' + String(m).padStart(2, '0') + (h < 12 ? ' AM' : ' PM');
    };
    function setText(el, text, empty) { el.textContent = text || empty; el.classList.toggle('is-empty', !text); }
    function render() {
        const d = $('date_activity').value;
        const de = $('date_end').disabled ? '' : $('date_end').value;
        const long = (k) => new Date(k + 'T00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
        const short = (k) => new Date(k + 'T00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        const n = d && de ? Math.round((new Date(de + 'T00:00') - new Date(d + 'T00:00')) / 864e5) + 1 : 1;
        $('sum-date').textContent = !d ? 'Pick a date' : (de && n > 1 ? short(d) + ' → ' + short(de) + ' (' + n + ' days)' : long(d));
        const s = fmtTime($('start_time').value), e = fmtTime($('end_time').value);
        $('sum-time').textContent = s || e ? (s || '…') + ' – ' + (e || '…') : 'Start – end time';
        const venues = Array.from(form.querySelectorAll('input[name="facility_ids[]"]:checked')).map(input => input.dataset.name);
        setText($('sum-venue'), venues.join(', '), 'None selected');
        const purpose = $('purpose').value.trim();
        setText($('sum-purpose'), purpose.length > 90 ? purpose.slice(0, 90) + '…' : purpose, '—');
        const equip = Array.from(form.querySelectorAll('.eq-count'))
            .filter(i => parseInt(i.value || '0', 10) > 0)
            .map(i => i.value + ' × ' + i.dataset.label);
        setText($('sum-equip'), equip.join(', '), 'None');
    }
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    const venueBoxes = Array.from(form.querySelectorAll('input[name="facility_ids[]"]'));
    const selectionError = document.getElementById('facility-selection-error');
    venueBoxes.forEach(input => input.addEventListener('change', () => {
        if (venueBoxes.some(box => box.checked)) selectionError.hidden = true;
    }));
    form.addEventListener('submit', event => {
        if (venueBoxes.some(input => input.checked)) return;
        event.preventDefault();
        selectionError.hidden = false;
        selectionError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    render();
})();
</script>
@include('partials.facility-time-availability')
@endsection
