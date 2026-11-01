@extends('layouts.org')

@section('org-content')
@include('layouts.partials.unit-users-index', [
    'prefix'   => 'org',
    'unitName' => auth()->user()->organization_name,
    'unitNoun' => 'organization',
])
@endsection
