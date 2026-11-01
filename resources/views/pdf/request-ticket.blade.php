{{-- Downloaded facility request ticket (dompdf: tables + inline CSS only). Same data as layouts.partials.request-ticket. --}}
@php
    $tones = ['is-good' => ['#DCFCE7', '#166534'], 'is-warn' => ['#FEF3C7', '#92400E'], 'is-bad' => ['#FEE2E2', '#991B1B'], 'is-muted' => ['#F1F5F9', '#475569']];
    [$pillBg, $pillFg] = $tones[$statusTone] ?? $tones['is-muted'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $ticketNo }}</title>
<style>
    @page { margin: 28px; }
    body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #0f172a; }
    .ticket { border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; }
    .top { background: #0f172a; color: #fff; }
    .top td { padding: 14px 16px; vertical-align: middle; }
    .brand { font-size: 11.5px; font-weight: bold; line-height: 1.3; }
    .brand small { display: block; font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #94A3B8; }
    .logo { width: 32px; height: 32px; border-radius: 16px; background: #fff; }
    .code-label { font-size: 7.5px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; color: #94A3B8; }
    .code { font-family: 'DejaVu Sans Mono', monospace; font-size: 16px; font-weight: bold; letter-spacing: .5px; }
    .pad { padding: 12px 16px 0; }
    .pill { display: inline-block; padding: 3px 9px; border-radius: 9px; font-size: 9.5px; font-weight: bold; background: {{ $pillBg }}; color: {{ $pillFg }}; }
    .control { font-size: 9.5px; color: #64748B; }
    .control b { font-family: 'DejaVu Sans Mono', monospace; color: #0f172a; }
    .cut { margin: 12px 0; border-top: 2px dashed #E2E8F0; }
    .rows { width: 100%; border-collapse: collapse; }
    .rows td { padding: 0 16px 10px; vertical-align: top; width: 50%; }
    .dt { font-size: 8px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; color: #94A3B8; margin-bottom: 2px; }
    .dd { font-size: 11px; font-weight: bold; line-height: 1.4; }
    .dd small { display: block; font-size: 9.5px; font-weight: normal; color: #64748B; }
    .venue { font-size: 13px; text-transform: uppercase; letter-spacing: .3px; }
    .stub { padding: 0 16px 14px; }
    .stub p { margin: 0 0 3px; font-size: 10px; line-height: 1.45; color: #334155; }
    .stub span { font-size: 9px; color: #94A3B8; }
    .note { margin-top: 10px; font-size: 8.5px; color: #94A3B8; text-align: center; }
</style>
</head>
<body>
<div class="ticket">
    <table width="100%" cellspacing="0" cellpadding="0" class="top">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0"><tr>
                    @if ($logo)<td style="padding:0 10px 0 0"><img src="{{ $logo }}" class="logo" alt=""></td>@endif
                    <td style="padding:0" class="brand">University of Antique<small>Facility request ticket</small></td>
                </tr></table>
            </td>
            <td align="right">
                <div class="code-label">Ticket code</div>
                <div class="code">{{ $ticketNo }}</div>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" class="pad">
        <tr>
            <td><span class="pill">{{ $statusLabel }}</span></td>
            <td align="right">@if (!empty($p['control_no']))<span class="control">Control no. <b>{{ $p['control_no'] }}</b></span>@endif</td>
        </tr>
    </table>

    <div class="cut"></div>

    <table class="rows">
        <tr><td colspan="2"><div class="dt">Venue</div><div class="dd venue">{{ $venue }}</div></td></tr>
        <tr>
            <td><div class="dt">Date</div><div class="dd">{{ $dateLabel }}@if ($dateSub)<small>{{ $dateSub }}</small>@endif</div></td>
            <td><div class="dt">Time</div><div class="dd">{{ $timeLabel }}</div></td>
        </tr>
        <tr><td colspan="2"><div class="dt">Purpose</div><div class="dd">{{ $p['purpose'] ?? '—' }}</div></td></tr>
        @if (!empty($p['number_of_persons']))
            <tr><td colspan="2"><div class="dt">Persons</div><div class="dd">{{ number_format($p['number_of_persons']) }}</div></td></tr>
        @endif
        <tr><td colspan="2"><div class="dt">Equipment</div><div class="dd">{{ $equipment->isNotEmpty() ? $equipment->join(', ') : 'None requested' }}</div></td></tr>
        <tr>
            <td><div class="dt">Noted by</div><div class="dd">{{ $notedBy }}</div></td>
            <td><div class="dt">Requested by</div><div class="dd">{{ $requester }}</div></td>
        </tr>
    </table>

    <div class="cut" style="margin-top:2px"></div>

    <div class="stub">
        <p>{{ $statusNote }}</p>
        <span>Submitted {{ $submission->created_at?->format('M j, Y · g:i A') }}</span>
    </div>
</div>
<p class="note">Track this request on the UA Facility Management System home page using the ticket code.</p>
</body>
</html>
