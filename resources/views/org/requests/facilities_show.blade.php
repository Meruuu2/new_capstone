@extends('layouts.org')

@section('org-content')
@include('layouts.partials.page-kit')
@include('layouts.partials.request-detail', [
    'backUrl'   => route('org.requests.facilities.index'),
    'cancelUrl' => route('org.requests.facilities.cancel', $submission),
])
@endsection
