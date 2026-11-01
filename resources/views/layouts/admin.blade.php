@extends('layouts.app')

{{-- Full-width portal: app.blade.php reads 'main-content-class' (the old 'main-class' name was ignored) --}}
@section('main-content-class', 'w-full')

@section('content')
@php
    $adminSections = [
        [
            'heading' => 'Overview',
            'links' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Monthly records', 'route' => 'admin.monthly-records.index', 'icon' => 'records'],
                ['label' => 'Repair records', 'route' => 'admin.repair-records.index', 'icon' => 'chart'],
            ],
        ],
        [
            'heading' => 'Facilities',
            'links' => [
                // Add/Edit facility open from this page (with a Back button), so they keep it highlighted
                ['label' => 'All facilities', 'route' => 'admin.facilities.index', 'routes' => 'admin.facilities.*', 'icon' => 'building'],
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
    ];
    // Facility and repair staff use the same shell with only the links their routes allow (see routes/web.php groups)
    $shellRole   = auth()->user()?->role;
    $repairLinks = ['admin.dashboard', 'admin.repair-records.index', 'admin.maintenance-tickets.index'];
    $adminOnly   = ['admin.users.index', 'admin.users.create', 'admin.audit-logs.index'];
    $allowed = match ($shellRole) {
        'maintenance_staff' => fn ($route) => in_array($route, $repairLinks, true),
        'facility_staff'    => fn ($route) => $route === 'admin.dashboard' || ! in_array($route, [...$repairLinks, ...$adminOnly], true),
        default             => fn ($route) => true,
    };
    $adminSections = collect($adminSections)
        ->map(fn ($g) => ['heading' => $g['heading'], 'links' => array_values(array_filter($g['links'], fn ($l) => $allowed($l['route'])))])
        ->filter(fn ($g) => $g['links'])->values()->all();
@endphp
@include('layouts.partials.portal-shell', [
    'roleLabel'      => match ($shellRole) { 'facility_staff' => 'Facilities', 'maintenance_staff' => 'Repair & Maintenance', default => 'GSU Administrator' },
    'portalHeading'  => 'GSU Admin Portal',
    'dashboardRoute' => 'admin.dashboard',
    'contentSection' => 'admin-content',
    'sections'       => $adminSections,
])
@endsection
