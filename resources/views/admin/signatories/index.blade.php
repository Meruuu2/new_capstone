@extends('layouts.admin')

@section('admin-content')
<div class="bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Signatories</h1>
        <a href="{{ route('admin.signatories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Add Signatory
        </a>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 text-green-800 border border-green-200 rounded">
            {{ session('status') }}
        </div>
    @endif

    @if($signatories->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Active</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($signatories as $s)
                        <tr>
                            <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $s->type)) }}</td>
                            <td class="px-4 py-2">{{ $s->name }}</td>
                            <td class="px-4 py-2">{{ $s->unit ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $s->is_active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.signatories.edit', $s) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.signatories.destroy', $s) }}" class="inline" data-confirm="Delete this signatory?" data-confirm-title="Delete signatory?" data-confirm-action="Delete signatory" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $signatories->links() }}
        </div>
    @else
        <p class="text-gray-600">No signatories found.</p>
    @endif
</div>
@endsection
