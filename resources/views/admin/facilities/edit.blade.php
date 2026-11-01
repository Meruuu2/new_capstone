@extends('layouts.admin')

@section('admin-content')
<div class="bg-white rounded shadow p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Facility</h1>
        <a href="{{ route('admin.facilities.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Facilities</a>
    </div>

    <form method="POST" action="{{ route('admin.facilities.update', $facility) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Facility Name</label>
            <input type="text" name="name" id="name" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   value="{{ old('name', $facility->name) }}">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" name="location" id="location" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   value="{{ old('location', $facility->location) }}">
            @error('location')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
 
        <div>
            <label for="owner_type" class="block text-sm font-medium text-gray-700">Owner Type</label>
            <select name="owner_type" id="owner_type" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    @if($facility->owner_type === 'college') disabled @endif>

                <option value="">Select Owner Type</option>
                <option value="gsu" {{ old('owner_type', $facility->owner_type) == 'gsu' ? 'selected' : '' }}>GSU (General Services Unit)</option>
                <option value="college" {{ old('owner_type', $facility->owner_type) == 'college' ? 'selected' : '' }}>College</option>
                <option value="org" {{ old('owner_type', $facility->owner_type) == 'org' ? 'selected' : '' }}>Organization</option>
            </select>
            @error('owner_type')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="college-field" class="{{ old('owner_type', $facility->owner_type) !== 'college' ? 'hidden' : '' }}">
            <label for="owner_college" class="block text-sm font-medium text-gray-700">College Name</label>
            <input type="text" name="owner_college" id="owner_college"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   value="{{ old('owner_college', $facility->owner_college) }}"
                   placeholder="e.g., College A"
                   @if($facility->owner_type === 'college') disabled @endif>

            @error('owner_college')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description', $facility->description) }}</textarea>
            @error('description')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center mb-4">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $facility->is_active ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active (facility enabled in the system)
            </label>
        </div>

        <div>
            <label for="availability_status" class="block text-sm font-medium text-gray-700">Availability Status</label>
            @if($facility->owner_type === 'college')
                <p class="mt-1 text-sm text-gray-500">
                    This facility is owned by a college ({{ $facility->owner_college ?? 'Unknown college' }}).
                    Availability is controlled by the college.
                </p>
                <input type="hidden" name="availability_status" value="{{ $facility->availability_status }}">
                <p class="mt-1 text-sm font-medium">
                    Current status:
                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                        {{ ucfirst($facility->availability_status) }}
                    </span>
                </p>
            @else
                <select name="availability_status" id="availability_status"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="available" {{ old('availability_status', $facility->availability_status) === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="unavailable" {{ old('availability_status', $facility->availability_status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    <option value="maintenance" {{ old('availability_status', $facility->availability_status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
                @error('availability_status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.facilities.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Update Facility
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('owner_type').addEventListener('change', function() {
    const collegeField = document.getElementById('college-field');
    if (this.value === 'college') {
        collegeField.classList.remove('hidden');
    } else {
        collegeField.classList.add('hidden');
    }
});
</script>
@endsection