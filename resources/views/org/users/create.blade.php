@extends('layouts.org')

@section('org-content')
@include('layouts.partials.unit-users-create', [
    'prefix'        => 'org',
    'unitName'      => auth()->user()->organization_name,
    'unitNoun'      => 'organization',
    'roleLabel'     => 'Organization Staff',
    'positionLabel' => 'Position in the organization',
    'headTitle'     => 'Governor',
    'positions'     => ['Governor', 'Vice Governor', 'Secretary', 'Treasurer', 'Auditor', 'Public Information Officer', 'Adviser'],
])
@endsection
