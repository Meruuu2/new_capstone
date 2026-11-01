<!doctype html>
<html lang="en">
<body>
    <p>Hello {{ $requesterName }},</p>
    <p>Your facility request was received by the University of Antique General Services Unit.</p>
    <p>Your tracking number is <strong>{{ $trackingCode }}</strong>.</p>
    <p>To check its status, visit <a href="{{ route('home') }}#track">Track a request</a> and enter the tracking number above with the last name <strong>{{ $lastName }}</strong>.</p>
    <p>Payment receipt upload will become available in the tracker if your request is approved.</p>
    <p>University of Antique<br>General Services Unit</p>
</body>
</html>