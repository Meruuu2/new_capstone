@extends('layouts.college')

@section('college-content')
@include('layouts.partials.page-kit')
@include('layouts.partials.ticket-detail', [
    'backUrl' => route('college.maintenance-tickets.index'),
    'conversation' => [
        'fetchUrl'  => route('college.maintenance-tickets.messages', $ticket),
        'sendUrl'   => route('college.maintenance-tickets.messages.store', $ticket),
        'otherSide' => 'the GSU facility administrators',
    ],
])
@endsection
