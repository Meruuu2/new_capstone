@extends('layouts.college')

@section('college-content')
@include('layouts.partials.page-kit')
@include('layouts.partials.request-detail', [
    'backUrl'   => route('college.requests.index'),
    'cancelUrl' => route('college.requests.facilities.cancel', $submission),
])
@endsection
