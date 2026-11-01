@extends('layouts.admin')

@section('admin-content')
<div class="bg-white rounded shadow p-6">
    <h1 class="text-2xl font-semibold mb-4">Add Signatory</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-800 border border-red-200 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.signatories.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
            <select name="type" id="type" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                <option value="">Select type</option>
                <option value="dean" {{ old('type') === 'dean' ? 'selected' : '' }}>Dean</option>
                <option value="program_head" {{ old('type') === 'program_head' ? 'selected' : '' }}>Program Head</option>
                <option value="org_president" {{ old('type') === 'org_president' ? 'selected' : '' }}>Org President</option>
                <option value="org_adviser" {{ old('type') === 'org_adviser' ? 'selected' : '' }}>Org Adviser</option>
                <option value="gsu_head" {{ old('type') === 'gsu_head' ? 'selected' : '' }}>GSU Head</option>
            </select>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                   value="{{ old('name') }}">
        </div>

        <div>
            <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
            <input type="text" name="unit" id="unit"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"
                   value="{{ old('unit') }}" placeholder="College A / Org A / GSU">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
            <label for="is_active" class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.signatories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save
            </button>
        </div>
    </form>
</div>
@endsection
