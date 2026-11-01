@extends('layouts.app')

{{-- Full-width portal: app.blade.php reads 'main-content-class' (the old 'main-class' name was ignored) --}}
@section('main-content-class', 'w-full')

@section('content')
@include('layouts.partials.portal-shell', [
    'roleLabel'      => 'GSU Administrator',
    'portalHeading'  => 'GSU Admin Portal',
    'dashboardRoute' => 'admin.dashboard',
    'contentSection' => 'admin-content',
    'sections' => [
        [
            'heading' => 'Overview',
            'links' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Monthly records', 'route' => 'admin.monthly-records.index', 'icon' => 'records'],
            ],
        ],
        [
            'heading' => 'Facilities',
            'links' => [
                ['label' => 'All facilities', 'route' => 'admin.facilities.index', 'icon' => 'building'],
                ['label' => 'Add facility', 'route' => 'admin.facilities.create', 'icon' => 'plus'],
            ],
        ],
        [
            'heading' => 'Requests',
            'links' => [
                ['label' => 'All Requests', 'route' => 'admin.forms.facilities.index', 'routes' => 'admin.forms.facilities.*', 'icon' => 'approve'],
                ['label' => 'Repair & maintenance', 'route' => 'admin.maintenance-tickets.index', 'routes' => 'admin.maintenance-tickets.*', 'icon' => 'wrench'],
                ['label' => 'Reservations', 'route' => 'admin.bookings.index', 'routes' => ['admin.bookings.index'], 'icon' => 'bookmark'],
                ['label' => 'Create Direct Reservation', 'route' => 'admin.bookings.create-direct', 'routes' => ['admin.bookings.create-direct'], 'icon' => 'edit'],
                ['label' => 'Reservation calendar', 'route' => 'admin.calendar', 'icon' => 'calendar'],
                ['label' => 'Monthly overview', 'route' => 'admin.overview', 'icon' => 'chart'],
            ],
        ],
        [
            'heading' => 'Administration',
            'links' => [
                ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users'],
                ['label' => 'Create user', 'route' => 'admin.users.create', 'icon' => 'user-plus'],
                ['label' => 'Audit trail', 'route' => 'admin.audit-logs.index', 'icon' => 'records'],
            ],
        ],
    ],
])
@endsection
