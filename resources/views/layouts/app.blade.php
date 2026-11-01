<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'UA Facility Management System'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@8..144,100..1000&family=Inter:wght@400;500;600;700&family=Merriweather:wght@700;900&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@8..144,100..1000&family=Inter:wght@400;500;600;700&family=Merriweather:wght@700;900&display=swap"></noscript>
    <script>
        window.loadReservationCalendarMonth = (url, month) => {
            if (window.ReservationCalendar) {
                return window.ReservationCalendar.loadMonth(url, month);
            }

            return new Promise((resolve, reject) => {
                window.addEventListener('reservation-calendar:ready', () => {
                    window.ReservationCalendar.loadMonth(url, month).then(resolve, reject);
                }, { once: true });
            });
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-black antialiased">
<div class="flex min-h-screen flex-col">
    {{-- Pages can opt out of the public top bar with @section('no-public-header', true) (e.g. Guest Access) --}}
    @if (!request()->routeIs('login') && !auth()->check() && !View::hasSection('no-public-header'))
        @include('layouts.partials.header')
    @endif


    <main class="flex-1">
        {{-- Flash messages pop up as a toast (success closes after 5 seconds) --}}
        @include('layouts.partials.flash-toast')

        <div class="@yield('main-content-class', 'mx-auto w-full max-w-6xl px-4 py-6')">
            @yield('content')
        </div>
    </main>

    @include('layouts.partials.confirmation-dialog')

    @if (request()->is('/'))
    <footer class="site-footer">
        <div class="site-footer-inner">

            <div class="site-footer-grid">
                <!-- Logo and Title -->
                <div class="site-footer-brand">
                    <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="University of Antique Logo" class="footer-logo">
                    <div>
                        <h3 class="site-footer-title">University of Antique</h3>
                        <p class="site-footer-subtitle">Facility Management System</p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h4 class="site-footer-heading">Contact Information</h4>
                    <ul class="site-footer-list">
                        <li>Sibalom, Antique, Philippines</li>
                        <li>(036) 543-8877</li>
                        <li><a href="mailto:fms@ua.edu.ph">fms@ua.edu.ph</a></li>
                    </ul>
                </div>

                <!-- System Information -->
                <div>
                    <h4 class="site-footer-heading">System Information</h4>
                    <dl class="site-footer-list">
                        <div><dt>System Version:</dt><dd>v2.1.0</dd></div>
                        <div><dt>Academic Year:</dt><dd>2026–2027</dd></div>
                        <div><dt>GSU-FM Form:</dt><dd>GSU-FM-011 Rev.2</dd></div>
                        <div><dt>Last Updated:</dt><dd>July 4, 2026</dd></div>
                    </dl>
                </div>
            </div>

            <!-- Sub Footer -->
            <div class="site-footer-bottom">
                <span>&copy; {{ date('Y') }} <strong>University of Antique</strong>. All rights reserved.</span>
                <span class="site-footer-sep" aria-hidden="true">|</span>
                <span>GSU Facility Management Office</span>
            </div>
        </div>
    </footer>
    @endif
</div>
</body>
</html>