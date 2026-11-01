@extends('layouts.college')

@section('college-content')
@include('layouts.partials.page-kit')
@php
    $sender = auth()->user();
    $senderUnit = $sender->college_name ?: 'College account';
    $senderInitials = collect(preg_split('/\s+/', trim((string) $sender->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/></svg>
                Repair &amp; maintenance
            </p>
            <h1>New repair request</h1>
            <p class="pk-hero-sub">Send a message to the facility administrators.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('college.maintenance-tickets.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/></svg>
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

    <div class="mtc">
        <form method="POST" action="{{ route('college.maintenance-tickets.store') }}" class="mtc-mail" id="ticket-form">
            @csrf
            <div class="mtc-row">
                <span class="mtc-row-label">From</span>
                <span class="mtc-who"><span class="pk-avatar" aria-hidden="true">{{ $senderInitials ?: '?' }}</span><span><strong>{{ $sender->name }}</strong> ({{ $senderUnit }})</span></span>
            </div>
            <div class="mtc-row">
                <span class="mtc-row-label">To</span>
                <span><span class="mtc-to"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>Facility administrators</span></span>
            </div>
            @php $isOther = old('facility_id') === 'other'; @endphp
            <div class="mtc-row">
                <label for="facility_id" class="mtc-row-label">Facility</label>
                <select id="facility_id" name="facility_id" required class="pk-input">
                    <option value="">Choose a facility</option>
                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected(!$isOther && old('facility_id') == $facility->id)>
                            {{ $facility->name }}{{ $facility->location ? ' - ' . $facility->location : '' }}
                        </option>
                    @endforeach
                    <option value="other" @selected($isOther)>Others (not on the list)</option>
                </select>
            </div>
            {{-- "Others": the typed place is saved on the ticket as its facility --}}
            <div class="mtc-row mtc-other" id="other-row" @if (!$isOther) hidden @endif>
                <label for="facility_other" class="mtc-row-label">Others</label>
                <div class="u-minw-0">
                    <input id="facility_other" name="facility_other" type="text" maxlength="160" class="pk-input"
                           value="{{ old('facility_other') }}" placeholder="Type the place, e.g. CCIS Faculty Room"
                           aria-describedby="other-help" @if ($isOther) required @else disabled @endif>
                    <p class="mtc-other-help" id="other-help">Not on the list? Type the room, building or spot that needs repair.</p>
                </div>
            </div>
            <div class="mtc-row">
                <label for="subject" class="mtc-row-label">Subject</label>
                <input id="subject" name="subject" type="text" maxlength="255" required class="pk-input mtc-subject"
                       value="{{ old('subject') }}" placeholder="Briefly describe the issue">
            </div>
            <div class="mtc-body">
                <label for="issue_description" class="pk-sr">Message</label>
                <textarea id="issue_description" name="issue_description" rows="7" maxlength="10000" required
                          class="pk-input" placeholder="Describe the repair or maintenance needed, including where and when you noticed it.">{{ old('issue_description') }}</textarea>
                <span class="mtc-count" id="msg-count" aria-live="polite">0 / 10,000</span>
            </div>
            <div class="mtc-foot">
                <a href="{{ route('college.maintenance-tickets.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" id="submitBtn" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
                    <span>Send request</span>
                </button>
            </div>
        </form>

        <aside class="mtc-aside" aria-label="What happens next">
            <div class="mtc-next">
                <h3>What happens next</h3>
                <ol>
                    <li><span><strong>Ticket created</strong>You get a tracking code right away.</span></li>
                    <li><span><strong>Admins review it</strong>Its status changes as the work moves along.</span></li>
                    <li><span><strong>You're notified</strong>Updates arrive in your notifications.</span></li>
                </ol>
            </div>
            <div class="mtc-tip"><strong>Tip:</strong> mention the room or spot, what you noticed, and when, so the right team can respond faster.</div>
        </aside>
    </div>
</div>

<script>
// Prevent double submission
(function() {
    // Target the ticket form by id (the sidebar's logout form comes first in the page)
    const form = document.getElementById('ticket-form');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        const label = submitBtn.querySelector('span');
        form.addEventListener('submit', function(e) {
            // Disable button and show loading state
            submitBtn.disabled = true;
            label.textContent = 'Sending...';
            submitBtn.style.opacity = '.6';
            submitBtn.style.cursor = 'not-allowed';

            // Re-enable after 5 seconds as fallback (in case of validation errors)
            setTimeout(function() {
                submitBtn.disabled = false;
                label.textContent = 'Send request';
                submitBtn.style.opacity = '';
                submitBtn.style.cursor = '';
            }, 5000);
        });
    }

    // "Others (not on the list)" shows the Others field; it is only sent while shown
    const facility = document.getElementById('facility_id');
    const otherRow = document.getElementById('other-row');
    const other = document.getElementById('facility_other');
    facility.addEventListener('change', function () {
        const on = facility.value === 'other';
        otherRow.hidden = !on;
        other.disabled = !on;
        other.required = on;
        if (on) other.focus();
    });

    // Character counter (display only)
    const msg = document.getElementById('issue_description');
    const count = document.getElementById('msg-count');
    const update = () => { count.textContent = msg.value.length.toLocaleString() + ' / 10,000'; };
    msg.addEventListener('input', update);
    update();
})();
</script>
@endsection
