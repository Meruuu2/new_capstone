@extends('layouts.college')

@section('college-content')
@include('layouts.partials.unit-users-index', [
    'prefix'   => 'college',
    'unitName' => auth()->user()->college_name,
    'unitNoun' => 'college',
])
@endsection
