@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $roles = [
        'admin'         => ['label' => 'Admin',              'desc' => 'Full GSU access',          'icon' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>'],
        'college_staff' => ['label' => 'College Staff',      'desc' => 'Books for a college',      'icon' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/>'],
        'org_staff'     => ['label' => 'Organization Staff', 'desc' => 'Books for an org unit',    'icon' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/>'],
        // Guests no longer get accounts: they request facilities from the public form on the home page
    ];
    // Same page edits an account when the controller passes $editUser
    $editUser = $editUser ?? null;
    $editing  = (bool) $editUser;
    $isSelf   = $editing && $editUser->id === auth()->id();
    if ($editing && $editUser->role === 'guest') {
        $roles['guest'] = ['label' => 'Guest', 'desc' => 'Legacy external account', 'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>'];
    }
    // ?role=admin (from "Add admin staff") pre-selects the Admin card
    $currentRole = old('role', $editUser?->role ?? (request('role') === 'admin' ? 'admin' : null));
    $adminPositions = ['GSU Head', 'Administrative Officer', 'Facilities Coordinator', 'Staff'];
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg>
                Administration
            </p>
            <h1>{{ $editing ? 'Edit User' : 'Add New User' }}</h1>
            <p class="pk-hero-sub">{{ $editing ? "Update {$editUser->name}'s details, sign-in and role." : 'Create an account and choose what the person can do in the system.' }}</p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route('admin.users.index') }}" class="pk-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/></svg>
                All users
            </a>
        </div>
    </section>

    @if ($errors->any())
        <div class="ucf-errors" role="alert">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ucf">
        <form method="POST" action="{{ $editing ? route('admin.users.update', $editUser) : route('admin.users.store') }}" class="ucf-card" id="user-form">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="ucf-step">
                <span class="ucf-num">01</span>
                <div>
                    <h2>Profile</h2>
                    <p class="ucf-intro">Enter the person's full name and the last name they will use to check request status.</p>
                    <div class="ucf-fields">
                        <div class="pk-field">
                            <label for="name" class="pk-label">Name <span class="pk-req">*</span></label>
                            <input type="text" name="name" id="name" required maxlength="255" pattern="\p{L}+([ '’\-]\p{L}+)*" title="Use letters, spaces, hyphens, or apostrophes only." class="pk-input" value="{{ old('name', $editUser?->name) }}" autocomplete="off">
                            @error('name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pk-field">
                            <label for="last_name" class="pk-label">Last name for tracking <span class="pk-req">*</span></label>
                            <input type="text" name="last_name" id="last_name" required maxlength="120" pattern="\p{L}+([ '’\-]\p{L}+)*" title="Use letters, spaces, hyphens, or apostrophes only." class="pk-input" value="{{ old('last_name', $editUser?->last_name) }}" autocomplete="family-name">
                            @error('last_name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        @include('layouts.partials.contact-number-input', ['value' => old('contact_number', $editUser?->contact_number)])
                        <div class="pk-field" style="grid-column: 1 / -1;">
                            <label for="email" class="pk-label">Email <span class="pk-req">*</span></label>
                            <input type="email" name="email" id="email" required class="pk-input" value="{{ old('email', $editUser?->email) }}" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ucf-step">
                <span class="ucf-num">02</span>
                <div>
                    <h2>Password</h2>
                    <p class="ucf-intro">Use 8–94 characters with uppercase and lowercase letters and at least one special character.{{ $editing ? ' Leave blank to keep the current password.' : '' }}</p>
                    <div class="ucf-fields">
                        <div class="pk-field">
                            <label for="password" class="pk-label">{{ $editing ? 'New password' : 'Password' }} @unless ($editing)<span class="pk-req">*</span>@endunless</label>
                            <div class="ucf-pass">
                                <input type="password" name="password" id="password" @unless ($editing) required @endunless minlength="8" maxlength="94" class="pk-input" autocomplete="new-password">
                                <button type="button" class="ucf-eye" data-target="password" aria-label="Show password" aria-pressed="false">
                                    <svg class="is-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="is-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A10 10 0 0112 5c6.5 0 10 7 10 7a17 17 0 01-3.2 4.1M6.6 6.6A17 17 0 002 12s3.5 7 10 7a9.7 9.7 0 005.4-1.6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="pk-field">
                            <label for="password_confirmation" class="pk-label">Confirm {{ $editing ? 'new password' : 'Password' }} @unless ($editing)<span class="pk-req">*</span>@endunless</label>
                            <div class="ucf-pass">
                                <input type="password" name="password_confirmation" id="password_confirmation" @unless ($editing) required @endunless minlength="8" maxlength="94" class="pk-input" autocomplete="new-password">
                                <button type="button" class="ucf-eye" data-target="password_confirmation" aria-label="Show password" aria-pressed="false">
                                    <svg class="is-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="is-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A10 10 0 0112 5c6.5 0 10 7 10 7a17 17 0 01-3.2 4.1M6.6 6.6A17 17 0 002 12s3.5 7 10 7a9.7 9.7 0 005.4-1.6"/></svg>
                                </button>
                            </div>
                            <p class="ucf-match" id="pass-match" aria-live="polite"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ucf-step u-border-b-0">
                <span class="ucf-num">03</span>
                <div>
                    <h2>Role <span class="pk-req u-text-danger">*</span></h2>
                    <p class="ucf-intro">{{ $isSelf ? "You can't change the role of your own account." : 'Decides which portal the person lands in.' }}</p>
                    <div class="ucf-roles" role="radiogroup" aria-label="Role">
                        @foreach ($roles as $value => $role)
                            <div class="ucf-role">
                                <input type="radio" name="role" id="role-{{ $value }}" value="{{ $value }}" required {{ $currentRole === $value ? 'checked' : '' }} {{ $isSelf && $value !== 'admin' ? 'disabled' : '' }}>
                                <label for="role-{{ $value }}">
                                    <span class="ucf-role-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $role['icon'] !!}</svg></span>
                                    <strong>{{ $role['label'] }}</strong>
                                    <span class="ucf-role-desc">{{ $role['desc'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    {{-- Admin team: title within GSU (everyone with the Admin role has full access) --}}
                    <div id="admin-field" class="pk-field ucf-unit hidden">
                        <label for="admin_position" class="pk-label">Position in the admin team</label>
                        <input type="text" name="position" id="admin_position" maxlength="120" class="pk-input" list="admin-position-list"
                               value="{{ old('position', $editUser?->role === 'admin' ? $editUser?->position : '') }}" placeholder="Pick one or type a title" autocomplete="off">
                        <datalist id="admin-position-list">
                            @foreach ($adminPositions as $p)<option value="{{ $p }}"></option>@endforeach
                        </datalist>
                        <div class="uu-chips" aria-label="Common positions">
                            @foreach ($adminPositions as $p)
                                <button type="button" class="uu-chip" data-position="{{ $p }}">{{ $p }}</button>
                            @endforeach
                        </div>
                        <p class="pk-help">Shown on the Admin team list. Every admin has full GSU access.</p>
                    </div>

                    <div id="college-field" class="pk-field ucf-unit hidden">
                        <label for="college_name" class="pk-label">College Name</label>
                        <input type="text" name="college_name" id="college_name" class="pk-input" value="{{ old('college_name', $editUser?->college_name) }}">
                    </div>

                    <div id="org-field" class="pk-field ucf-unit hidden">
                        <label for="organization_name" class="pk-label">Organization Name</label>
                        <input type="text" name="organization_name" id="organization_name" class="pk-input" value="{{ old('organization_name', $editUser?->organization_name) }}">
                    </div>

                    <div id="guest-field" class="pk-field ucf-unit hidden">
                        <label for="organization_name_guest" class="pk-label">Organization Name (External)</label>
                        <input type="text" name="organization_name" id="organization_name_guest" class="pk-input" value="{{ old('organization_name', $editUser?->organization_name) }}" placeholder="e.g., ABC Company, XYZ Foundation">
                        <p class="pk-help">Enter the external organization or company name</p>
                    </div>
                </div>
            </div>

            <div class="ucf-actions">
                <a href="{{ route('admin.users.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" class="pk-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg>
                    {{ $editing ? 'Save changes' : 'Create User' }}
                </button>
            </div>
        </form>

        <aside class="ucf-aside" aria-label="Account preview">
            <p class="ucf-aside-label">Account preview</p>
            <div class="ucf-id">
                <div class="ucf-id-top">
                    <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="">
                    <div>
                        <strong>University of Antique</strong>
                        <span>Facility Management System</span>
                    </div>
                </div>
                <div class="ucf-id-body">
                    <span class="ucf-id-avatar" id="id-avatar" aria-hidden="true">?</span>
                    <p class="ucf-id-name" id="id-name">New user</p>
                    <p class="ucf-id-email" id="id-email">email@example.com</p>
                    <span class="ucf-id-role is-empty" id="id-role">No role yet</span>
                    <p class="ucf-id-unit" id="id-unit"></p>
                </div>
                <div class="ucf-id-foot">Access card preview</div>
            </div>
        </aside>
    </div>
</div>

<script>
(function () {
    const collegeField = document.getElementById('college-field');
    const orgField = document.getElementById('org-field');
    const guestField = document.getElementById('guest-field');
    const adminField = document.getElementById('admin-field');

    // Same show/hide rules as before, driven by the role cards instead of a select.
    // Hidden unit inputs are disabled so they aren't submitted (two of them share the name organization_name).
    function syncRoleFields(value) {
        syncVisibility(value);
        adminField.classList.toggle('hidden', value !== 'admin');
        [collegeField, orgField, guestField, adminField].forEach(f => f.querySelectorAll('input').forEach(i => { i.disabled = f.classList.contains('hidden'); }));
    }

    // Admin position chips fill the field; the matching chip stays highlighted
    const positionInput = document.getElementById('admin_position');
    const positionChips = [...adminField.querySelectorAll('.uu-chip')];
    const syncChips = () => positionChips.forEach(c => c.classList.toggle('is-on', c.dataset.position.toLowerCase() === positionInput.value.trim().toLowerCase()));
    positionChips.forEach(c => c.addEventListener('click', () => { positionInput.value = c.dataset.position; positionInput.dispatchEvent(new Event('input', { bubbles: true })); }));
    positionInput.addEventListener('input', syncChips);
    syncChips();
    function syncVisibility(value) {
        if (value === 'college_staff') {
            collegeField.classList.remove('hidden');
            orgField.classList.add('hidden');
            guestField.classList.add('hidden');
        } else if (value === 'org_staff') {
            collegeField.classList.add('hidden');
            orgField.classList.remove('hidden');
            guestField.classList.add('hidden');
        } else if (value === 'guest') {
            collegeField.classList.add('hidden');
            orgField.classList.add('hidden');
            guestField.classList.remove('hidden');
        } else {
            collegeField.classList.add('hidden');
            orgField.classList.add('hidden');
            guestField.classList.add('hidden');
        }
    }

    const form = document.getElementById('user-form');
    const $ = (id) => document.getElementById(id);
    const roleLabels = @json(collect($roles)->map(fn ($r) => $r['label']));
    const checkedRole = () => (form.querySelector('input[name="role"]:checked') || {}).value || '';

    form.querySelectorAll('input[name="role"]').forEach((r) => r.addEventListener('change', () => syncRoleFields(r.value)));

    // Show / hide password
    form.querySelectorAll('.ucf-eye').forEach((btn) => btn.addEventListener('click', () => {
        const input = $(btn.dataset.target);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    }));

    function render() {
        const name = $('name').value.trim();
        $('id-name').textContent = name || 'New user';
        $('id-email').textContent = $('email').value.trim() || 'email@example.com';
        $('id-avatar').textContent = name ? name.split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('') : '?';
        const role = checkedRole();
        $('id-role').textContent = roleLabels[role] || 'No role yet';
        $('id-role').classList.toggle('is-empty', !role);
        const unit = role === 'college_staff' ? $('college_name').value
            : role === 'org_staff' ? $('organization_name').value
            : role === 'guest' ? $('organization_name_guest').value
            : role === 'admin' ? $('admin_position').value : '';
        $('id-unit').textContent = unit.trim();

        const p = $('password').value, c = $('password_confirmation').value, m = $('pass-match');
        m.textContent = c ? (p === c ? 'Passwords match' : 'Passwords do not match') : '';
        m.className = 'ucf-match' + (c ? (p === c ? ' is-ok' : ' is-bad') : '');
    }
    form.addEventListener('input', render);
    form.addEventListener('change', render);

    syncRoleFields(checkedRole());
    render();
})();
</script>
@endsection
