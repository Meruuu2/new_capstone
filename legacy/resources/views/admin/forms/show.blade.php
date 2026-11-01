@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Form Submission Review</h4>
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submission ID:</strong> #{{ $submission->id }}
                        </div>
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $submission->type)) }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Control No.:</strong> {{ $submission->payload['control_no'] ?? 'Not assigned' }}
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
                            <strong>Requester Type:</strong> {{ ucfirst($submission->requester_type) }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Requester Unit:</strong> {{ $submission->requester_unit ?? '-' }}
                        </div>
                        <div class="col-md-6">
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
                    </div>

                    <hr>

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

                    @if($submission->status === 'disapproved' && isset($submission->payload['disapproval_reason']))
                        <div class="alert alert-danger">
                            <strong>Disapproval Reason:</strong> {{ $submission->payload['disapproval_reason'] }}
                        </div>
                    @endif

                    <hr>

                    <div class="mt-4">
                        @if($submission->status === 'pending')
                            <form action="{{ route('admin.forms.approve', $submission->id) }}" method="POST" class="d-inline">
                                @csrf
                                <div class="input-group d-inline-flex" style="width: 300px;">
                                    <input type="text" name="control_no" class="form-control" placeholder="Control No. (optional)" value="GSU-{{ str_pad($submission->id, 6, '0', STR_PAD_LEFT) }}">
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </div>
                            </form>

                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#disapproveModal">Disapprove</button>
                        @endif

                        @if($submission->status === 'approved')
                            <a href="{{ route('admin.forms.set-booking', $submission->id) }}" class="btn btn-primary" onclick="return confirm('Convert this approved form to a booking?')">Set Booking</a>
                        @endif

                        @if(in_array($submission->status, ['pending', 'approved']))
                            <a href="{{ route('admin.forms.cancel', $submission->id) }}" class="btn btn-secondary" onclick="return confirm('Cancel this submission?')">Cancel</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disapprove Modal -->
<div class="modal fade" id="disapproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disapprove Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.forms.disapprove', $submission->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason for Disapproval</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disapprove</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
 