{{-- Add / edit user form for the College Staff and Organization portals.
     Expects: $prefix ('college' | 'org'), $unitName, $unitNoun ('college' | 'organization'),
              $roleLabel ('College Staff' | 'Organization Staff'), $positions (suggestions), $positionLabel,
              $headTitle ('Dean' | 'Governor'),
              $editUser (null when adding), $canManage (the signed-in user is the head) --}}
@include('layouts.partials.page-kit')
@php
    $editing = (bool) $editUser;
    $isSelf  = $editing && $editUser->id === auth()->id();
    $title   = !$editing ? 'Add user' : ($isSelf ? 'Edit your account' : 'Edit user');
    $initial = $editing ? collect(preg_split('/\s+/', trim((string) $editUser->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') : '?';
@endphp

<div class="pk-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/new-avr.jpg') }}')">
        <div>
            <h1>{{ $title }}</h1>
            <p class="pk-hero-sub">
                @if (!$editing)
                    Create an account for someone who handles the system for {{ $unitName ?: 'your ' . $unitNoun }}.
                @elseif ($isSelf)
                    Update your name, email or password.
                @else
                    Update {{ $editUser->name }}'s details for {{ $unitName ?: 'your ' . $unitNoun }}.
                @endif
            </p>
        </div>
        <div class="pk-hero-actions">
            <a href="{{ route($prefix . '.users.index') }}" class="pk-btn-ghost">
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
        <form method="POST" action="{{ $editing ? route($prefix . '.users.update', $editUser) : route($prefix . '.users.store') }}" class="ucf-card" id="user-form">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="ucf-step">
                <span class="ucf-num">01</span>
                <div>
                    <h2>Profile</h2>
                    <p class="ucf-intro">Enter the person's name and the exact last name they will use to check request status.</p>
                    <div class="ucf-fields">
                        <div class="pk-field">
                            <label for="name" class="pk-label">Full name <span class="pk-req">*</span></label>
                            <input type="text" name="name" id="name" required maxlength="255" pattern="\p{L}+([ '’\-]\p{L}+)*" title="Use letters, spaces, hyphens, or apostrophes only." class="pk-input" value="{{ old('name', $editUser?->name) }}" autocomplete="off">
                            @error('name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pk-field">
                            <label for="last_name" class="pk-label">Last name for tracking <span class="pk-req">*</span></label>
                            <input type="text" name="last_name" id="last_name" required maxlength="120" pattern="\p{L}+([ '’\-]\p{L}+)*" title="Use letters, spaces, hyphens, or apostrophes only." class="pk-input" value="{{ old('last_name', $editUser?->last_name) }}" autocomplete="family-name">
                            @error('last_name')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        @include('layouts.partials.contact-number-input', ['value' => old('contact_number', $editUser?->contact_number)])
                        <div class="pk-field">
                            <label for="position" class="pk-label">{{ $positionLabel }} @if ($canManage)<span class="pk-req">*</span>@endif</label>
                            @if ($canManage)
                                <input type="text" name="position" id="position" required maxlength="120" class="pk-input" list="position-list" value="{{ old('position', $editUser?->position) }}" placeholder="Pick one or type a title" autocomplete="off">
                                <datalist id="position-list">
                                    @foreach ($positions as $p)<option value="{{ $p }}"></option>@endforeach
                                </datalist>
                            @else
                                <input type="text" id="position" readonly class="pk-input" value="{{ $editUser?->position ?: 'Not set' }}">
                                <p class="pk-help">Only the {{ $headTitle }} can change positions.</p>
                            @endif
                            @error('position')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @if ($canManage)
                        <div class="uu-chips" aria-label="Common positions">
                            @foreach ($positions as $p)
                                <button type="button" class="uu-chip" data-position="{{ $p }}">{{ $p }}</button>
                            @endforeach
                        </div>
                        <p class="pk-help uu-note">
                            @if ($prefix === 'college')
                                Only the primary college account has College Admin access; staff positions do not grant account-management rights.
                            @else
                                Giving someone the {{ $headTitle }} position lets them add, edit and deactivate accounts too.
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <div class="ucf-step u-border-b-0">
                <span class="ucf-num">02</span>
                <div>
                    <h2>Sign-in details</h2>
                    <p class="ucf-intro">
                        Use 8–94 characters with uppercase and lowercase letters and at least one special character.{{ $editing ? ' Leave blank to keep the current password.' : '' }}
                    </p>
                    <div class="pk-field uu-email">
                        <label for="email" class="pk-label">Email <span class="pk-req">*</span></label>
                        <input type="email" name="email" id="email" required maxlength="255" class="pk-input" value="{{ old('email', $editUser?->email) }}" autocomplete="off">
                        @error('email')<p class="pk-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="ucf-fields">
                        <div class="pk-field">
                            <label for="password" class="pk-label">{{ $editing ? 'New password' : 'Password' }} @unless ($editing)<span class="pk-req">*</span>@endunless</label>
                            <div class="ucf-pass">
                                <input type="password" name="password" id="password" {{ $editing ? '' : 'required' }} minlength="8" maxlength="94" class="pk-input" autocomplete="new-password">
                                <button type="button" class="ucf-eye" data-target="password" aria-label="Show password" aria-pressed="false">
                                    <svg class="is-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="is-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A10 10 0 0112 5c6.5 0 10 7 10 7a17 17 0 01-3.2 4.1M6.6 6.6A17 17 0 002 12s3.5 7 10 7a9.7 9.7 0 005.4-1.6"/></svg>
                                </button>
                            </div>
                            @error('password')<p class="pk-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="pk-field">
                            <label for="password_confirmation" class="pk-label">Confirm password @unless ($editing)<span class="pk-req">*</span>@endunless</label>
                            <div class="ucf-pass">
                                <input type="password" name="password_confirmation" id="password_confirmation" {{ $editing ? '' : 'required' }} minlength="8" maxlength="94" class="pk-input" autocomplete="new-password">
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

            <div class="ucf-actions">
                <a href="{{ route($prefix . '.users.index') }}" class="pk-btn-outline">Cancel</a>
                <button type="submit" class="pk-btn">
                    @if ($editing)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                        Save changes
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg>
                        Create user
                    @endif
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
                    <span class="ucf-id-avatar" id="id-avatar" aria-hidden="true">{{ $initial ?: '?' }}</span>
                    <p class="ucf-id-name" id="id-name">{{ $editUser?->name ?? 'New user' }}</p>
                    <p class="ucf-id-email" id="id-email">{{ $editUser?->email ?? 'email@example.com' }}</p>
                    <span class="ucf-id-role" id="id-role">{{ $roleLabel }}</span>
                    <p class="ucf-id-unit" id="id-unit">{{ $unitName }}</p>
                </div>
                <div class="ucf-id-foot">Access card preview</div>
            </div>
            @unless ($editing)
                <p class="pk-help uu-note">The new account opens the same {{ $roleLabel }} portal you use, limited to {{ $unitName ?: 'your ' . $unitNoun }}.</p>
            @endunless
        </aside>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('user-form');
    const $ = (id) => document.getElementById(id);
    const unit = @json((string) $unitName);

    // Position chips fill the field (it stays typeable for other titles)
    form.querySelectorAll('.uu-chip').forEach((chip) => chip.addEventListener('click', () => {
        $('position').value = chip.dataset.position;
        $('position').dispatchEvent(new Event('input', { bubbles: true }));
    }));

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
        const position = $('position').readOnly ? '' : $('position').value.trim();
        $('id-unit').textContent = [position || ($('position').readOnly ? $('position').value : ''), unit].filter(v => v && v !== 'Not set').join(' · ');
        form.querySelectorAll('.uu-chip').forEach((c) => c.classList.toggle('is-on', c.dataset.position === position));

        const p = $('password').value, c = $('password_confirmation').value, m = $('pass-match');
        m.textContent = c ? (p === c ? 'Passwords match' : 'Passwords do not match') : '';
        m.className = 'ucf-match' + (c ? (p === c ? ' is-ok' : ' is-bad') : '');
    }
    form.addEventListener('input', render);
    render();
})();
</script>
