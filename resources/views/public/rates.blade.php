@extends('layouts.app')

@section('title', 'Venue Rates – UA Facility Management System')

@section('no-public-header', true)

@section('content')
@include('layouts.partials.page-kit')
@php
    $venues = \App\Support\VenueRates::all();
    // Split "PhP 15,000.00/day" into amount + unit for the price display
    $splitRate = function ($rate) {
        $rest = trim(preg_replace('/^PhP\s*/', '', $rate));
        $parts = explode('/', $rest, 2);
        return ['amount' => $parts[0], 'unit' => isset($parts[1]) ? '/ ' . $parts[1] : 'per use'];
    };
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/paghiusa-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>
                University of Antique · General Services Unit
            </p>
            <h1>Venues/Facilities &amp; Rentals</h1>
            <p class="pk-hero-sub">Official rates and packages for renting University venues.</p>
        </div>
        <div class="pk-hero-actions">
            {{-- No top bar on this page: just a way back --}}
            <a href="{{ route('home') }}" class="pk-btn-ghost" id="rt-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </a>
            <a href="{{ route('public.request.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Book a Facility Now
            </a>
        </div>
    </section>

    <div class="pk-section-head">
        <h2 class="pk-section-title">Venues</h2>
        <span class="pk-section-meta">{{ count($venues) }} venues</span>
    </div>
    <div class="rt-grid">
        @foreach ($venues as $venue)
            @php $price = $splitRate($venue['rate']); @endphp
            <article class="rt-card">
                <div class="rt-media">
                    @if ($venue['img'])
                        <img src="{{ asset('img/facilities/' . $venue['img']) }}" alt="" loading="lazy">
                    @else
                        <div class="rt-ph" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg></div>
                    @endif
                    <h3 class="rt-name">{{ $venue['name'] }}</h3>
                </div>
                <div class="rt-body">
                    <div class="rt-price" aria-label="Rate: {{ $venue['rate'] }}">
                        <span class="rt-cur">PhP</span>
                        <span class="rt-amount">{{ $price['amount'] }}</span>
                        <span class="rt-unit">{{ $price['unit'] }}</span>
                    </div>
                    <span class="rt-cap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/></svg>
                        Estimated seating capacity: {{ $venue['capacity'] === '-' ? 'No fixed seating capacity' : $venue['capacity'] }}
                    </span>
                    <ul class="rt-pack" aria-label="Facilities provided / package">
                        @foreach ($venue['package'] as $item)
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </div>

    <!-- LED Wall Notice -->
    <div class="rt-note" role="note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
        <p><strong>Note:</strong> LED Wall is excluded in the package and available for additional rental.</p>
    </div>
</div>

<script>
// Back: return to the previous page on this site, otherwise fall back to the home page
document.getElementById('rt-back').addEventListener('click', function (e) {
    try {
        if (document.referrer && new URL(document.referrer).origin === location.origin && history.length > 1) {
            e.preventDefault();
            history.back();
        }
    } catch (err) {}
});
</script>
@endsection
