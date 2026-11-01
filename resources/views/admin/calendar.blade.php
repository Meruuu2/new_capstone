@extends('layouts.admin')

@section('admin-content')

<h1 class="fms-page-title mb-6">Reservation Calendar</h1>

@include('public.calendar', ['calendarRoute' => 'admin.calendar'])

@endsection
