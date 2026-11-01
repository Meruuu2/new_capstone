<!doctype html>
<html lang="en">
<body>
    <p>Hello {{ $requesterName }},</p>
    <p>There is an update to your University of Antique facility request.</p>
    <p>Tracking code: <strong>{{ $trackingCode }}</strong></p>

    @switch($event)
        @case('approved')
            <p>Your request was approved and the reservation was created.</p>
            @break
        @case('booked')
            <p>Your reservation has been booked.</p>
            @break
        @case('disapproved')
            <p>Your request was not approved.</p>
            @break
        @case('payment_verified')
            <p>GSU verified your payment receipt.</p>
            @break
        @case('rescheduled')
            <p>Your booking schedule has changed.</p>
            @break
        @case('cancelled')
            <p>Your booking was cancelled.</p>
            @break
    @endswitch

    @if (!empty($details['booking_code']))
        <p>Booking code: <strong>{{ $details['booking_code'] }}</strong></p>
    @endif
    @if (!empty($details['facility']))
        <p>Facility: {{ $details['facility'] }}</p>
    @endif
    @if (!empty($details['booking_date']))
        <p>Date: {{ $details['booking_date'] }}</p>
    @endif
    @if (!empty($details['booking_time']))
        <p>Time: {{ $details['booking_time'] }}</p>
    @endif
    @if (!empty($details['reason']))
        <p>Reason: {{ $details['reason'] }}</p>
    @endif

    <p>Track your request at <a href="{{ route('home') }}#track">the UA Facility Management System</a> using your tracking code and last name ({{ $lastName }}).</p>
    <p>University of Antique<br>General Services Unit</p>
</body>
</html>