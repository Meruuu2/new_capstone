@extends('layouts.college') {{-- Or layouts.org depending on your folder --}}

@section('college-content')
<div class="bg-white rounded shadow p-6">
    <h1 class="text-2xl font-semibold mb-4">Facilities Utilization Request</h1>

    <p class="text-gray-700 mb-4">
        Submit a request for facility and equipment usage. Date of request will be recorded automatically after submission.
    </p>

    {{-- 1. SAFELY check for Approved College Facilities --}}
    @if(isset($collegeFacilities) && $collegeFacilities->isNotEmpty())
        <div class="mb-4 p-3 bg-blue-50 text-blue-800 border border-blue-200 rounded">
            <h2 class="font-semibold text-md mb-2">Approved College Facilities</h2>
            <ul class="list-disc list-inside text-sm">
                @foreach($collegeFacilities as $facility)
                    <li>{{ $facility->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-800 border border-red-200 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('college.requests.facilities.store') }}" class="space-y-6">
        @csrf

        {{-- Date & Time --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="date_activity" class="block text-sm font-medium text-gray-700">Date of Activity</label>
                <input type="date" name="date_activity" id="date_activity" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white"
                       value="{{ old('date_activity') }}">
            </div>
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="time" name="start_time" id="start_time" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white"
                       value="{{ old('start_time') }}">
            </div>
            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="time" name="end_time" id="end_time" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white"
                       value="{{ old('end_time') }}">
            </div>
        </div>

        {{-- Facility / Venue --}}
        <div>
            <label for="facility_id" class="block text-sm font-medium text-gray-700">Venue</label>
            <select name="facility_id" id="facility_id" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">
                <option value="">Select a facility</option>
                {{-- 2. SAFELY check for Core Facilities --}}
                @if(!empty($coreFacilities))
                    @foreach($coreFacilities as $facility)
                        <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                            {{ $facility->name }} — Estimated seating: {{ \App\Support\VenueRates::estimatedSeatingCapacityFor($facility->name) ?? 'Not listed' }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- Noted by Signatory --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Noted by</h2>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select signatory</label>
            <select name="noted_signatory_id"
                    id="noted_signatory_id"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">

                <option value="">-- Select a signatory --</option>
                <option value="custom" {{ old('noted_signatory_id') == 'custom' ? 'selected' : '' }}>Custom name (type below)</option>

                {{-- 3. SAFELY check for College Signatories --}}
                @if(!empty($deans))
                    @foreach($deans as $s)
                        <option value="dean:{{ $s->id }}" {{ old('noted_signatory_id') == "dean:{$s->id}" ? 'selected' : '' }}>
                            Dean – {{ $s->name }} ({{ $s->unit }})
                        </option>
                    @endforeach
                @endif

                @if(!empty($programHeads))
                    @foreach($programHeads as $s)
                        <option value="program_head:{{ $s->id }}" {{ old('noted_signatory_id') == "program_head:{$s->id}" ? 'selected' : '' }}>
                            Program Head – {{ $s->name }} ({{ $s->unit }})
                        </option>
                    @endforeach
                @endif

                {{-- 4. SAFELY check for Org Signatories --}}
                @if(!empty($presidents))
                    @foreach($presidents as $s)
                        <option value="president:{{ $s->id }}" {{ old('noted_signatory_id') == "president:{$s->id}" ? 'selected' : '' }}>
                            President – {{ $s->name }}
                        </option>
                    @endforeach
                @endif

                @if(!empty($advisers))
                    @foreach($advisers as $s)
                        <option value="adviser:{{ $s->id }}" {{ old('noted_signatory_id') == "adviser:{$s->id}" ? 'selected' : '' }}>
                            Adviser – {{ $s->name }}
                        </option>
                    @endforeach
                @endif
            </select>

            <div class="mt-3 hidden" id="custom-noted-input">
                <label class="block text-sm font-medium text-gray-700">Custom name</label>
                <input type="text" name="noted_signatory_custom"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white"
                       placeholder="Enter specific name"
                       value="{{ old('noted_signatory_custom') }}">
            </div>
        </div>

        {{-- Purpose --}}
        <div>
            <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
            <textarea name="purpose" id="purpose" rows="3" required
                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-white">{{ old('purpose') }}</textarea>
        </div>

        {{-- Equipment --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Facilities / Equipment to be used</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $equipmentFields = [
                        'qty_monobloc' => 'Monobloc Chair',
                        'qty_table'    => 'Table',
                        'qty_fan'      => 'Electric Fan',
                        'qty_rostrum'  => 'Rostrum',
                        'qty_flag'     => 'Flag & School Color',
                        'qty_sound'    => 'Sound',
                        'qty_led'      => 'LED Wall',
                    ];
                @endphp

                @foreach($equipmentFields as $name => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <div class="mt-1 flex items-center space-x-2">
                            <button type="button" class="px-2 py-1 border rounded text-sm bg-white"
                                    onclick="adjustQty('{{ $name }}', -10)">-10</button>
                            <button type="button" class="px-2 py-1 border rounded text-sm bg-white"
                                    onclick="adjustQty('{{ $name }}', -1)">-1</button>
                            <input type="number" name="{{ $name }}" id="{{ $name }}" min="0"
                                   class="w-20 border border-gray-300 rounded-md py-1 px-2 text-center bg-white"
                                   value="{{ old($name, 0) }}">
                            <button type="button" class="px-2 py-1 border rounded text-sm bg-white"
                                    onclick="adjustQty('{{ $name }}', 1)">+1</button>
                            <button type="button" class="px-2 py-1 border rounded text-sm bg-white"
                                    onclick="adjustQty('{{ $name }}', 10)">+10</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end space-x-4">
            <a href="{{ url()->previous() }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 bg-white">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Submit Request
            </button>
        </div>
    </form>
</div>

<script>
function adjustQty(fieldId, delta) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    let value = parseInt(input.value || '0', 10);
    value += delta;
    if (value < 0) value = 0;
    input.value = value;
}

(function() {
    const notedSelect = document.getElementById('noted_signatory_id');
    const customInput = document.getElementById('custom-noted-input');

    function updateNotedUI() {
        if (!notedSelect) return;
        const val = notedSelect.value;
        if (val === 'custom') {
            customInput.classList.remove('hidden');
        } else {
            customInput.classList.add('hidden');
        }
    }

    if (notedSelect) {
        notedSelect.addEventListener('change', updateNotedUI);
        updateNotedUI();
    }
})();
</script>
@endsection
