@extends('layouts.admin')

@section('admin-content')
@include('layouts.partials.page-kit')
@php
    $roleMeta = [
        'admin'         => ['label' => 'Admin',         'class' => 'is-admin',   'icon' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>'],
        'college_staff' => ['label' => 'College staff', 'class' => 'is-college', 'icon' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/>'],
        'org_staff'     => ['label' => 'Org staff',     'class' => 'is-org',     'icon' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/>'],
        'guest'         => ['label' => 'Guest',         'class' => 'is-guest',   'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>'],
    ];
@endphp

<div class="pk-page" id="admin-users-page">
    <section class="pk-hero" style="--pk-img: url('{{ asset('img/facilities/busalian-hall.jpg') }}')">
        <div>
            <p class="pk-eyebrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0M16 4.5a3.5 3.5 0 010 7M21.5 20a6.5 6.5 0 00-4-6"/></svg>
                Administration
            </p>
            <h1>User Management</h1>
            <p class="pk-hero-sub">Accounts for GSU admins, college staff, organization units and guests.</p>
        </div>
        <div class="pk-hero-actions">
            <div class="pk-stat is-dark pk-stat--glass pk-stat--hero u-minw-150">
                <div>
                    <p class="pk-stat-label u-mb-6">Accounts</p>
                    <p class="pk-stat-value">{{ $users->total() }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.create') }}" class="pk-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg>
                Add New User
            </a>
        </div>
    </section>

    {{-- Admin team: everyone with the Admin role, with their GSU title --}}
    <div class="pk-section-head">
        <h2 class="pk-section-title">Admin team</h2>
        <span class="pk-section-meta">{{ $adminTeam->count() }} {{ $adminTeam->count() === 1 ? 'member' : 'members' }}</span>
    </div>
    <div class="adt-grid">
        @foreach ($adminTeam as $member)
            @php $mInitials = collect(preg_split('/\s+/', trim((string) $member->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?'; @endphp
            <article class="adt-card">
                <span class="pk-avatar usr-avatar is-admin" aria-hidden="true">{{ $mInitials }}</span>
                <div class="u-minw-0 adt-body">
                    <p class="adt-name">{{ $member->name }}@if ($member->id === auth()->id())<span class="adt-you">You</span>@endif</p>
                    <p class="adt-pos {{ $member->position ? '' : 'is-empty' }}">{{ $member->position ?: 'No position set' }}</p>
                    <p class="adt-meta">{{ $member->email }}@if ($member->contact_number) · {{ $member->contact_number }}@endif</p>
                </div>
                <a href="{{ route('admin.users.edit', $member) }}" class="adt-edit" aria-label="Edit {{ $member->name }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                </a>
            </article>
        @endforeach
        <a href="{{ route('admin.users.create', ['role' => 'admin']) }}" class="adt-card adt-add">
            <span class="adt-add-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0M19 8v6M16 11h6"/></svg></span>
            <span>
                <strong>Add admin staff</strong>
                <span>New GSU team member with full admin access</span>
            </span>
        </a>
    </div>

    <div class="pk-section-head">
        <h2 class="pk-section-title">All users</h2>
        <div class="u-row-8">
            @if ($users->count() > 0)
                <span class="pk-section-meta">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>
            @endif
            <button type="button" class="pk-btn-outline" id="manage-users-toggle" aria-pressed="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                Manage users
            </button>
        </div>
    </div>

    @if($users->count() > 0)
        <div class="pk-table-wrap">
            <table class="pk-table usr-table" id="admin-users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Unit</th>
                        <th>Created</th>
                        <th class="usr-action-cell"><span class="pk-sr">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $meta = $roleMeta[$user->role] ?? ['label' => ucfirst(str_replace('_', ' ', $user->role)), 'class' => '', 'icon' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>'];
                            $initials = collect(preg_split('/\s+/', trim((string) $user->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                            $unit = $user->college_name ?: ($user->organization_name ?: null);
                            $h = $history[$user->id] ?? ['bookings' => 0, 'requests' => 0, 'tickets' => 0];
                            $isSelf = $user->id === auth()->id();
                            $isSystem = $user->email === $systemEmail;
                            // Records that remain preserved when the account is deactivated
                            $retained = collect(['bookings' => 'reservation', 'requests' => 'request', 'tickets' => 'repair ticket'])
                                ->filter(fn ($label, $k) => $h[$k] > 0)
                                ->map(fn ($label, $k) => $h[$k] . ' ' . $label . ($h[$k] > 1 ? 's' : ''))
                                ->values();
                        @endphp
                        <tr>
                            <td>
                                <span class="pk-person">
                                    <span class="pk-avatar usr-avatar {{ $user->role === 'admin' ? 'is-admin' : '' }}" aria-hidden="true">{{ $initials ?: '?' }}</span>
                                    <span class="u-minw-0">
                                        <span class="pk-strong">{{ $user->name }}</span>
                                        <span class="usr-email">{{ $user->email }}</span>
                                        @if ($user->contact_number)<span class="usr-email">{{ $user->contact_number }}</span>@endif
                                        @unless ($user->is_active)<span class="usr-status">Deactivated</span>@endunless
                                        @if ($user->email === 'walk-in-guest@ua-fms.local')
                                            <span class="usr-system">System account · holds all walk-in guest requests, do not remove</span>
                                        @endif
                                    </span>
                                </span>
                            </td>
                            <td>
                                <span class="usr-role {{ $meta['class'] }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $meta['icon'] !!}</svg>
                                    {{ $meta['label'] }}
                                </span>
                            </td>
                            <td><span class="usr-unit {{ $unit ? '' : 'is-empty' }}">{{ $unit ?? '-' }}</span></td>
                            <td class="pk-nowrap">{{ $user->created_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="usr-action-cell">
                                <span class="usr-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="usr-btn" aria-label="Edit {{ $user->name }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                                        Edit
                                    </a>
                                    @if (! $user->is_active)
                                        <span class="usr-btn is-locked" aria-label="{{ $user->name }} is deactivated">Deactivated</span>
                                    @elseif ($isSelf || $isSystem)
                                        <span class="usr-btn is-locked" title="{{ $isSelf ? "You can't deactivate your own account" : "System account must remain active" }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                                            {{ $isSelf ? 'You' : 'Protected' }}
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="usr-deactivate" data-name="{{ $user->name }}" data-email="{{ $user->email }}" data-initials="{{ $initials ?: '?' }}" data-retained='@json($retained)'>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="usr-btn is-danger" aria-label="Deactivate {{ $user->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/></svg>
                                                Deactivate
                                            </button>
                                        </form>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pk-pagination">
            {{ $users->links() }}
        </div>
    @else
        <div class="pk-empty">
            <span class="pk-empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg></span>
            <h3>No users found</h3>
            <p>Create an account to give someone access to the system.</p>
        </div>
    @endif
</div>

{{-- Deactivation confirmation --}}
<dialog class="usr-modal" id="deactivate-modal" aria-labelledby="deactivate-title" aria-describedby="deactivate-desc">
    <div class="usr-modal-card">
        <span class="usr-modal-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6M10 11v6M14 11v6"/></svg>
        </span>
        <h2 class="usr-modal-title" id="deactivate-title">Deactivate this account?</h2>
        <p class="usr-modal-desc" id="deactivate-desc">The person will no longer be able to sign in. Future facility requests and upcoming bookings will be cancelled; past and completed activity will remain unchanged.</p>

        <div class="usr-modal-person">
            <span class="pk-avatar" id="deactivate-initials" aria-hidden="true">?</span>
            <span class="u-minw-0">
                <strong id="deactivate-name"></strong>
                <span id="deactivate-email"></span>
            </span>
        </div>

        <div class="usr-modal-records" id="deactivate-records" hidden>
            <p>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
                These records stay in history. Upcoming bookings and requests will be marked cancelled, not deleted:
            </p>
            <ul id="deactivate-records-list"></ul>
        </div>
        <p class="usr-modal-clean" id="deactivate-clean" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            No reservations, requests, or repair tickets are attached to this account.
        </p>

        <div class="usr-modal-actions">
            <button type="button" class="pk-btn-outline" id="deactivate-cancel">Cancel</button>
            <button type="button" class="usr-modal-confirm" id="deactivate-confirm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                Deactivate account
            </button>
        </div>
    </div>
</dialog>

<style>
    .usr-table:not(.is-managing) .usr-action-cell { display: none; }
    #admin-users-page:not(.is-managing-users) .adt-edit { display: none; }
    .usr-actions { display: inline-flex; align-items: center; gap: 6px; justify-content: flex-end; white-space: nowrap; }
    .usr-deactivate { margin: 0; }
    .usr-status { display: inline-block; margin-top: 4px; padding: 2px 7px; border-radius: 999px; background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; }
    .usr-btn { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border: 1px solid #E2E8F0; border-radius: 9px; background: #fff; color: #0f172a; font: inherit; font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer; transition: border-color .15s ease, background .15s ease, color .15s ease; }
    .usr-btn svg { width: 14px; height: 14px; flex-shrink: 0; }
    .usr-btn:hover { border-color: #94A3B8; background: #F8FAFC; }
    .usr-btn:focus-visible { outline: 2px solid #64748B; outline-offset: 2px; }
    .usr-btn.is-danger { border-color: #FECACA; color: #B91C1C; }
    .usr-btn.is-danger:hover { border-color: #B91C1C; background: #FEF2F2; }
    .usr-btn.is-locked { border-style: dashed; color: #94A3B8; cursor: default; }
    .usr-btn.is-locked:hover { border-color: #E2E8F0; background: #fff; }

    .adt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; margin-bottom: 28px; }
    .adt-card { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px 16px; border-radius: 16px; border: 1px solid #E2E8F0; background: #fff; }
    .adt-card .pk-avatar { width: 42px; height: 42px; flex-shrink: 0; font-size: 13.5px; }
    .adt-body { flex: 1; }
    .adt-name { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 14.5px; font-weight: 600; color: #0f172a; }
    .adt-you { padding: 1px 7px; border-radius: 999px; background: #F1F5F9; color: #475569; font-size: 11px; font-weight: 700; }
    .adt-pos { margin: 3px 0 0; font-size: 12.5px; font-weight: 600; color: #334155; }
    .adt-pos.is-empty { font-weight: 500; font-style: italic; color: #94A3B8; }
    .adt-meta { margin: 3px 0 0; font-size: 12px; color: #64748B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .adt-edit { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; border: 1px solid #E2E8F0; color: #334155; transition: border-color .15s ease, background .15s ease; }
    .adt-edit svg { width: 15px; height: 15px; }
    .adt-edit:hover { border-color: #94A3B8; background: #F8FAFC; }
    .adt-edit:focus-visible, .adt-add:focus-visible { outline: 2px solid #64748B; outline-offset: 2px; }
    .adt-add { border-style: dashed; border-color: #CBD5E1; background: #F8FAFC; color: #0f172a; text-decoration: none; transition: border-color .15s ease, background .15s ease; }
    .adt-add:hover { border-color: #0f172a; background: #fff; }
    .adt-add-icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; flex-shrink: 0; border-radius: 12px; background: #0f172a; color: #fff; }
    .adt-add-icon svg { width: 19px; height: 19px; }
    .adt-add strong { display: block; font-size: 14px; font-weight: 700; }
    .adt-add span span { display: block; margin-top: 2px; font-size: 12px; color: #64748B; }

    .usr-modal { position: fixed; inset: 0; margin: auto; height: fit-content; max-height: calc(100vh - 32px); overflow: auto; width: min(440px, calc(100vw - 32px)); padding: 0; border: 0; border-radius: 20px; background: transparent; box-shadow: 0 30px 70px -20px rgba(15, 23, 42, .45); }
    .usr-modal::backdrop { background: rgba(15, 23, 42, .55); backdrop-filter: blur(3px); }
    .usr-modal[open] { animation: usr-pop .18s ease-out; }
    @keyframes usr-pop { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }
    .usr-modal-card { display: flex; flex-direction: column; padding: 26px 24px 22px; border-radius: 20px; background: #fff; color: #0f172a; }
    .usr-modal-icon { display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 14px; background: #FEF2F2; color: #B91C1C; }
    .usr-modal-icon svg { width: 22px; height: 22px; }
    .usr-modal-title { margin: 16px 0 4px; font-size: 18px; font-weight: 700; letter-spacing: -.01em; }
    .usr-modal-desc { margin: 0; font-size: 13.5px; line-height: 1.5; color: #64748B; }
    .usr-modal-person { display: flex; align-items: center; gap: 12px; margin-top: 18px; padding: 12px 14px; border-radius: 14px; border: 1px solid #E2E8F0; background: #F8FAFC; }
    .usr-modal-person .pk-avatar { width: 38px; height: 38px; flex-shrink: 0; font-size: 13px; }
    .usr-modal-person strong { display: block; font-size: 14.5px; font-weight: 600; }
    .usr-modal-person span span, .usr-modal-person #deactivate-email { display: block; margin-top: 1px; font-size: 12.5px; color: #64748B; overflow-wrap: anywhere; }
    .usr-modal-records { margin-top: 14px; padding: 12px 14px; border-radius: 14px; border: 1px solid #BFDBFE; background: #EFF6FF; color: #1E40AF; }
    .usr-modal-records p { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 13px; font-weight: 600; }
    .usr-modal-records p svg { width: 16px; height: 16px; flex-shrink: 0; }
    .usr-modal-records ul { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0 0; padding: 0; list-style: none; }
    .usr-modal-records li { padding: 4px 10px; border-radius: 999px; background: #fff; border: 1px solid #BFDBFE; font-size: 12.5px; font-weight: 600; }
    .usr-modal-clean { display: flex; align-items: center; gap: 8px; margin: 14px 0 0; font-size: 13px; color: #047857; }
    .usr-modal-clean svg { width: 16px; height: 16px; flex-shrink: 0; }
    .usr-modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
    .usr-modal-confirm { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 18px; border: 1px solid #B91C1C; border-radius: 12px; background: #B91C1C; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s ease; }
    .usr-modal-confirm svg { width: 16px; height: 16px; }
    .usr-modal-confirm:hover { background: #991B1B; }
    .usr-modal-confirm:focus-visible, .usr-modal .pk-btn-outline:focus-visible { outline: 2px solid #64748B; outline-offset: 2px; }
    .usr-modal-confirm:disabled { opacity: .7; cursor: progress; }
    @media (max-width: 480px) { .usr-modal-actions { flex-direction: column-reverse; } .usr-modal-actions > * { width: 100%; justify-content: center; } }
</style>

<script>
(function () {
    const manageUsers = document.getElementById('manage-users-toggle');
    const usersPage = document.getElementById('admin-users-page');
    const usersTable = document.getElementById('admin-users-table');
    manageUsers?.addEventListener('click', function () {
        const isManaging = this.getAttribute('aria-pressed') !== 'true';
        this.setAttribute('aria-pressed', String(isManaging));
        this.innerHTML = isManaging
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg> Done managing'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg> Manage users';
        usersPage?.classList.toggle('is-managing-users', isManaging);
        usersTable?.classList.toggle('is-managing', isManaging);
    });

    const modal = document.getElementById('deactivate-modal');
    if (!modal) return;
    const $ = (id) => document.getElementById(id);
    let pending = null;

    document.querySelectorAll('.usr-deactivate').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            pending = form;
            const retained = JSON.parse(form.dataset.retained || '[]');
            $('deactivate-name').textContent = form.dataset.name;
            $('deactivate-email').textContent = form.dataset.email;
            $('deactivate-initials').textContent = form.dataset.initials;
            $('deactivate-records-list').innerHTML = '';
            retained.forEach(function (item) { const li = document.createElement('li'); li.textContent = item; $('deactivate-records-list').append(li); });
            $('deactivate-records').hidden = !retained.length;
            $('deactivate-clean').hidden = !!retained.length;
            $('deactivate-confirm').disabled = false;
            modal.showModal();
            $('deactivate-cancel').focus();   // the safe choice is focused first
        });
    });

    $('deactivate-cancel').addEventListener('click', function () { modal.close(); });
    // Click on the dimmed backdrop closes it (Esc is handled by <dialog>)
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.close(); });
    modal.addEventListener('close', function () { pending = null; });
    $('deactivate-confirm').addEventListener('click', function () {
        if (!pending) return;
        this.disabled = true;
        pending.submit();   // native submit skips the listener above
    });
})();
</script>
@endsection
