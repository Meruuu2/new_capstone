@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $photo = $facility->photo_url;
    $isCollege = $facility->owner_type === 'college';
    // The controller ignores owner changes for core and college-owned facilities, so lock them in the UI too.
    $ownerLocked = $isCollege || $facility->isCoreFacility();
    $ownerType = old('owner_type', $facility->owner_type);
    $availability = old('availability_status', $facility->availability_status ?? 'available');
    $isActive = (bool) old('is_active', $facility->is_active);
    $ownerNames = ['gsu' => 'GSU', 'college' => 'College', 'org' => 'Organization'];
    $tones = ['available' => 'is-good', 'unavailable' => 'is-bad', 'maintenance' => 'is-warn'];
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ $photo ?? asset('img/facilities/tiripunan-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                Edit facility
            </p>
            <h1>{{ $facility->name }}</h1>
            <p class="pk-hero-sub">
                {{ $facility->location ?: 'No location set' }}
                @if ($facility->updated_at) · Last updated {{ $facility->updated_at->diffForHumans() }} @endif
            </p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.facilities.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back to Facilities
            </a>
        </div>
    </section>

    <div class="facform">
        <form method="POST" action="{{ route('admin.facilities.update', $facility) }}" class="facform-card" id="facility-edit-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="facform-step">
                <span class="facform-num">01</span>
                <div>
                    <h2>Basic details</h2>
                    <p>The name and place people will see when booking.</p>
                    <div class="facform-fields">
                        <div class="pk-field">
                            <label for="name" class="pk-label">Facility Name <span class="pk-req">*</span></label>
                            <input type="text" name="name" id="name" required class="pk-input" value="{{ old('name', $facility->name) }}">
                            @error('name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pk-field">
                            <label for="location" class="pk-label">Location <span class="pk-req">*</span></label>
                            <input type="text" name="location" id="location" required class="pk-input" value="{{ old('location', $facility->location) }}">
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
                    @if ($ownerLocked)
                        {{-- Disabled fields are not submitted; keep owner_type so validation passes. --}}
                        <input type="hidden" name="owner_type" value="{{ $facility->owner_type }}">
                        <div class="facform-locked">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                            <div>
                                <strong>{{ $ownerNames[$facility->owner_type] ?? ucfirst($facility->owner_type) }}{{ $facility->owner_college ? ' · ' . $facility->owner_college : '' }}</strong>
                                <span>{{ $isCollege ? 'Owned by a college. Ownership and availability are managed by the college.' : 'Core university facility. Ownership cannot be changed.' }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="facform-fields {{ $ownerLocked ? 'hidden' : '' }}">
                        <div class="pk-field">
                            <label for="owner_type" class="pk-label">Owner Type <span class="pk-req">*</span></label>
                            <select id="owner_type" class="pk-input" @if ($ownerLocked) disabled @else name="owner_type" required @endif>
                                <option value="">Select Owner Type</option>
                                <option value="gsu" {{ $ownerType == 'gsu' ? 'selected' : '' }}>GSU (General Services Unit)</option>
                                <option value="college" {{ $ownerType == 'college' ? 'selected' : '' }}>College</option>
                                <option value="org" {{ $ownerType == 'org' ? 'selected' : '' }}>Organization</option>
                            </select>
                            @error('owner_type')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div id="college-field" class="pk-field {{ $ownerType !== 'college' ? 'hidden' : '' }}">
                            <label for="owner_college" class="pk-label">College Name</label>
                            <input type="text" name="owner_college" id="owner_college" class="pk-input" value="{{ old('owner_college', $facility->owner_college) }}" placeholder="e.g., College of Engineering" @if ($ownerLocked) disabled @endif>
                            @error('owner_college')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.facilities.partials.photo-field', ['num' => '03', 'photo' => $photo, 'hasUpload' => (bool) $facility->image_path])

            <div class="facform-step">
                <span class="facform-num">04</span>
                <div>
                    <h2>Description</h2>
                    <p>Capacity, equipment, or anything requesters should know.</p>
                    <div class="pk-field">
                        <label for="description" class="pk-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="pk-input" placeholder="A short description of the venue">{{ old('description', $facility->description) }}</textarea>
                        @error('description')<p class="pk-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="facform-step">
                <span class="facform-num">05</span>
                <div>
                    <h2>Availability</h2>
                    <p>Whether the venue can be booked right now.</p>
                    @if ($isCollege)
                        <input type="hidden" name="availability_status" value="{{ $facility->availability_status }}">
                    @endif
                    <div class="facform-choices" role="radiogroup" aria-label="Availability Status">
                        @foreach ([
                            'available'   => ['#047857', 'Open for reservations.'],
                            'unavailable' => ['#B91C1C', 'Hidden from new bookings.'],
                            'maintenance' => ['#B45309', 'Under repair for now.'],
                        ] as $value => [$dot, $hint])
                            <div class="facform-choice">
                                <input type="radio" id="avail-{{ $value }}" value="{{ $value }}"
                                       @unless ($isCollege) name="availability_status" @else disabled @endunless
                                       {{ $availability === $value ? 'checked' : '' }}>
                                <label for="avail-{{ $value }}" style="--dot:{{ $dot }}"><strong>{{ ucfirst($value) }}</strong><span>{{ $hint }}</span></label>
                            </div>
                        @endforeach
                    </div>
                    @error('availability_status')<p class="pk-error u-mt-8">{{ $message }}</p>@enderror

                    <div class="facform-switch">
                        <label for="is_active" class="facform-switch-text">
                            <strong>Active</strong>
                            <span>Facility enabled in the system</span>
                        </label>
                        {{-- Unchecked boxes send nothing; the hidden 0 lets an admin actually deactivate. --}}
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $isActive ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            <div class="facform-actions">
                <span class="facform-dirty" id="dirty-flag" hidden>Unsaved changes</span>
                <a href="{{ route('admin.facilities.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                    Update Facility
                </button>
            </div>
        </form>

        <aside class="facform-aside" aria-label="Preview">
            <p class="facform-preview-label">Live preview</p>
            <div class="facform-preview">
                <div class="facform-preview-media">
                    <img id="pv-photo" src="{{ $photo }}" alt="" @unless ($photo) hidden @endunless>
                    <svg id="pv-photo-icon" @if ($photo) hidden @endif viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg>
                    <span class="pk-tag" id="pv-owner">Owner</span>
                    <span class="pk-pill is-good" id="pv-status">Available</span>
                </div>
                <div class="facform-preview-body">
                    <h3 id="pv-name">Facility name</h3>
                    <p id="pv-location">Location</p>
                    <p class="is-desc" id="pv-desc"></p>
                </div>
            </div>
            <dl class="facform-meta">
                <div><dt>Status</dt><dd><span class="pk-pill {{ $facility->is_active ? 'is-good' : 'is-neutral' }}">{{ $facility->is_active ? 'Active' : 'Deactivated' }}</span></dd></div>
                <div><dt>Created</dt><dd>{{ optional($facility->created_at)->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>Last updated</dt><dd>{{ optional($facility->updated_at)->format('M j, Y g:i A') ?? '—' }}</dd></div>
            </dl>
            <div class="facform-tip">
                <strong>Tip:</strong> changes apply right away to new reservations. Existing bookings keep their original details.
            </div>
        </aside>
    </div>
</div>

<script>
document.getElementById('owner_type').addEventListener('change', function() {
    document.getElementById('college-field').classList.toggle('hidden', this.value !== 'college');
});

// Live preview + unsaved-changes flag (display only)
(function () {
    const $ = (id) => document.getElementById(id);
    const form = $('facility-edit-form');
    const ownerNames = @json($ownerNames);
    const tones = @json($tones);
    const snapshot = () => new URLSearchParams(new FormData(form)).toString();
    const initial = snapshot();
    let submitting = false;

    function render() {
        $('pv-name').textContent = $('name').value.trim() || 'Facility name';
        $('pv-location').textContent = $('location').value.trim() || 'Location';
        $('pv-desc').textContent = $('description').value.trim() || 'No description yet.';
        const owner = $('owner_type').value;
        const college = $('owner_college').value.trim();
        $('pv-owner').textContent = (ownerNames[owner] || 'Owner') + (owner === 'college' && college ? ' · ' + college : '');
        const picked = document.querySelector('.facform-choice input:checked');
        const status = picked ? picked.value : 'available';
        $('pv-status').className = 'pk-pill ' + (tones[status] || 'is-neutral');
        $('pv-status').textContent = status.charAt(0).toUpperCase() + status.slice(1);
        $('dirty-flag').hidden = snapshot() === initial;
    }
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    form.addEventListener('submit', () => { submitting = true; });
    window.addEventListener('beforeunload', (e) => {
        if (!submitting && snapshot() !== initial) e.preventDefault();
    });
    render();
})();
</script>
@endsection
