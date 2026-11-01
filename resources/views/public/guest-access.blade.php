@extends('layouts.app')

@section('title', 'Guest Access – UA Facility Management System')

@section('main-content-class', 'w-full')

@section('no-public-header', true)

@section('content')
@php
    // Official rates (shared with the rates page and the request form)
    $rates = collect(\App\Support\VenueRates::all());
    $featured = $rates->filter(fn ($v) => !str_contains($v['rate'], '/head'))->take(6);
    $price = function ($rate) {
        $rest = trim(preg_replace('/^PhP\s*/', '', $rate));
        $parts = explode('/', $rest, 2);
        return ['amount' => '₱' . $parts[0], 'unit' => isset($parts[1]) ? '/ ' . $parts[1] : 'per day'];
    };
@endphp

<div class="ua-landing gx">
    {{-- Hero: an invitation, with venue "polaroids" fanned on the right --}}
    {{-- No top bar on this page: just a way back --}}
    <a href="{{ route('home') }}" class="gx-back" id="gx-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back
    </a>

    <section class="gx-hero">
        <div class="gx-hero-copy">
            <p class="gx-pill">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>
                Guest access · No account needed
            </p>
            <h1>Bring your event to the <em>University of Antique</em>.</h1>
            <p class="gx-lead">Halls, AVRs, open grounds and the track oval are open to groups and individuals from outside the university. Pick a date, choose a venue, and the GSU office takes it from there.</p>
            <div class="gx-cta">
                <a href="{{ route('public.request.create') }}" class="gx-btn gx-btn--primary">
                    Start your request
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="{{ route('public.rates') }}" class="gx-btn gx-btn--ghost">See venue rates</a>
            </div>
        </div>

        <div class="gx-stack" aria-hidden="true">
            <figure class="gx-photo gx-photo--1">
                <img src="{{ asset('img/facilities/paghiusa-hall.jpg') }}" alt="">
                <figcaption><strong>PaghiUsA Hall</strong><span>Up to 600 guests</span></figcaption>
            </figure>
            <figure class="gx-photo gx-photo--2">
                <img src="{{ asset('img/facilities/grand-stand.jpg') }}" alt="">
                <figcaption><strong>Track Oval</strong><span>Sports & outdoor events</span></figcaption>
            </figure>
            <figure class="gx-photo gx-photo--3">
                <img src="{{ asset('img/facilities/new-avr.jpg') }}" alt="">
                <figcaption><strong>New AVR</strong><span>Seminars · 80–100 seats</span></figcaption>
            </figure>
            <span class="gx-stamp">Open to<br>outside<br>guests</span>
        </div>
    </section>

    {{-- Who can request --}}
    <section class="gx-section">
        <header class="gx-head">
            <p class="ua-eyebrow">Who can request</p>
            <h2>Not from UA? You can still book a venue.</h2>
        </header>
        <div class="gx-audience">
            <article class="gx-aud">
                <span class="gx-aud-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg></span>
                <h3>Organizations outside the university</h3>
                <p>Government offices, schools, NGOs, companies, churches and community groups holding an activity on campus.</p>
                <ul class="gx-tags">
                    <li>Seminars & trainings</li><li>Conferences</li><li>Assemblies</li><li>Graduations</li><li>Outreach programs</li>
                </ul>
            </article>
            <article class="gx-aud gx-aud--dark">
                <span class="gx-aud-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/></svg></span>
                <h3>Individuals & private groups</h3>
                <p>Anyone from outside the school who wants to hold an event inside the campus, on their own or for family and friends.</p>
                <ul class="gx-tags">
                    <li>Reunions</li><li>Sports tournaments</li><li>Recitals & shows</li><li>Workshops</li><li>Celebrations</li>
                </ul>
            </article>
        </div>
    </section>

    {{-- How it works --}}
    <section class="gx-section gx-section--tint">
        <header class="gx-head">
            <p class="ua-eyebrow">How it works</p>
            <h2>Four steps from idea to reserved venue</h2>
        </header>
        <ol class="gx-steps">
            <li>
                <span class="gx-step-num">1</span>
                <h3>Pick your date</h3>
                <p>See which days are already booked on the calendar. Choose one day or a run of days.</p>
            </li>
            <li>
                <span class="gx-step-num">2</span>
                <h3>Choose a venue</h3>
                <p>Each venue shows its rate and whether it's free on your date, so there are no surprises.</p>
            </li>
            <li>
                <span class="gx-step-num">3</span>
                <h3>Submit &amp; pay</h3>
                <p>Upload your payment receipt with your request. GSU verifies the payment, then reviews your request.</p>
            </li>
            <li>
                <span class="gx-step-num">4</span>
                <h3>Track with your ticket</h3>
                <p>You get a ticket code like <code>FM-2026-00016</code>. Check its status anytime from the home page.</p>
            </li>
        </ol>
    </section>

    {{-- Venues & rates --}}
    <section class="gx-section">
        <header class="gx-head gx-head--row">
            <div>
                <p class="ua-eyebrow">Venues & rates</p>
                <h2>Popular venues for guests</h2>
            </div>
            <a href="{{ route('public.rates') }}" class="gx-more">All rates &amp; packages →</a>
        </header>
        <ul class="gx-rates">
            @foreach ($featured as $venue)
                @php $pr = $price($venue['rate']); @endphp
                <li class="gx-rate">
                    <div class="gx-rate-media">
                        @if ($venue['img'])
                            <img src="{{ asset('img/facilities/' . $venue['img']) }}" alt="" loading="lazy">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                        @endif
                    </div>
                    <div class="gx-rate-body">
                        <h3>{{ $venue['name'] }}</h3>
                        <p>Estimated seating: {{ $venue['capacity'] !== '-' ? $venue['capacity'] : 'No fixed seating capacity' }}</p>
                    </div>
                    <p class="gx-rate-price"><strong>{{ $pr['amount'] }}</strong><span>{{ $pr['unit'] }}</span></p>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- What to prepare --}}
    <section class="gx-section gx-prepare">
        <div class="gx-prepare-copy">
            <p class="ua-eyebrow">Before you start</p>
            <h2>Have these ready</h2>
            <p>The form takes about five minutes. Your request is filed with GSU right away, and this permit also serves as the <strong>gate pass</strong> for your organizers.</p>
        </div>
        <ul class="gx-check">
            <li><strong>Your contact details</strong><span>Name, mobile number and address, so GSU can reach you.</span></li>
            <li><strong>Organization or group name</strong><span>Who you represent, or your own name for a private event.</span></li>
            <li><strong>Your immediate head</strong><span>The person who notes the request (e.g. your organization's head).</span></li>
            <li><strong>Event date &amp; time</strong><span>At least 30 days from today; same time each day for multi-day events.</span></li>
            <li><strong>Your payment receipt</strong><span>Upload a JPG, PNG, or PDF file no larger than 10 MB with your request.</span></li>
            <li><strong>An email address</strong><span>GSU uses it to contact you about your request.</span></li>
        </ul>
    </section>

    {{-- Closing call to action --}}
    <section class="gx-final">
        <div>
            <h2>Ready to bring your event to campus?</h2>
            <p>No account, no sign-up. Just your details and a date.</p>
        </div>
        <div class="gx-cta">
            <a href="{{ route('public.request.create') }}" class="gx-btn gx-btn--light">
                Start your request
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <a href="{{ route('home') }}#track" class="gx-btn gx-btn--outline-light">Already requested? Track it</a>
        </div>
    </section>
</div>

<script>
    // Back returns to the previous page on this site; visitors who arrived from elsewhere go home
    document.getElementById('gx-back').addEventListener('click', function (e) {
        try {
            if (document.referrer && new URL(document.referrer).origin === location.origin && history.length > 1) {
                e.preventDefault();
                history.back();
            }
        } catch (err) {}
    });
</script>
@endsection
