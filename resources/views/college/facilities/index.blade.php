@extends('layouts.college')

@section('college-content')
<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">My College Facilities</h1>
        <a href="{{ route('college.facilities.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add New Facility
        </a>
    </div>

    <p class="text-gray-600 mb-4">Showing facilities for: <strong>{{ $collegeName }}</strong></p>

    @if($facilities->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($facilities as $facility)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $facility->name }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($facility->description, 50) }}</div>
                                @include('partials.facility-seating-capacity', ['facility' => $facility])
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $facility->location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $status = $facility->availability_status ?? 'available';
                                @endphp
                                @if($status === 'available')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Available
                                    </span>
                                @elseif($status === 'maintenance')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Maintenance
                                    </span>
                                @elseif($status === 'unavailable')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Unavailable
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($status) }}
                                    </span>
                                @endif
                                @if(!$facility->is_active)
                                    <div class="text-xs text-gray-400 mt-1">Pending GSU verification</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('college.facilities.edit', $facility) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                @if($facility->is_active)
                                    <form action="{{ route('college.facilities.deactivate', $facility) }}" method="POST" class="inline" data-confirm="Deactivate {{ $facility->name }}?" data-confirm-title="Deactivate facility?" data-confirm-action="Deactivate facility" data-confirm-tone="danger">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="fac-action-btn is-danger" aria-label="Deactivate {{ $facility->name }}" title="Deactivate facility">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $facilities->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">No facilities found for your college.</p>
            <a href="{{ route('college.facilities.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add Your First Facility
            </a>
        </div>
    @endif
</div>
@endsection 