@extends('layouts.app')

{{-- Full-width portal; same shell as the other portals (CLAUDE.md: navigation is identical across portals) --}}
@section('main-content-class', 'w-full')

@section('content')
@include('layouts.partials.portal-shell', [
    'roleLabel'      => 'Guest · ' . (optional(auth()->user())->organization_name ?: 'External requester'),
    'portalHeading'  => 'Guest Portal',
    'dashboardRoute' => 'guest.dashboard',
    'contentSection' => 'guest-content',
    'sections' => [
        [
            'heading' => 'Overview',
            'links' => [
                ['label' => 'Dashboard', 'route' => 'guest.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Venue rates', 'route' => 'guest.rates', 'icon' => 'tag'],
                ['label' => 'Reservation calendar', 'route' => 'guest.bookings.calendar', 'icon' => 'calendar'],
                ['label' => 'My bookings', 'route' => 'guest.bookings.index', 'icon' => 'bookmark'],
            ],
        ],
        [
            'heading' => 'Requests',
            'links' => [
                ['label' => 'My requests', 'route' => 'guest.requests.facilities.index', 'routes' => ['guest.requests.facilities.index', 'guest.requests.facilities.show'], 'icon' => 'inbox'],
                ['label' => 'New request', 'route' => 'guest.requests.facilities.create', 'icon' => 'file-plus'],
            ],
        ],
    ],
])
@endsection
