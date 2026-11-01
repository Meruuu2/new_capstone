{{-- Shared portal shell (Admin, College Staff, Organization Unit): sidebar, topbar, collapse/drawer behaviour.
     Per CLAUDE.md every portal's navigation must look and behave the same, so all portal layouts include this.
     Params: $sections (sidebar groups; 'icon' may be a key of $navIcons), $roleLabel (footer card), $portalHeading,
             $dashboardRoute (heading only shows there), $contentSection (section the page yields). --}}
@php
    $shellUser = auth()->user();
    $shellName = optional($shellUser)->name ?? 'User';
    $shellInitials = collect(preg_split('/\s+/', trim($shellName)))->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
    // Nav icons (24px line icons, stroke inherits the link colour)
    $navIcons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'records'   => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
        'building'  => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>',
        'plus'      => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M12 8v8M8 12h8"/>',
        'approve'   => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'wrench'    => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/>',
        'bookmark'  => '<path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/>',
        'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'chart'     => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'users'     => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.9.4 5 2.8 5 5.8"/>',
        'tag'       => '<path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'inbox'     =>'<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
        'file-plus' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M12 12v6M9 15h6"/>',
        'user-plus' =>'<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/><path d="M19 8v6M16 11h6"/>',
    ];
    $sections = collect($sections)->map(function ($group) use ($navIcons) {
        $group['links'] = collect($group['links'])->map(function ($link) use ($navIcons) {
            if (isset($link['icon']) && isset($navIcons[$link['icon']])) {
                $link['icon'] = $navIcons[$link['icon']];
            }
            return $link;
        })->all();
        return $group;
    })->all();
    $shellFooter = '<div class="admin-user"><span class="admin-user-avatar" aria-hidden="true">' . e($shellInitials) . '</span>'
        . '<div class="min-w-0"><p class="admin-user-name truncate">' . e($shellName) . '</p><p class="admin-user-role truncate">' . e($roleLabel) . '</p></div></div>';
@endphp
<div class="fms-shell admin-shell" id="admin-shell">
    {{-- app.css paints ".fms-shell > :first-child" and ".fms-shell > aside" with layered !important rules (black header,
         #2c2c2c hovers). The backdrop goes first and the sidebar sits in a wrapper so neither selector reaches it. --}}
    <div class="admin-sidebar-backdrop" aria-hidden="true"></div>
    <div class="admin-nav-wrap">
    @include('layouts.partials.sidebar', [
        'portalTitle' => 'University of Antique',
        'portalSubtitle' => 'Facility Management System',
        'collapsible' => true,
        'footer' => $shellFooter,
        'sections' => $sections,
    ])
    </div>
    <script>
        // Apply the saved desktop state before paint to avoid a flash of the sidebar.
        // The header's black background comes from a layered !important rule in app.css, so only an inline style can clear it.
        window.adminRailHeader = function (shell) {
            const header = shell.querySelector('.fms-sidebar-header');
            const rail = shell.classList.contains('is-collapsed') && !window.matchMedia('(max-width: 900px)').matches;
            if (!header) return;
            if (rail) { header.style.setProperty('background', 'transparent', 'important'); }
            else { header.style.removeProperty('background'); }
        };
        try {
            const shell = document.getElementById('admin-shell');
            if (localStorage.getItem('adminSidebarCollapsed') === '1') shell.classList.add('is-collapsed');
            window.adminRailHeader(shell);
        } catch (e) {}
    </script>

    <section class="fms-main">
        {{-- Topbar --}}
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-burger" aria-label="Open navigation menu" aria-controls="admin-sidebar" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                @if (request()->routeIs($dashboardRoute))
                    <h1>{{ $portalHeading }}</h1> {{-- portal title only on the dashboard; other pages carry their own page title --}}
                @endif
            </div>
            <div class="admin-topbar-actions">
                @include('layouts.partials.notification-bell')
            </div>
        </div>

        <div class="fms-content-wrapper">
            @yield($contentSection)
        </div>
    </section>

    {{-- Right after a facility request is submitted, pop up its ticket (College, Org; guests get the public ticket page).
         Read-only lookup of the user's newest request; the submit flow itself is unchanged. --}}
    @if (session('status') === 'Facilities utilization request submitted to GSU.' && auth()->check())
        @php
            $ticketUser = auth()->user();
            $ticketPortal = $ticketUser->isCollegeStaff() ? 'college' : ($ticketUser->isOrgStaff() ? 'org' : null); // guests use the public form + ticket page
            $ticketSubmission = $ticketPortal
                ? \App\Models\FormSubmission::where('requester_id', $ticketUser->id)->where('type', 'facilities_utilization')->latest('id')->first()
                : null;
        @endphp
        @if ($ticketSubmission)
            @include('layouts.partials.request-ticket', [
                'submission' => $ticketSubmission,
                'autoOpen'   => true,
                'showUrl'    => route($ticketPortal . '.requests.facilities.show', $ticketSubmission),
            ])
        @endif
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const shell = document.getElementById('admin-shell');
        const sidebar = shell.querySelector('.fms-sidebar');
        const topBurger = shell.querySelector('.admin-burger');            // small screens: opens the drawer
        const sideBurger = shell.querySelector('.fms-sidebar-burger');     // inside the sidebar: minimize / close
        const logo = shell.querySelector('.fms-sidebar-logo');             // collapsed rail: expand
        const backdrop = shell.querySelector('.admin-sidebar-backdrop');
        if (!sidebar || !sideBurger || !logo) return;
        sidebar.id = 'admin-sidebar';

        const mobile = window.matchMedia('(max-width: 900px)');
        const save = (collapsed) => { try { localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {} };

        const sync = () => {
            const railMode = !mobile.matches && shell.classList.contains('is-collapsed');
            const label = mobile.matches ? 'Close navigation' : 'Minimize navigation';
            topBurger.setAttribute('aria-expanded', String(shell.classList.contains('is-open')));
            sideBurger.setAttribute('aria-label', label);
            sideBurger.setAttribute('title', label);
            // The logo is only a control while the sidebar is minimized to the rail
            logo.disabled = !railMode;
            window.adminRailHeader(shell); // black header only when the sidebar is expanded
            if (railMode) { logo.removeAttribute('aria-hidden'); } else { logo.setAttribute('aria-hidden', 'true'); }
        };
        const closeDrawer = () => { shell.classList.remove('is-open'); sync(); };

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const replay = (el, cls) => { el.classList.remove(cls); void el.offsetWidth; el.classList.add(cls); };
        logo.addEventListener('animationend', () => logo.classList.remove('is-spinning', 'is-closing'));

        sideBurger.addEventListener('click', function () {
            if (sideBurger.classList.contains('is-morphing')) return;
            const finish = () => {
                sideBurger.classList.remove('is-morphing');
                if (mobile.matches) { closeDrawer(); topBurger.focus(); return; }
                shell.classList.add('is-collapsed');
                save(true);
                sync();
                logo.focus({ preventScroll: true });
                if (!reduceMotion.matches) replay(logo, 'is-closing'); // turns back the other way as the sidebar folds
            };
            if (reduceMotion.matches) { finish(); return; }
            sideBurger.classList.add('is-morphing'); // lines turn into an X, then the sidebar collapses
            setTimeout(finish, 140); // start collapsing while the X finishes, so it reads as one motion
        });
        logo.addEventListener('click', function () {
            if (mobile.matches || !shell.classList.contains('is-collapsed')) return;
            if (!reduceMotion.matches) replay(logo, 'is-spinning');
            shell.classList.remove('is-collapsed');
            save(false);
            sync();
            sideBurger.focus({ preventScroll: true }); // without this the narrow header scrolls to the burger and the logo jumps
        });
        topBurger.addEventListener('click', function () { shell.classList.toggle('is-open'); sync(); });
        backdrop.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && shell.classList.contains('is-open')) { closeDrawer(); topBurger.focus(); }
        });
        mobile.addEventListener('change', closeDrawer);
        sync();
    });
</script>
