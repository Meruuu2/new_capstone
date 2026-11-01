@extends('layouts.app')

@section('title', 'Welcome to UA Facility Management System')

@section('main-content-class', 'w-full')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&display=swap" rel="stylesheet">

@php
  // "Next on the calendar": soonest reservation from today on, from the same month data the calendar below shows
  $todayStart = now()->startOfDay();
  $upcoming = collect($days ?? [])
      ->flatMap(fn ($bookings, $date) => collect($bookings)->map(fn ($b) => ['date' => \Carbon\Carbon::parse($date), 'booking' => $b]))
      ->filter(fn ($x) => $x['date']->gte($todayStart))
      ->sortBy(fn ($x) => $x['date']->timestamp * 10000 + (int) optional($x['booking']->start_time)->format('Hi'))
      ->values();
  $nextUp = $upcoming->first();
  $nextTime = $nextUp
      ? trim(optional($nextUp['booking']->start_time)->format('g:i A') . (optional($nextUp['booking']->end_time) ? ' – ' . $nextUp['booking']->end_time->format('g:i A') : ''), ' –')
      : '';
@endphp

<div class="ua-landing ul">

{{-- HERO: the seal's crimson field, campus photos inside the seal's gold triangle --}}
<section class="ul-hero" aria-labelledby="ul-hero-title">
  <div class="ul-hero-copy">
    <h1 class="ul-hero-title" id="ul-hero-title">Reserve a venue at the <span>University of Antique</span>.</h1>
    <p class="ul-hero-lead">See what&rsquo;s already scheduled, request a hall or ground, and follow your ticket until GSU confirms it. Guests don&rsquo;t need an account.</p>
    <div class="ul-hero-actions">
      <a href="{{ route('public.guest') }}" class="ul-btn ul-btn--gold">
        Request a facility
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <a href="#calendar" class="ul-btn ul-btn--line">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
        View schedule
      </a>
      <a href="#track" class="ul-hero-link">Track a request</a>
    </div>
    <p class="ul-hero-by">
      <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="" width="36" height="36">
      <span>Facility Management System<small>General Services Unit &middot; Sibalom, Antique</small></span>
    </p>
  </div>

  <div class="ul-hero-art">
    <div class="ul-tri" aria-hidden="true">
      <span class="ul-tri-gold"></span>
      <div class="ul-tri-photo ua-hero-slides">
        <div class="ua-hero-slide is-active" style="background-image: url('{{ asset('img/facilities/busalian-hall.jpg') }}')"><span class="ul-slide-cap">Busalian Hall</span></div>
        <div class="ua-hero-slide" style="background-image: url('{{ asset('img/facilities/grand-stand.jpg') }}')"><span class="ul-slide-cap">Grandstand</span></div>
        <div class="ua-hero-slide" style="background-image: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')"><span class="ul-slide-cap">Paghiusa Hall</span></div>
      </div>
    </div>

    <div class="ul-next">
      <p class="ul-next-label"><i aria-hidden="true"></i>Next on the calendar</p>
      @if ($nextUp)
        <p class="ul-next-date">{{ $nextUp['date']->isToday() ? 'Today' : $nextUp['date']->format('D, M j') }}</p>
        <p class="ul-next-meta">{{ $nextUp['booking']->facilities->pluck('name')->join(', ') ?: 'Facility' }}@if ($nextTime) &middot; {{ $nextTime }}@endif</p>
        <a href="#calendar" class="ul-next-more">{{ $upcoming->count() === 1 ? '1 upcoming reservation' : $upcoming->count() . ' upcoming reservations' }} this month</a>
      @else
        <p class="ul-next-date">Dates are open</p>
        <p class="ul-next-meta">No upcoming reservations this month yet.</p>
        <a href="#calendar" class="ul-next-more">Open the calendar</a>
      @endif
    </div>

    <div class="ua-hero-dots ul-dots" role="tablist" aria-label="Choose photo">
      <button type="button" class="ua-hero-dot is-active" role="tab" aria-selected="true" aria-label="Busalian Hall"></button>
      <button type="button" class="ua-hero-dot" role="tab" aria-selected="false" aria-label="Grandstand"></button>
      <button type="button" class="ua-hero-dot" role="tab" aria-selected="false" aria-label="Paghiusa Hall"></button>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const slides = document.querySelectorAll('.ua-hero-slide');
  const dots = document.querySelectorAll('.ua-hero-dot');
  if (slides.length < 2) return;
  let current = 0, timer;

  function show(i) {
    slides[current].classList.remove('is-active');
    dots[current].classList.remove('is-active');
    dots[current].setAttribute('aria-selected', 'false');
    current = i;
    slides[current].classList.add('is-active');
    dots[current].classList.add('is-active');
    dots[current].setAttribute('aria-selected', 'true');
  }
  function start() { clearInterval(timer); timer = setInterval(() => show((current + 1) % slides.length), 6000); }

  dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); start(); }));
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) start();
});
</script>

{{-- WELCOME + SIGN IN --}}
<section class="ul-welcome" id="about">
  <div class="ul-welcome-copy">
    <h2>Kruhay Bambuhay!</h2>
    <p>
      Welcome to the University of Antique Facility Management System. The General Services Unit (GSU)
      is committed to providing top-tier logistical support, preventive maintenance, and administrative assistance
      across campus facilities. Our services ensure that physical spaces, venue reservations, and event systems
      run seamlessly to promote a productive academic and communal life for all internal organizational units
      and college personnel.
    </p>
    <a class="ul-text-link" href="{{ route('public.rates') }}">See venue rates
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>

  <div class="ul-doors">
    <h3 class="ul-doors-title">Staff sign in</h3>
    <div class="ua-portrait-cards ul-cards">
      <a href="{{ route('login', ['portal' => 'admin']) }}" class="ua-portrait-card">
        <div class="ua-card-top">
          <span class="ua-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg></span>
          <span class="ua-card-index" aria-hidden="true">01</span>
        </div>
        <h4>Admin Login</h4>
        <div class="ua-card-text">
          <p>System configuration &amp; general services controls</p>
        </div>
        <span class="ua-simple-btn">Log In <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>

      <a href="{{ route('login', ['portal' => 'college']) }}" class="ua-portrait-card">
        <div class="ua-card-top">
          <span class="ua-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-5 9 5-9 5-9-5z"/><path d="M7 11.2V16c0 1.1 2.2 2.5 5 2.5s5-1.4 5-2.5v-4.8"/><path d="M21 9v5"/></svg></span>
          <span class="ua-card-index" aria-hidden="true">02</span>
        </div>
        <h4>College Staff</h4>
        <div class="ua-card-text">
          <p>Departmental venue reservations &amp; access</p>
        </div>
        <span class="ua-simple-btn">Log In <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>

      <a href="{{ route('login', ['portal' => 'org']) }}" class="ua-portrait-card">
        <div class="ua-card-top">
          <span class="ua-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.9.4 5 2.8 5 5.8"/></svg></span>
          <span class="ua-card-index" aria-hidden="true">03</span>
        </div>
        <h4>Org Unit</h4>
        <div class="ua-card-text">
          <p>Student org requests &amp; event scheduling</p>
        </div>
        <span class="ua-simple-btn">Log In <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </a>
    </div>

    {{-- Guests don't sign in: the Guest Access page explains the process, then leads to the request form --}}
    <a href="{{ route('public.guest') }}" class="ul-guest">
      <span class="ul-guest-text">
        <strong>Not university staff?</strong>
        <span>Guests &amp; partner organizations can book a venue, then track it with a ticket code. No account needed.</span>
      </span>
      <span class="ul-guest-go">Start request <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </a>
  </div>
</section>

{{-- TICKET TRACKER --}}
<section class="ua-track-section" id="track" aria-labelledby="ua-track-title" style="--ua-track-image: url('{{ asset('img/facilities/tiripunan-hall.jpg') }}')">
  <div class="ua-track-inner">
    <div class="ua-track-copy">
      <h2 id="ua-track-title">Track a request or booking</h2>
      <p>No sign-in needed. Enter the tracking code and the exact last name the submitter provided for tracking.</p>
    </div>

    <form class="ua-track-form" id="ua-track-form" novalidate data-endpoint="{{ route('ticket.lookup') }}" role="search" aria-label="Request, booking, or maintenance status">
      <div class="ua-track-bar">
        <div class="ua-track-seg ua-track-seg--type" data-for="ua-track-type">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          <div class="ua-track-seg-text">
            <label for="ua-track-type">Request type</label>
            <div class="ua-track-select">
              <select id="ua-track-type" name="type">
                <option value="FM" title="Facility request">Facility (FM)</option>
                <option value="BK" title="Facility booking">Booking (BKG)</option>
                <option value="RM" title="Repair &amp; maintenance">Maintenance (RM)</option>
              </select>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
            </div>
          </div>
        </div>
        <div class="ua-track-seg" data-for="ua-track-code">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/></svg>
          <div class="ua-track-seg-text">
            <label for="ua-track-code">Tracking code</label>
            <input id="ua-track-code" name="code" type="text" inputmode="text" autocomplete="off" spellcheck="false" maxlength="48" placeholder="FM-2026-00123" aria-describedby="ua-track-code-err">
          </div>
        </div>
        <div class="ua-track-seg" data-for="ua-track-last">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>
          <div class="ua-track-seg-text">
            <label for="ua-track-last">Requester&rsquo;s last name</label>
            <input id="ua-track-last" name="last_name" type="text" autocomplete="family-name" maxlength="120" placeholder="Last name provided for tracking" aria-describedby="ua-track-last-err" required>
          </div>
        </div>
        <button type="submit" class="ua-track-submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          Check status
        </button>
      </div>
      <div class="ua-track-errors">
        <span aria-hidden="true"></span>
        <p class="ua-track-error" id="ua-track-code-err" aria-live="polite"></p>
        <p class="ua-track-error" id="ua-track-last-err" aria-live="polite"></p>
      </div>
      <div class="ua-track-meta">
        <ul class="ua-track-legend" data-kind="FM" aria-label="Facility request statuses">
          <li style="--dot:#F59E0B">Pending</li>
          <li style="--dot:#34D399">Approved</li>
          <li style="--dot:#60A5FA">Reserved</li>
          <li style="--dot:#F87171">Disapproved</li>
          <li style="--dot:#94A3B8">Cancelled</li>
        </ul>
        <ul class="ua-track-legend" data-kind="RM" aria-label="Repair ticket statuses" hidden>
          <li style="--dot:#94A3B8">Notified</li>
          <li style="--dot:#F59E0B">Pending</li>
          <li style="--dot:#60A5FA">Ongoing</li>
          <li style="--dot:#34D399">Success</li>
          <li style="--dot:#F87171">Failed</li>
        </ul>
        <ul class="ua-track-legend" data-kind="BK" aria-label="Booking statuses" hidden>
          <li style="--dot:#60A5FA">Reserved</li>
          <li style="--dot:#4338CA">Rescheduled</li>
          <li style="--dot:#34D399">Completed</li>
          <li style="--dot:#94A3B8">Cancelled</li>
        </ul>
        <p class="ua-track-hint">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
          <span id="ua-track-hint-text">Facility tickets start with <code>FM</code> — find yours on your request confirmation.</span>
        </p>
      </div>
    </form>
  </div>

  {{-- Result pop-up --}}
  <dialog class="ua-track-dialog" id="ua-track-dialog" aria-labelledby="ua-tr-status">
    <div class="ua-tr-top">
      <button type="button" class="ua-tr-close" data-close aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
      <div class="ua-tr-badge" id="ua-tr-badge" aria-hidden="true"></div>
      <p class="ua-tr-type" id="ua-tr-type"></p>
      <p class="ua-tr-code" id="ua-tr-code"></p>
      <h3 class="ua-tr-status" id="ua-tr-status"></h3>
      <span class="ua-tr-pill" id="ua-tr-pill"></span>
      <p class="ua-tr-msg" id="ua-tr-msg"></p>
    </div>
    <div class="ua-tr-body">
      <dl class="ua-tr-details" id="ua-tr-details"></dl>
      <div class="ua-tr-documents" id="ua-tr-documents" hidden>
        <a class="ua-tr-primary" id="ua-tr-pdf" href="#" hidden>Open approved application PDF</a>
        <form id="ua-tr-payment-form" method="post" enctype="multipart/form-data" hidden>
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <label for="ua-tr-payment-file">Payment receipt (JPG, PNG, or PDF, max 10 MB)</label>
          <input type="file" id="ua-tr-payment-file" name="payment_attachment" accept=".jpg,.jpeg,.png,.pdf" required>
          <button class="ua-tr-primary" type="submit">Submit receipt</button>
        </form>
        <p id="ua-tr-payment-message" role="status" hidden></p>
      </div>
      <div class="ua-tr-actions">
        <button type="button" class="ua-tr-secondary" data-close autofocus>Search again</button>
          <a href="#calendar" class="ua-tr-primary" data-close data-kind="FM">View calendar</a>
        <button type="button" class="ua-tr-primary" data-close data-kind="RM" hidden>Done</button>
          <button type="button" class="ua-tr-primary" data-close data-kind="BK" hidden>Done</button>
      </div>
    </div>
  </dialog>
</section>

<script>
(function () {
  const form = document.getElementById('ua-track-form');
  const dialog = document.getElementById('ua-track-dialog');
  if (!form || !dialog) return;
  const codeInput = document.getElementById('ua-track-code');
  const lastInput = document.getElementById('ua-track-last');
  const typeSelect = document.getElementById('ua-track-type');
  const $ = (id) => document.getElementById(id);

  const icons = {
    clock:  '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    check:  '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
    play:   '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z"/>',
    swap:   '<path d="M17 3l4 4-4 4"/><path d="M3 7h18"/><path d="M7 21l-4-4 4-4"/><path d="M21 17H3"/>',
    x:      '<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/><path d="M8 11h6"/>',
    plug:   '<path d="M9 2v6M15 2v6M6 8h12v3a6 6 0 01-12 0z"/><path d="M12 17v5"/>',
    bell:   '<path d="M6 8a6 6 0 0112 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 003.4 0"/>',
    wrench: '<path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/>'
  };
  // Status sets per request type. FM = facility request, BK = booking, RM = repair & maintenance.
  const common = {
    notfound: { label: 'No match found', tone: '#475569', bg: '#F1F5F9', icon: 'search', msg: 'No matching request or booking was found. Check the tracking code and the exact last name provided for tracking.' },
    busy:     { label: 'Too many searches', tone: '#475569', bg: '#F1F5F9', icon: 'clock', msg: 'Please wait a minute, then try again.' },
    offline:  { label: 'Lookup not available yet', tone: '#475569', bg: '#F1F5F9', icon: 'plug', msg: 'Online status tracking isn’t available right now. Please try again or contact the GSU office.' }
  };
  const kinds = {
    FM: {
      name: 'Facility request', example: 'FM-2026-00123',
      hint: 'Enter the tracking code and the exact last name provided for tracking.',
      states: {
        pending:     { title: 'Waiting for review', label: 'Pending', tone: '#B45309', bg: '#FFFBEB', icon: 'clock', msg: 'Your request was received and is waiting for GSU review.' },
        pending_payment: { title: 'Payment being verified', label: 'Pending payment', tone: '#B45309', bg: '#FFFBEB', icon: 'clock', msg: 'GSU is checking your payment receipt before reviewing the request.' },
        reserved:    { title: 'Request approved', label: 'Approved', tone: '#047857', bg: '#ECFDF5', icon: 'check', msg: 'GSU approved your request. Submit the payment receipt below to continue.' },
        disapproved: { title: 'Request not approved', label: 'Disapproved', tone: '#B91C1C', bg: '#FEF2F2', icon: 'x', msg: 'GSU did not approve this request. Sign in to your portal for details, or contact the GSU office.' },
        approved:    { title: 'Request approved', label: 'Approved', tone: '#047857', bg: '#ECFDF5', icon: 'check', msg: 'GSU approved your request. Submit the payment receipt below to continue.' },
        ongoing:     { title: 'In progress', label: 'Ongoing', tone: '#1D4ED8', bg: '#EFF6FF', icon: 'play', msg: 'Your request is approved and being processed for your schedule.' },
        rescheduled: { title: 'Moved to a new schedule', label: 'Rescheduled', tone: '#4338CA', bg: '#EEF2FF', icon: 'swap', msg: 'Your booking was moved to a new date or time. Check the details below.' },
        cancelled:   { title: 'Request cancelled', label: 'Cancelled', tone: '#475569', bg: '#F1F5F9', icon: 'x', msg: 'This request was cancelled. Submit a new request if you still need the venue.' }
      }
    },
    RM: {
      name: 'Repair & maintenance', example: 'RM-20261019-AB12CD',
      hint: 'Enter the code and the exact last name provided for tracking.',
      states: {
        notified: { title: 'Admins notified', label: 'Notified', tone: '#475569', bg: '#F1F5F9', icon: 'bell', msg: 'Your repair request reached the facility administrators and is waiting to be scheduled.' },
        pending:  { title: 'Waiting to start', label: 'Pending', tone: '#B45309', bg: '#FFFBEB', icon: 'clock', msg: 'Your repair is queued and will start once a team is assigned.' },
        ongoing:  { title: 'Repair in progress', label: 'Ongoing', tone: '#1D4ED8', bg: '#EFF6FF', icon: 'wrench', msg: 'The maintenance team is working on it now.' },
        success:  { title: 'Repair completed', label: 'Success', tone: '#047857', bg: '#ECFDF5', icon: 'check', msg: 'The repair was completed successfully.' },
        failed:   { title: 'Repair not completed', label: 'Failed', tone: '#B91C1C', bg: '#FEF2F2', icon: 'x', msg: 'The repair couldn’t be completed. GSU will contact you about next steps, or you can send a new request.' }
      }
    },
    BK: {
      name: 'Facility booking', example: 'BKG-20261025173245-11-ABC123',
      hint: 'Enter the booking code and the exact last name provided for tracking.',
      states: {
      pending:     { title: 'Booking awaiting confirmation', label: 'Pending', tone: '#B45309', bg: '#FFFBEB', icon: 'clock', msg: 'This booking is waiting for confirmation. See the requested details below.' },
      reserved:    { title: 'Booking reserved', label: 'Reserved', tone: '#1D4ED8', bg: '#EFF6FF', icon: 'check', msg: 'This reservation is confirmed. See the booking details below.' },
      booked:      { title: 'Booking reserved', label: 'Reserved', tone: '#1D4ED8', bg: '#EFF6FF', icon: 'check', msg: 'This reservation is confirmed. See the booking details below.' },
        rescheduled: { title: 'Booking rescheduled', label: 'Rescheduled', tone: '#4338CA', bg: '#EEF2FF', icon: 'swap', msg: 'This reservation was moved to a new date or time. See the updated details below.' },
        completed:   { title: 'Booking completed', label: 'Completed', tone: '#047857', bg: '#ECFDF5', icon: 'check', msg: 'This reservation has been completed.' },
        cancelled:   { title: 'Booking cancelled', label: 'Cancelled', tone: '#475569', bg: '#F1F5F9', icon: 'x', msg: 'This reservation was cancelled.' }
      }
    }
  };

  // Shows the pop-up. result includes signed document actions when a facility request is approved.
  window.uaShowTicketResult = function (result) {
    const kind = kinds[result.kind] ? result.kind : typeSelect.value;
    const isStatus = !!kinds[kind].states[result.status];
    const s = kinds[kind].states[result.status] || common[result.status] || common.notfound;
    dialog.style.setProperty('--tone', s.tone);
    dialog.style.setProperty('--tone-bg', s.bg);
    $('ua-tr-badge').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + icons[s.icon] + '</svg>';
    $('ua-tr-type').textContent = kind === 'BK' ? kinds[kind].name : kinds[kind].name + ' ticket';
    $('ua-tr-code').textContent = result.code || '';
    $('ua-tr-status').textContent = isStatus ? s.title : s.label;
    $('ua-tr-pill').textContent = s.label;
    $('ua-tr-pill').hidden = !isStatus;
    let message = s.msg;
    const approvedStatus = ['approved', 'reserved', 'rescheduled'].includes(result.status);
    if (kind === 'FM' && approvedStatus && result.payment_status === 'pending_payment') {
      message = 'Your request is approved. Submit your payment receipt below using this ticket.';
    } else if (kind === 'FM' && approvedStatus && result.payment_status === 'payment_uploaded') {
      message = 'Your request is approved. GSU is verifying your payment receipt.';
    } else if (kind === 'FM' && approvedStatus && result.payment_status === 'payment_verified') {
      message = 'Your request and payment are approved. Open the completed application PDF below.';
    }
    $('ua-tr-msg').textContent = message;
    const rows = (kind === 'RM'
      ? [['Facility', result.venue], ['Subject', result.subject], ['Sent', result.sent]]
      : [
          ['Venue', result.venue],
          ...(kind === 'BK' ? [['Purpose', result.purpose]] : []),
          ['Date', result.date],
          ['Time', result.time],
          ...(kind === 'FM' && result.payment_status ? [['Payment', result.payment_status.replaceAll('_', ' ')]] : [])
        ]).filter(r => r[1]);
    dialog.querySelectorAll('.ua-tr-actions [data-kind]').forEach(el => { el.hidden = el.dataset.kind !== kind; });
    const dl = $('ua-tr-details');
    dl.innerHTML = '';
    rows.forEach(function (r) {
      const div = document.createElement('div');
      const dt = document.createElement('dt'); dt.textContent = r[0];
      const dd = document.createElement('dd'); dd.textContent = r[1];
      div.append(dt, dd); dl.append(div);
    });
    dl.hidden = rows.length === 0;
    const documents = $('ua-tr-documents');
    const pdf = $('ua-tr-pdf');
    const paymentForm = $('ua-tr-payment-form');
    const paymentMessage = $('ua-tr-payment-message');
    pdf.hidden = !(kind === 'FM' && result.can_download_pdf && result.pdf_url);
    if (!pdf.hidden) pdf.href = result.pdf_url;
    paymentForm.hidden = !(kind === 'FM' && result.can_upload_payment && result.payment_upload_url);
    if (!paymentForm.hidden) paymentForm.action = result.payment_upload_url;
    paymentMessage.hidden = true;
    documents.hidden = pdf.hidden && paymentForm.hidden;
    if (!dialog.open) dialog.showModal();
  };

  $('ua-tr-payment-form').addEventListener('submit', function (event) {
    event.preventDefault();
    const paymentForm = event.currentTarget;
    const button = paymentForm.querySelector('button[type="submit"]');
    const message = $('ua-tr-payment-message');
    button.disabled = true;
    fetch(paymentForm.action, {
      method: 'POST',
      body: new FormData(paymentForm),
      headers: { Accept: 'application/json' }
    }).then(async response => {
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Receipt upload failed. Search for your ticket again and retry.');
      paymentForm.hidden = true;
      message.textContent = data.message;
      message.hidden = false;
    }).catch(error => {
      message.textContent = error.message;
      message.hidden = false;
    }).finally(() => { button.disabled = false; });
  });

  function setError(input, errId, text) {
    $(errId).textContent = text;
    input.setAttribute('aria-invalid', text ? 'true' : 'false');
    input.closest('.ua-track-seg').classList.toggle('is-invalid', !!text);
    return !text;
  }
  form.querySelectorAll('.ua-track-seg').forEach(function (seg) {
    seg.addEventListener('click', function (e) { if (!['INPUT', 'SELECT'].includes(e.target.tagName)) $(seg.dataset.for).focus(); });
  });

  function applyKind() {
    const k = kinds[typeSelect.value];
    codeInput.placeholder = k.example;
    $('ua-track-hint-text').innerHTML = k.hint;
    form.querySelectorAll('.ua-track-legend').forEach(ul => { ul.hidden = ul.dataset.kind !== typeSelect.value; });
    if (codeInput.getAttribute('aria-invalid') === 'true') setError(codeInput, 'ua-track-code-err', '');
  }
  typeSelect.addEventListener('change', function () { applyKind(); codeInput.focus(); });
  applyKind();

  codeInput.addEventListener('input', function () {
    codeInput.value = codeInput.value.toUpperCase().replace(/\s+/g, '');
    if (codeInput.getAttribute('aria-invalid') === 'true') setError(codeInput, 'ua-track-code-err', '');
  });
  if (lastInput) {
    lastInput.addEventListener('input', function () {
      if (lastInput.getAttribute('aria-invalid') === 'true') setError(lastInput, 'ua-track-last-err', '');
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const code = codeInput.value.trim().toUpperCase();
    const last = lastInput ? lastInput.value.trim() : '';
    const kind = typeSelect.value, k = kinds[kind];
    const codePatterns = {
      FM: /^FM-\d{4}-\d{1,10}$/,
      BK: /^BKG-[A-Z0-9-]{10,40}$/,
      RM: /^RM-[A-Z0-9-]{3,40}$/
    };
    const codeOk = setError(codeInput, 'ua-track-code-err',
      !code ? 'Enter your tracking code.' : (!codePatterns[kind].test(code) ? 'Enter a valid ' + k.name.toLowerCase() + ' code, e.g. ' + k.example + '.' : ''));
    const lastOk = setError(lastInput, 'ua-track-last-err', !last ? 'Enter the exact tracking last name.' : '');
    if (!codeOk) { codeInput.focus(); return; }
    if (!lastOk) { lastInput.focus(); return; }

    const endpoint = form.dataset.endpoint;
    if (!endpoint) { window.uaShowTicketResult({ kind: kind, status: 'offline', code: code }); return; }
    const params = new URLSearchParams({ type: kind, code: code });
    params.set('last_name', last);
    fetch(endpoint + '?' + params, { headers: { Accept: 'application/json' } })
      .then(r => r.ok ? r.json() : (r.status === 429 ? { status: 'busy' } : { status: 'notfound' }))
      .then(data => {
        if (data.status === 'needs_last_name' && lastInput) {
          setError(lastInput, 'ua-track-last-err', 'Enter the exact tracking last name.');
          lastInput.focus();
          return;
        }
        window.uaShowTicketResult(Object.assign({ kind: kind, code: code }, data));
      })
      .catch(() => window.uaShowTicketResult({ kind: kind, status: 'offline', code: code }));
  });

  let refocus = true;
  dialog.addEventListener('click', function (e) {
    const closer = e.target.closest('[data-close]');
    if (e.target === dialog || closer) {
      refocus = !(closer && closer.tagName === 'A'); // "View calendar" scrolls away, so don't pull focus back
      dialog.close();
    }
  });
  dialog.addEventListener('close', function () { if (refocus) codeInput.focus({ preventScroll: true }); refocus = true; });
})();
</script>

{{-- KEY FACILITIES --}}
@php
  $keyFacilities = [
    ['name' => 'Busalian Hall',  'img' => 'busalian-hall.jpg',  'desc' => 'The main multi-purpose hall for large assemblies, graduations, and formal ceremonies.'],
    ['name' => 'Paghiusa Hall',  'img' => 'paghiusa-hall.jpg',  'desc' => 'A large audio-visual hall ideal for university-wide seminars and major presentations.'],
    ['name' => 'Tiripunan Hall', 'img' => 'tiripunan-hall.jpg', 'desc' => 'A versatile gathering space designed for student organization meetings and collaborative activities.'],
    ['name' => 'Grandstand',     'img' => 'grand-stand.jpg',    'desc' => 'Open-air seating and field area for university sports events, intramurals, and outdoor gatherings.'],
    ['name' => 'New AVR',        'img' => 'new-avr.jpg',        'desc' => 'Newly upgraded audio-visual room featuring state-of-the-art projection and sound systems.'],
    ['name' => 'E-Hub',          'img' => null,                 'desc' => 'Modern digital innovation center equipped with workstations for tech events and workshops.'],
    ['name' => 'CIT AVR',        'img' => null,                 'desc' => "Dedicated audio-visual room for the College of Industrial Technology's lectures and defenses."],
    ['name' => 'CBA AVR',        'img' => null,                 'desc' => "Premier audio-visual facility for the College of Business Administration's seminars and conferences."],
    ['name' => 'Balay ni Juan',  'img' => null,                 'desc' => 'A cultural and heritage venue suited for intimate gatherings, art exhibits, and special receptions.'],
  ];
  // Venues without a photo get a panel in one of the seal's colours
  $sealTones = ['green', 'blue', 'gold', 'crimson'];
@endphp
<section class="ua-facilities-section ul-venues" id="facilities" aria-labelledby="ul-venues-title">
  <div class="ul-section-head">
    <div>
      <h2 id="ul-venues-title">Key Facilities</h2>
      <p>Core venues and spaces available through the University of Antique Facility Management System.</p>
    </div>
    <a class="ul-text-link" href="{{ route('public.rates') }}">Venue rates
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>

  <div class="ua-fac-carousel">
    <button type="button" class="ua-fac-arrow ua-fac-prev" aria-label="Previous facilities">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>

    <div class="ua-fac-track">
      @foreach ($keyFacilities as $i => $facility)
        <article class="ua-fac-card">
          <div class="ua-fac-media">
            @if ($facility['img'])
              <img src="{{ asset('img/facilities/' . $facility['img']) }}" alt="{{ $facility['name'] }}" loading="lazy">
            @else
              <div class="ul-fac-tone is-{{ $sealTones[$i % count($sealTones)] }}">
                <span class="ul-fac-initial" aria-hidden="true">{{ \Illuminate\Support\Str::of($facility['name'])->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}</span>
                <span class="ul-fac-soon">Photo coming soon</span>
              </div>
            @endif
          </div>
          <div class="ua-fac-body">
            <h3>{{ $facility['name'] }}</h3>
            <p class="ua-fac-desc">{{ $facility['desc'] }}</p>
          </div>
        </article>
      @endforeach
    </div>

    <button type="button" class="ua-fac-arrow ua-fac-next" aria-label="Next facilities">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
    </button>
  </div>

  @if (($unavailableFacilities ?? collect())->isNotEmpty())
    <p class="ul-venues-note">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
      <span>Not available right now:
        @foreach ($unavailableFacilities as $f)
          <strong>{{ $f->name }}</strong> ({{ $f->availability_status === 'maintenance' ? 'under maintenance' : 'unavailable' }}){{ $loop->last ? '' : ',' }}
        @endforeach
      </span>
    </p>
  @endif
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('.ua-fac-track');
    if (!track) return;
    const carousel = track.parentElement;
    const prev = carousel.querySelector('.ua-fac-prev');
    const next = carousel.querySelector('.ua-fac-next');
    const page = () => track.clientWidth + parseFloat(getComputedStyle(track).columnGap || 0);
    const update = () => {
      prev.disabled = track.scrollLeft <= 4;
      next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
    };
    prev.addEventListener('click', () => track.scrollBy({ left: -page() }));
    next.addEventListener('click', () => track.scrollBy({ left: page() }));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  });
</script>

{{-- PUBLIC CALENDAR (the shared partial; the request form also reads its markup) --}}
<section class="ua-calendar-section ul-schedule" aria-labelledby="ul-schedule-title">
  <div class="ul-section-head">
    <div>
      <h2 id="ul-schedule-title">Reservation Schedule</h2>
      <p>Public calendar of approved and scheduled facility reservations. All times are in Philippine Standard Time.</p>
    </div>
    <ul class="ul-cal-key" aria-label="Calendar key">
      <li><i class="is-today"></i>Today</li>
      <li><i class="is-booked"></i>Has reservations</li>
    </ul>
  </div>
  @include('public.calendar', ['hideCalendarHeading' => true])
</section>

</div>
@endsection
