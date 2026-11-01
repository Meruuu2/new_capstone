@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Facilities and Utilization Form</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('college.forms.store') }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info">
                            <strong>Date Request:</strong> {{ now()->format('F d, Y') }} (Auto-filled)
                        </div>

                        <div class="form-group mb-3">
                            <label for="date_activity">Date of Activity <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_activity" name="date_activity" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="facility_id">Facility <span class="text-danger">*</span></label>
                            <select class="form-control" id="facility_id" name="facility_id">
                                <option value="">-- Select Facility --</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                                @endforeach
                                <option value="other">Other (specify below)</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="facility_other_group" style="display: none;">
                            <label for="facility_other">Specify Other Facility</label>
                            <input type="text" class="form-control" id="facility_other" name="facility_other">
                        </div>

                        <div class="form-group mb-3">
                            <label for="purpose">Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
                        </div>

                        <h5 class="mt-4 mb-3">Equipment Request</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Monobloc Chair</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_monobloc', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_monobloc', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_monobloc" name="qty_monobloc" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_monobloc', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_monobloc', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Table</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_table', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_table', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_table" name="qty_table" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_table', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_table', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Electric Fan</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_fan', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_fan', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_fan" name="qty_fan" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_fan', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_fan', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Rostrum</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_rostrum', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_rostrum', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_rostrum" name="qty_rostrum" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_rostrum', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_rostrum', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Flag & School Color</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_flag', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_flag', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_flag" name="qty_flag" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_flag', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_flag', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sound System</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_sound', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_sound', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_sound" name="qty_sound" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_sound', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_sound', 10)">+10</button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>LED Wall</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_led', -10)">-10</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_led', -1)">-1</button>
                                    <input type="number" class="form-control text-center" id="qty_led" name="qty_led" min="0" value="0">
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_led', 1)">+1</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="adjustQty('qty_led', 10)">+10</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                            <a href="{{ route('college.dashboard') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adjustQty(fieldId, delta) {
    const input = document.getElementById(fieldId);
    let value = parseInt(input.value) || 0;
    value = Math.max(0, value + delta);
    input.value = value;
}

document.getElementById('facility_id').addEventListener('change', function() {
    const otherGroup = document.getElementById('facility_other_group');
    if (this.value === 'other') {
        otherGroup.style.display = 'block';
    } else {
        otherGroup.style.display = 'none';
    }
});
</script>
@endsection
 