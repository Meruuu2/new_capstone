@extends('layouts.college')

@section('college-content')
@include('layouts.partials.unit-users-create', [
    'prefix'        => 'college',
    'unitName'      => auth()->user()->college_name,
    'unitNoun'      => 'college',
    'roleLabel'     => 'College Staff',
    'positionLabel' => 'Position in the department',
    'headTitle'     => 'College Admin',
    'positions'     => ['Dean', 'Program Head', 'Staff'],
])
@endsection
