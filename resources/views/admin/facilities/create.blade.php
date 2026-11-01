@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/tiripunan-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New facility
            </p>
            <h1>Add New Facility</h1>
            <p class="pk-hero-sub">Register a venue so it shows up in reservations, the calendar and monthly records.</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.facilities.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                All facilities
            </a>
        </div>
    </section>

    <div class="facform">
        <form method="POST" action="{{ route('admin.facilities.store') }}" class="facform-card">
            @csrf

            <div class="facform-step">
                <span class="facform-num">01</span>
                <div>
                    <h2>Basic details</h2>
                    <p>The name and place people will see when booking.</p>
                    <div class="facform-fields">
                        <div class="pk-field">
                            <label for="name" class="pk-label">Facility Name <span class="pk-req">*</span></label>
                            <input type="text" name="name" id="name" required class="pk-input" value="{{ old('name') }}" placeholder="e.g., Busalian Hall">
                            @error('name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pk-field">
                            <label for="location" class="pk-label">Location <span class="pk-req">*</span></label>
                            <input type="text" name="location" id="location" required class="pk-input" value="{{ old('location') }}" placeholder="e.g., Main Campus, Sibalom">
                            @error('location')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="facform-step">
                <span class="facform-num">02</span>
                <div>
                    <h2>Ownership</h2>
                    <p>Which office manages the venue and approves its requests.</p>
                    <div class="facform-fields">
                        <div class="pk-field">
                            <label for="owner_type" class="pk-label">Owner Type <span class="pk-req">*</span></label>
                            <select name="owner_type" id="owner_type" required class="pk-input">
                                <option value="">Select Owner Type</option>
                                <option value="gsu" {{ old('owner_type') == 'gsu' ? 'selected' : '' }}>GSU (General Services Unit)</option>
                                <option value="college" {{ old('owner_type') == 'college' ? 'selected' : '' }}>College</option>
                                <option value="org" {{ old('owner_type') == 'org' ? 'selected' : '' }}>Organization</option>
                            </select>
                            @error('owner_type')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div id="college-field" class="pk-field hidden">
                            <label for="owner_college" class="pk-label">College Name</label>
                            <input type="text" name="owner_college" id="owner_college" class="pk-input" value="{{ old('owner_college') }}" placeholder="e.g., College of Engineering">
                            @error('owner_college')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="facform-step">
                <span class="facform-num">03</span>
                <div>
                    <h2>Description</h2>
                    <p>Capacity, equipment, or anything requesters should know.</p>
                    <div class="pk-field">
                        <label for="description" class="pk-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="pk-input" placeholder="A short description of the venue">{{ old('description') }}</textarea>
                        @error('description')<p class="pk-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="facform-step">
                <span class="facform-num">04</span>
                <div>
                    <h2>Availability</h2>
                    <p>Whether the venue can be booked right now.</p>
                    @php $availability = old('availability_status', 'available'); @endphp
                    <div class="facform-choices" role="radiogroup" aria-label="Availability Status">
                        <div class="facform-choice">
                            <input type="radio" name="availability_status" id="avail-available" value="available" {{ $availability === 'available' ? 'checked' : '' }}>
                            <label for="avail-available" style="--dot:#047857"><strong>Available</strong><span>Open for reservations.</span></label>
                        </div>
                        <div class="facform-choice">
                            <input type="radio" name="availability_status" id="avail-unavailable" value="unavailable" {{ $availability === 'unavailable' ? 'checked' : '' }}>
                            <label for="avail-unavailable" style="--dot:#B91C1C"><strong>Unavailable</strong><span>Hidden from new bookings.</span></label>
                        </div>
                        <div class="facform-choice">
                            <input type="radio" name="availability_status" id="avail-maintenance" value="maintenance" {{ $availability === 'maintenance' ? 'checked' : '' }}>
                            <label for="avail-maintenance" style="--dot:#B45309"><strong>Maintenance</strong><span>Under repair for now.</span></label>
                        </div>
                    </div>
                    @error('availability_status')<p class="pk-error u-mt-8">{{ $message }}</p>@enderror

                    <div class="facform-switch">
                        <label for="is_active" class="facform-switch-text">
                            <strong>Active</strong>
                            <span>Facility enabled in the system</span>
                        </label>
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    </div>
                </div>
            </div>

            <div class="facform-actions">
                <a href="{{ route('admin.facilities.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                    Create Facility
                </button>
            </div>
        </form>

        <aside class="facform-aside" aria-label="Preview">
            <p class="facform-preview-label">Live preview</p>
            <div class="facform-preview">
                <div class="facform-preview-media">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg>
                    <span class="pk-tag" id="pv-owner">Owner</span>
                    <span class="pk-pill is-good" id="pv-status">Available</span>
                </div>
                <div class="facform-preview-body">
                    <h3 id="pv-name">Facility name</h3>
                    <p id="pv-location">Location</p>
                    <p class="is-desc" id="pv-desc">A short description of the venue will appear here.</p>
                </div>
            </div>
            <div class="facform-tip">
                <strong>Tip:</strong> fields marked <span class="pk-req u-text-danger">*</span> are required. Details can be changed later from <em>All facilities → Edit</em>.
            </div>
        </aside>
    </div>
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

// Live preview (display only)
(function () {
    const $ = (id) => document.getElementById(id);
    const ownerNames = { gsu: 'GSU', college: 'College', org: 'Organization' };
    const tones = { available: 'is-good', unavailable: 'is-bad', maintenance: 'is-warn' };
    function render() {
        $('pv-name').textContent = $('name').value.trim() || 'Facility name';
        $('pv-location').textContent = $('location').value.trim() || 'Location';
        $('pv-desc').textContent = $('description').value.trim() || 'A short description of the venue will appear here.';
        const owner = $('owner_type').value;
        const college = $('owner_college').value.trim();
        $('pv-owner').textContent = (ownerNames[owner] || 'Owner') + (owner === 'college' && college ? ' · ' + college : '');
        const picked = document.querySelector('input[name="availability_status"]:checked');
        const status = picked ? picked.value : 'available';
        $('pv-status').className = 'pk-pill ' + (tones[status] || 'is-neutral');
        $('pv-status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }
    document.querySelector('.facform-card').addEventListener('input', render);
    document.querySelector('.facform-card').addEventListener('change', render);
    if ($('owner_type').value === 'college') $('college-field').classList.remove('hidden');
    render();
})();
</script>
@endsection
