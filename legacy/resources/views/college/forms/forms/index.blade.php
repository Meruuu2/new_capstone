@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>My Form Submissions</h4>
                    <a href="{{ route('college.forms.create') }}" class="btn btn-primary">New Facility Request</a>
                </div>
                <div class="card-body">
                    @if($submissions->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Control No.</th>
                                    <th>Date Request</th>
                                    <th>Date Activity</th>
                                    <th>Facility</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td>{{ $submission->payload['control_no'] ?? 'Pending' }}</td>
                                        <td>{{ $submission->payload['date_request'] ?? '-' }}</td>
                                        <td>{{ $submission->payload['date_activity'] ?? '-' }}</td>
                                        <td>
                                            @if($submission->payload['facility_id'])
                                                {{ \App\Models\Facility::find($submission->payload['facility_id'])->name ?? 'Unknown' }}
                                            @else
                                                {{ $submission->payload['facility_other'] ?? 'Other' }}
                                            @endif
                                        </td>
                                        <td>
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
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($submission->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('college.forms.show', $submission->id) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No form submissions yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
 