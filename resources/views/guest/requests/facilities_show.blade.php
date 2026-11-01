@extends('layouts.guest')

@section('guest-content')
<div class="bg-white rounded shadow p-6">
    <div class="mb-4">
        <a href="{{ route('guest.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm font-medium transition">
            ← Back to Dashboard
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $payload['control_no'] ?? 'Pending Request' }}</h1>
        <a href="{{ route('guest.requests.facilities.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 text-sm">
            Back to requests
        </a>
    </div>

    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <dt class="font-semibold text-gray-700">Requested On</dt>
            <dd class="text-gray-800">{{ $submission->created_at?->format('Y-m-d H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-700">Status</dt>
            <dd class="text-gray-800">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($submission->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($submission->status === 'pending_payment') bg-orange-100 text-orange-800
                    @elseif($submission->status === 'approved') bg-green-100 text-green-800
                    @elseif($submission->status === 'reserved') bg-blue-100 text-blue-800
                    @elseif($submission->status === 'disapproved') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                </span>
                @if(in_array($submission->status, ['pending', 'pending_payment']))
                    <form method="POST"
                          action="{{ route('guest.requests.facilities.cancel', $submission) }}"
                          class="inline-block ml-3"
                          data-confirm="Cancel this request? This cannot be undone." data-confirm-title="Cancel request?" data-confirm-action="Cancel request" data-confirm-tone="danger">
                        @csrf
                        <button type="submit" class="px-2 py-1 border border-gray-400 rounded text-xs hover:bg-gray-100">
                            Cancel request
                        </button>
                    </form>
                @endif
            </dd>
        </div>
        @if ($submission->status === 'disapproved' && !empty($payload['disapproval_reason']))
            <div class="md:col-span-2">
                <dt class="font-semibold text-red-800">Reason for disapproval</dt>
                <dd class="text-red-900">{{ $payload['disapproval_reason'] }}</dd>
            </div>
        @endif

        {{-- Payment Information --}}
        @if($submission->payment_attachment)
            <div class="md:col-span-2 p-4 bg-blue-50 border border-blue-200 rounded">
                <dt class="font-semibold text-gray-700 mb-2">Payment Information</dt>
                <dd class="text-gray-800">
                    <p class="mb-2">
                        <strong>Status:</strong>
                        @if($submission->payment_status === 'payment_uploaded')
                            <span class="text-orange-600">Pending Verification</span>
                        @elseif($submission->payment_status === 'payment_verified')
                            <span class="text-green-600">Verified ✓</span>
                        @else
                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $submission->payment_status)) }}</span>
                        @endif
                    </p>
                    <a href="{{ route('payment-receipts.show', $submission) }}"
                       class="inline-block px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Download Payment Receipt
                    </a>
                </dd>
            </div>
        @endif

        <div>
            <dt class="font-semibold text-gray-700">Date of Activity</dt>
            <dd class="text-gray-800">{{ $payload['date_activity'] ?? '-' }}@if (!empty($payload['date_end'])) → {{ $payload['date_end'] }} <span class="text-gray-500">(multi-day, same time each day)</span>@endif</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-700">Time</dt>
            <dd class="text-gray-800">
                @if(isset($payload['time_range']))
                    {{ $payload['time_range']['start'] ?? '' }} – {{ $payload['time_range']['end'] ?? '' }}
                @else
                    -
                @endif
            </dd>
        </div>
        <div class="md:col-span-2">
            <dt class="font-semibold text-gray-700">Venue</dt>
            <dd class="text-gray-800">
                @if($facilities->isNotEmpty())
                    {{ $facilities->map(fn ($venue) => $venue->name . ($venue->location ? ' (' . $venue->location . ')' : ''))->join(', ') }}
                @else
                    Not specified
                @endif
            </dd>
        </div>
        <div class="md:col-span-2">
            <dt class="font-semibold text-gray-700">Purpose</dt>
            <dd class="text-gray-800">{{ $payload['purpose'] ?? '-' }}</dd>
        </div>
        <div class="md:col-span-2">
            <dt class="font-semibold text-gray-700">Noted By</dt>
            <dd class="text-gray-800">{{ $payload['noted_by']['name'] ?? '-' }}</dd>
        </div>
    </dl>

    @if(!empty($payload['equipment']))
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Requested Equipment</h2>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($payload['equipment'] as $item => $qty)
                        @if($qty > 0)
                            <tr>
                                <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $item) }}</td>
                                <td class="px-4 py-2 text-right">{{ $qty }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($payload['attachments']))
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Attachments</h2>
            <ul class="space-y-2">
                @foreach($payload['attachments'] as $attachment)
                    @php($attachmentIndex = $loop->index)
                    <li>
                        <a href="{{ route('payment-attachments.show', [$submission, $attachmentIndex]) }}" class="text-blue-600 hover:underline">
                            Download attachment {{ $attachmentIndex + 1 }} ({{ strtoupper(pathinfo($attachment, PATHINFO_EXTENSION)) }})
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
