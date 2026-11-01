@if($submissions->count() > 0)
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Requester</th>
                <th>Type</th>
                <th>Date Activity</th>
                <th>Facility</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr>
                    <td>{{ $submission->id }}</td>
                    <td>
                        {{ $submission->payload['requester_name'] ?? '-' }}
                        <br><small class="text-muted">{{ $submission->requester_type }} - {{ $submission->requester_unit ?? '-' }}</small>
                    </td>
                    <td>{{ ucfirst(str_replace('_', ' ', $submission->type)) }}</td>
                    <td>{{ $submission->payload['date_activity'] ?? '-' }}</td>
                    <td>
                        @if($submission->payload['facility_id'])
                            {{ \App\Models\Facility::find($submission->payload['facility_id'])->name ?? 'Unknown' }}
                        @else
                            {{ $submission->payload['facility_other'] ?? 'Other' }}
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.forms.show', $submission->id) }}" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr> 
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted">No submissions in this category.</p>
@endif
