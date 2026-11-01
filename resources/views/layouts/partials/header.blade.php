<header class="main-header">
    <div class="header-left">
        <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="University of Antique Logo" class="header-logo" />
        <div class="header-titles">
            <span class="ht-uni">University of Antique</span>
            <span class="ht-sys">Facility Management System</span>
        </div>
    </div>
    <div class="header-right">
        <details class="header-navigation">
            <summary class="nav-menu-toggle">Menu</summary>
            <nav class="nav-links" aria-label="Main navigation">
                <a class="nav-link" href="{{ route('home') }}#facilities">Facilities</a>
                <a class="nav-link" href="{{ route('home') }}#calendar">Schedule</a>
                <a class="nav-link" href="{{ route('public.guest') }}">Guest access</a>
                <a class="nav-link" href="{{ route('public.rates') }}">Venue rates</a>
                <a class="nav-link" href="{{ route('home') }}#about">About</a>
            </nav>
        </details>
        <div class="nav-actions">
            @auth
                @php
                    $user = auth()->user();
                    $dashRoute = $user->isAdmin()
                        ? 'admin.dashboard'
                        : ($user->isCollegeStaff()
                            ? 'college.dashboard'
                            : ($user->isOrgStaff()
                                ? 'org.dashboard'
                                : 'home'));
                @endphp
                <a href="{{ route($dashRoute) }}" class="btn-ghost">Dashboard</a>
                <form class="u-inline" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-solid">Sign out</button>
                </form>
            @endauth
        </div>
    </div>
</header>

