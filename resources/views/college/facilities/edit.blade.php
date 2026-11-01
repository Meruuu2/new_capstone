@extends('layouts.college')

@section('college-content')
<div class="bg-white rounded shadow p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Facility</h1>
        <p class="text-gray-600">Editing facility for: <strong>{{ $collegeName }}</strong></p>
        <a href="{{ route('college.facilities.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Facilities</a>
    </div>

    <form method="POST" action="{{ route('college.facilities.update', $facility) }}" class="space-y-6">
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
            <select name="availability_status" id="availability_status"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="available" {{ old('availability_status', $facility->availability_status ?? 'available') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ old('availability_status', $facility->availability_status ?? 'available') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                <option value="maintenance" {{ old('availability_status', $facility->availability_status ?? 'available') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
            @error('availability_status')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('college.facilities.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Update Facility
            </button>
        </div>
    </form>
</div>
@endsection