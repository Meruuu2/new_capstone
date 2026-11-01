@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Form Submission Details</h4>
                    <a href="{{ route('college.forms.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Control No.:</strong> {{ $submission->payload['control_no'] ?? 'Pending' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date Request:</strong> {{ $submission->payload['date_request'] ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Requester Name:</strong> {{ $submission->payload['requester_name'] ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Requester Unit:</strong> {{ $submission->payload['requester_unit'] ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date of Activity:</strong> {{ $submission->payload['date_activity'] ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Time:</strong> 
                            {{ $submission->payload['time_range']['start'] ?? '-' }} - {{ $submission->payload['time_range']['end'] ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Facility:</strong>
                        @if($submission->payload['facility_id'])
                            {{ \App\Models\Facility::find($submission->payload['facility_id'])->name ?? 'Unknown' }}
                        @else
                            {{ $submission->payload['facility_other'] ?? 'Other' }}
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Purpose:</strong>
                        <p>{{ $submission->payload['purpose'] ?? '-' }}</p>
                    </div>

                    <div class="mb-3">
                        <strong>Equipment Requested:</strong>
                        <ul>
                            @foreach($submission->payload['equipment'] ?? [] as $item => $qty)
                                @if($qty > 0)
                                    <li>{{ ucfirst(str_replace('_', ' ', $item)) }}: {{ $qty }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <div class="mb-3">
                        <strong>Status:</strong>
                        @php
                            $statusClass = match($submission->status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'disapproved' => 'danger',
                                'converted' => 'info',
                                'cancelled' => 'secondary',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst($submission->status) }}</span>
                    </div>

                    @if($submission->status === 'approved')
                        <div class="alert alert-success">
                            <strong>Your request has been approved!</strong> Please proceed to the GSU office to sign and finalize your request.
                        </div>
                    @endif

                    @if($submission->status === 'disapproved' && isset($submission->payload['disapproval_reason']))
                        <div class="alert alert-danger">
                            <strong>Disapproval Reason:</strong> {{ $submission->payload['disapproval_reason'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
 