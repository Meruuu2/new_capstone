@extends('layouts.app')

@section('title', 'Log In – UA Facility Management System')

@section('main-content-class', 'w-full')

@section('content')
@php
    // Display-only label for the portal card the visitor clicked on the landing page
    $portalLabels = [
        'admin'   => 'Admin Portal',
        'college' => 'College Staff Portal',
        'org'     => 'Org Unit Portal',
        'guest'   => 'Guest Access Portal',
    ];
    $portalLabel = $portalLabels[request('portal')] ?? 'Facility Management Portal';
@endphp

<div class="login-page-wrapper" style="--login-hero-image: url('{{ asset('img/facilities/grand-stand.jpg') }}')">
  <div class="login-container">

    <a href="{{ url('/') }}" class="home-return-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      Back to home
    </a>

    <div class="login-card">
      <div class="login-header">
        <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="University of Antique Logo" class="login-logo">
        <h1>Log In</h1>
        <div class="login-admin-tag">{{ $portalLabel }}</div>
        <p>Use your assigned UA email and password to sign in.</p>
      </div>

      @if ($errors->any())
          <div class="fms-alert-error">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf
        {{-- Which portal card was used, so only that portal's accounts can sign in here --}}
        @if (in_array(request('portal'), ['admin', 'college', 'org'], true))
          <input type="hidden" name="portal" value="{{ request('portal') }}">
        @endif

        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            id="email"
            type="email"
            name="email"
            autocomplete="username"
            value="{{ old('email') }}"
            required
            autofocus
            class="form-control"
            placeholder="Enter your email"
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-field">
            <input
              id="password"
              type="password"
              name="password"
              autocomplete="current-password"
              required
              class="form-control"
              placeholder="Enter your password"
            >
            <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" aria-controls="password">
              <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 7 10 7a9.8 9.8 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="login-btn">
          Log In
        </button>
      </form>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.password-toggle');
    const input = document.getElementById('password');
    if (!toggle || !input) return;
    toggle.addEventListener('click', function () {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(show));
      toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      input.focus();
    });
  });
</script>
@endsection
