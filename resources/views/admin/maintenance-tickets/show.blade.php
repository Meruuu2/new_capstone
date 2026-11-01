@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
{{-- Same layout as the College/Org ticket detail (layouts/partials/ticket-detail.blade.php), plus the admin update form. --}}
@php
    $statusMeta = [
        'notified' => ['Notified', 'is-neutral'],
        'pending'  => ['Pending',  'is-warn'],
        'ongoing'  => ['Ongoing',  'is-info'],
        'success'  => ['Success',  'is-good'],
        'failed'   => ['Failed',   'is-bad'],
    ];
    [$statusLabel, $statusTone] = $statusMeta[$ticket->status] ?? [ucfirst(str_replace('_', ' ', (string) $ticket->status)), 'is-neutral'];
    $sentAt       = $ticket->requested_at ?? $ticket->created_at;
    $facilityName = $ticket->facility?->name ?? $ticket->facility_other ?? 'Facility removed';
    $sender       = $ticket->requester?->name ?? 'Unknown';
    $initials     = collect(preg_split('/\s+/', trim((string) $sender)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
@endphp

<div class="pk-page rqd">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div class="u-minw-0">
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/></svg>
                Repair &amp; maintenance · <span class="rqd-code">{{ $ticket->ticket_code }}</span>
            </p>
            <h1>{{ $ticket->subject }}</h1>
            <p class="pk-hero-sub">{{ $facilityName }}</p>
            <div class="rqd-meta">
                <span class="rqd-status {{ $statusTone }}">{{ $statusLabel }}</span>
                <span class="rqd-meta-item">Sent {{ $sentAt?->format('M j, Y · g:i A') ?? '—' }}</span>
            </div>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.maintenance-tickets.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                All tickets
            </a>
        </div>
    </section>

    @if (session('status'))
        <div class="pk-flash" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="rqd-grid">
        <div class="rqd-side">
            {{-- Issue details --}}
            <section class="rqd-card">
                <h2 class="rqd-card-title">Issue details</h2>
                <dl class="rqd-rows">
                    <div class="rqd-row">
                        <dt>Facility</dt>
                        <dd>{{ $facilityName }}</dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Subject</dt>
                        <dd>{{ $ticket->subject }}</dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Message</dt>
                        <dd class="rqd-pre">{{ $ticket->issue_description }}</dd>
                    </div>
                    <div class="rqd-row">
                        <dt>Sent</dt>
                        <dd>{{ $sentAt?->format('M j, Y · g:i A') ?? '—' }}</dd>
                    </div>
                    @if ($ticket->completed_at)
                        <div class="rqd-row">
                            <dt>Completed</dt>
                            <dd>{{ $ticket->completed_at->format('M j, Y · g:i A') }}</dd>
                        </div>
                    @endif
                    <div class="rqd-row">
                        <dt>Tracking code</dt>
                        <dd class="rqd-mono">{{ $ticket->ticket_code }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Sender --}}
            <section class="rqd-card">
                <h2 class="rqd-card-title">Sender</h2>
                <div class="mtd-sender">
                    <span class="pk-avatar" aria-hidden="true">{{ $initials }}</span>
                    <div class="u-minw-0">
                        <p class="mtd-sender-name">{{ $sender }}</p>
                        @if ($ticket->requester?->email)
                            <p class="mtd-sender-email">{{ $ticket->requester->email }}</p>
                        @endif
                    </div>
                </div>
                <dl class="rqd-rows">
                    <div class="rqd-row">
                        <dt>College</dt>
                        <dd>{{ $ticket->requester?->college_name ?? 'Not specified' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Conversation with the college staff who sent the ticket --}}
            @if ($ticket->requester?->isCollegeStaff())
                @include('layouts.partials.ticket-conversation', [
                    'ticket'    => $ticket,
                    'fetchUrl'  => route('admin.maintenance-tickets.messages', $ticket),
                    'sendUrl'   => route('admin.maintenance-tickets.messages.store', $ticket),
                    'otherSide' => $sender . ($ticket->requester->college_name ? ' (' . $ticket->requester->college_name . ')' : ''),
                ])
            @endif
        </div>

        {{-- Update form --}}
        <section class="rqd-card mtd-form-card">
            <h2 class="rqd-card-title">Update ticket</h2>
            <p class="rqd-muted">The sender is notified when the status changes.</p>

            <form method="POST" action="{{ route('admin.maintenance-tickets.update', $ticket) }}" class="mtd-form">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="pk-errors" role="alert">
                        <strong>Please fix the following:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="pk-field">
                    <label for="status" class="pk-label">Current status <span class="pk-req">*</span></label>
                    <select id="status" name="status" required class="pk-input">
                        <option value="notified" disabled @selected($ticket->status === 'notified')>Notified</option>
                        @foreach (['pending' => 'Pending', 'ongoing' => 'Ongoing', 'success' => 'Success', 'failed' => 'Failed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $ticket->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pk-field">
                    <label for="admin_remarks" class="pk-label">Update for sender</label>
                    <textarea id="admin_remarks" name="admin_remarks" rows="5" maxlength="5000" class="pk-input" placeholder="e.g. Technician scheduled for Monday morning.">{{ old('admin_remarks', $ticket->admin_remarks) }}</textarea>
                    <p class="pk-help">Shown to the sender on their request page.</p>
                </div>

                <div class="pk-form-actions">
                    <a href="{{ route('admin.maintenance-tickets.index') }}" class="pk-btn-outline">Cancel</a>
                    <button type="submit" class="pk-btn">Save update</button>
                </div>
            </form>
        </section>
    </div>
</div>

<style>
    .mtd-sender { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .mtd-sender .pk-avatar { width: 40px; height: 40px; font-size: 13px; flex-shrink: 0; }
    .mtd-sender-name { margin: 0; font-size: 15px; font-weight: 600; color: #0f172a; }
    .mtd-sender-email { margin: 2px 0 0; font-size: 13px; color: #64748B; overflow-wrap: anywhere; }
    .mtd-form-card .rqd-muted { margin: 0 0 18px; }
    .mtd-form { display: flex; flex-direction: column; gap: 16px; }
    .mtd-form .pk-errors { margin-bottom: 0; }
    .mtd-form textarea.pk-input { resize: vertical; min-height: 120px; }
    .mtd-form .pk-form-actions { padding-top: 16px; }
</style>
@endsection
