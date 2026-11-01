<?php
// setup-views.php - Run this to create the directory structure and view files

$basePath = __DIR__ . '/resources/views';

// Create directories
$dirs = [
    'admin/bookings',
    'notifications',
];

foreach ($dirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created: $fullPath\n";
    }
}

// Notifications index view
file_put_contents($basePath . '/notifications/index.blade.php', <<<'EOT'
@extends('layouts.app')

@section('content')
    <div class="fms-card">
        <div class="fms-page-header">
            <h1 class="fms-page-title">Notifications</h1>
        </div>

        @if($notifications->count())
            <div class="fms-table-wrap">
                <table class="fms-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>When</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td>
                                    @if($notification->is_read)
                                        <span class="fms-badge" style="background-color: #e5e7eb; color: #374151;">Read</span>
                                    @else
                                        <span class="fms-badge">New</span>
                                    @endif
                                </td>
                                <td>{{ $notification->title }}</td>
                                <td>{{ Str::limit($notification->message, 80) }}</td>
                                <td>{{ $notification->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if(!$notification->is_read)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="fms-link">Mark as read</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <p class="text-sm text-neutral-600">No notifications yet.</p>
        @endif
    </div>
@endsection
);

echo "Created: notifications/index.blade.php\n";

// Bookings index view
file_put_contents($basePath . '/admin/bookings/index.blade.php', <<<'EOT'
@extends('layouts.admin')

@section('admin-content')
<div class="fms-card">
    <div class="fms-page-header">
        <h1 class="fms-page-title">Bookings (GSU overview)</h1>
    </div>

    @if($bookings->count())
        <div class="fms-table-wrap">
            <table class="fms-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Requester</th>
                        <th>Facility</th>
                        <th>When</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->booking_code }}</td>
                            <td>
                                {{ optional($booking->requester)->name ?? 'Unknown' }}<br>
                                <span class="text-xs text-neutral-500">
                                    {{ $booking->requester_type }} – {{ $booking->requester_unit ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ optional($booking->facility)->name ?? 'Unknown' }}</td>
                            <td>
                                {{ $booking->start_time?->format('M d, Y H:i') }} –
                                {{ $booking->end_time?->format('H:i') }}
                            </td>
                            <td><span class="fms-badge">{{ ucfirst($booking->status) }}</span></td>
                            <td>
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="fms-link">
                                    View / Modify
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @else
        <p class="text-sm text-neutral-600">No bookings yet.</p>
    @endif
</div>
@endsection
EOT
);

echo "Created: admin/bookings/index.blade.php\n";

// Bookings edit view
file_put_contents($basePath . '/admin/bookings/edit.blade.php', <<<'EOT'
@extends('layouts.admin')

@section('admin-content')
<div class="fms-card">
    <div class="fms-page-header border-0 pb-0 mb-4">
        <h1 class="fms-page-title">Booking {{ $booking->booking_code }}</h1>
        <a href="{{ route('admin.bookings.index') }}" class="fms-link">← Back to bookings</a>
    </div>

    @if ($errors->any())
        <div class="fms-alert-error" style="background-color: #fee2e2; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="mb-4 text-sm text-neutral-600">
        This booking was created from an approved and signed request.
        Any changes you make here represent a GSU decision and should be agreed upon by both parties offline.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 text-sm">
        <div>
            <h2 class="mb-2 font-semibold text-neutral-800">Requester</h2>
            <p>{{ optional($booking->requester)->name ?? 'Unknown' }}</p>
            <p class="text-xs text-neutral-500">
                {{ $booking->requester_type }} – {{ $booking->requester_unit ?? 'N/A' }}
            </p>
        </div>
        <div>
            <h2 class="mb-2 font-semibold text-neutral-800">Current details</h2>
            <p>{{ optional($booking->facility)->name ?? 'Unknown' }}</p>
            <p class="text-xs text-neutral-500">
                {{ $booking->start_time?->format('M d, Y H:i') }} –
                {{ $booking->end_time?->format('M d, Y H:i') }}
            </p>
            <p class="text-xs text-neutral-500 mt-1">
                Status: {{ ucfirst($booking->status) }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="fms-label" for="facility_id">Facility</label>
                <select id="facility_id" name="facility_id" class="fms-input" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}"
                            {{ $booking->facility_id === $facility->id ? 'selected' : '' }}>
                            {{ $facility->name }} ({{ $facility->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            @php
                $currentDate = $booking->start_time ? $booking->start_time->toDateString() : now()->toDateString();
                $currentStart = $booking->start_time ? $booking->start_time->format('H:i') : '08:00';
                $currentEnd   = $booking->end_time ? $booking->end_time->format('H:i') : '17:00';

                $details = $booking->additional_details ? json_decode($booking->additional_details, true) : [];
                $equip   = $details['equipment'] ?? [];
            @endphp
            <div>
                <label class="fms-label" for="date_activity">Date of activity</label>
                <input type="date" id="date_activity" name="date_activity" class="fms-input"
                       value="{{ old('date_activity', $currentDate) }}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div>
                <label class="fms-label" for="start_time">Start time</label>
                <input type="time" id="start_time" name="start_time" class="fms-input"
                       value="{{ old('start_time', $currentStart) }}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
            <div>
                <label class="fms-label" for="end_time">End time</label>
                <input type="time" id="end_time" name="end_time" class="fms-input"
                       value="{{ old('end_time', $currentEnd) }}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
        </div>

        <div>
            <label class="fms-label" for="purpose">Purpose</label>
            <textarea id="purpose" name="purpose" rows="3" class="fms-input" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">{{ old('purpose', $booking->purpose) }}</textarea>
        </div>

        <div>
            <h2 class="mb-2 text-sm font-semibold text-neutral-800">Equipment (optional)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $equipmentFields = [
                        'qty_monobloc' => 'Monobloc Chair',
                        'qty_table'    => 'Table',
                        'qty_fan'      => 'Electric Fan',
                        'qty_rostrum'  => 'Rostrum',
                        'qty_flag'     => 'Flag & School Color',
                        'qty_sound'    => 'Sound',
                        'qty_led'      => 'LED Wall',
                    ];
                    $map = [
                        'qty_monobloc' => 'monobloc_chair',
                        'qty_table'    => 'table',
                        'qty_fan'      => 'electric_fan',
                        'qty_rostrum'  => 'rostrum',
                        'qty_flag'     => 'flag',
                        'qty_sound'    => 'sound',
                        'qty_led'      => 'led',
                    ];
                @endphp

                @foreach($equipmentFields as $field => $label)
                    @php
                        $key = $map[$field];
                        $currentQty = $equip[$key] ?? 0;
                    @endphp
                    <div>
                        <label class="fms-label">{{ $label }}</label>
                        <input type="number" min="0" name="{{ $field }}" class="fms-input" style="width: 80px; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;"
                               value="{{ old($field, $currentQty) }}">
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <label class="fms-label" for="reason">Reason for change (required)</label>
            <textarea id="reason" name="reason" rows="3" class="fms-input" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;"
                      placeholder="Explain why GSU is changing the facility / schedule. This will appear in the requester's notifications.">{{ old('reason') }}</textarea>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="fms-btn-primary">
                Save changes & notify requester
            </button>
        </div>
    </form>

    <hr style="margin: 24px 0; border: 1px solid #000;">
    
    <div>
        <h2 class="mb-3 text-sm font-semibold text-neutral-800">Cancel booking (GSU override)</h2>
        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="space-y-2"
              onsubmit="return confirm('Cancel this booking? This cannot be undone.');">
            @csrf
            <label class="fms-label" for="cancel_reason">Cancellation reason</label>
            <textarea id="cancel_reason" name="reason" rows="2" class="fms-input" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;"
                      placeholder="Reason for cancellation (visible to requester)"></textarea>
            <button type="submit" class="fms-btn-danger">
                Cancel booking
            </button>
        </form>
    </div>
</div>
@endsection
);

echo "Created: admin/bookings/edit.blade.php\n";
echo "Setup complete!\n";