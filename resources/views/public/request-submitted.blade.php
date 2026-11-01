@extends('layouts.app')

@section('title', 'Request Submitted – UA Facility Management System')

@section('content')
@include('layouts.partials.page-kit')
@php
    $code = 'FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT);
    $name = trim((string) ($submission->payload['client_name'] ?? ''));
    $email = trim((string) ($submission->payload['email'] ?? ''));
    $lastName = trim((string) ($submission->payload['requester_last_name'] ?? ''));
@endphp

<div class="pk-page pr-done">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                Request sent to GSU
            </p>
            <h1>Thank you{{ $name !== '' ? ', ' . $name : '' }}!</h1>
            <p class="pk-hero-sub">Your facility request was received. Keep your ticket code: it's how you check your request's status.</p>
            @if (session('tracking_email_sent'))
                <p class="pk-hero-sub">We sent tracking code <strong>{{ $code }}</strong> to <strong>{{ $email }}</strong>. Use it with the tracking last name you provided to check your request.</p>
            @else
                <p class="pk-hero-sub">We couldn't email tracking code <strong>{{ $code }}</strong>{{ $email !== '' ? ' to ' . $email : '' }}. Save the code below to check your request.</p>
            @endif
        </div>
        <div class="pk-hero-actions">
            <button type="button" class="pk-btn" data-ticket-open>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/><path d="M13 5v2M13 11v2M13 17v2"/></svg>
                View ticket
            </button>
        </div>
    </section>

    <section class="pr-steps" aria-label="What happens next">
        <div class="pr-step">
            <span class="pr-step-num">1</span>
            <div>
                <h2>Save your ticket code</h2>
                <p class="pr-code">{{ $code }}</p>
                <p>Print the ticket or take a screenshot of it.</p>
            </div>
        </div>
        <div class="pr-step">
            <span class="pr-step-num">2</span>
            <div>
                <h2>Wait for approval</h2>
                <p>GSU will review your request. If approved, upload your payment receipt using the ticket tracker.</p>
            </div>
        </div>
        <div class="pr-step">
            <span class="pr-step-num">3</span>
            <div>
                <h2>Track it anytime</h2>
                <p>On the home page, enter <strong>{{ $code }}</strong>@if ($lastName !== '') and the tracking last name <strong>{{ $lastName }}</strong>@endif.</p>
                <a href="{{ route('home') }}#track" class="pr-link">Go to Track a request →</a>
            </div>
        </div>
    </section>

    <div class="pr-actions">
        <a href="{{ route('home') }}" class="pk-btn-outline">Back to home</a>
        <a href="{{ route('public.request.create') }}" class="pk-btn">Request another facility</a>
    </div>
</div>

@include('layouts.partials.request-ticket', ['submission' => $submission, 'autoOpen' => true])
@endsection
