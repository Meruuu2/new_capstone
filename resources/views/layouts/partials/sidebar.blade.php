@php
    $portalTitle = $portalTitle ?? 'Portal';
    $portalSubtitle = $portalSubtitle ?? null;
    $sections = $sections ?? [];
    $footer = $footer ?? null;
    $collapsible = $collapsible ?? false; // opt-in: UA logo + in-sidebar burger (admin layout)
@endphp

<aside class="fms-sidebar" aria-label="Main navigation">
    {{-- Inline style: app.css paints this border with a layered !important that page CSS can't override --}}
    <div class="fms-sidebar-header {{ $collapsible ? 'fms-sidebar-header--brand' : '' }}" @if ($collapsible) style="border-bottom-color: transparent !important;" @endif>
        @if ($collapsible)
            <button type="button" class="fms-sidebar-logo" aria-label="Expand navigation" title="Expand navigation">
                <img src="{{ asset('img/facilities/UA-logo.png') }}" alt="">
            </button>
            <div class="fms-sidebar-heading">
                <p class="fms-sidebar-title">{{ $portalTitle }}</p>
                @if ($portalSubtitle)
                    <p class="fms-sidebar-subtitle">{{ $portalSubtitle }}</p>
                @endif
            </div>
            <button type="button" class="fms-sidebar-burger" aria-label="Minimize navigation" title="Minimize navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line class="l1" x1="4" y1="7" x2="20" y2="7"/><line class="l2" x1="4" y1="12" x2="20" y2="12"/><line class="l3" x1="4" y1="17" x2="20" y2="17"/></svg>
            </button>
        @else
            <p class="fms-sidebar-title">{{ $portalTitle }}</p>
            @if ($portalSubtitle)
                <p class="fms-sidebar-subtitle">{{ $portalSubtitle }}</p>
            @endif
        @endif
    </div>

    {{-- NAV GROUPS --}}
    <nav class="fms-sidebar-nav">
        @foreach ($sections as $section)
            <div class="fms-nav-group">
                @if (!empty($section['heading']))
                    <p class="fms-nav-group-title">{{ $section['heading'] }}</p>
                @endif

                @if (!empty($section['links']))
                    <ul class="fms-nav-list">
                        @foreach ($section['links'] as $link)
                            @php
                                $route    = $link['route']  ?? null;
                                $patterns = (array)($link['routes'] ?? $route);
                                $isActive = false;
                                foreach ($patterns as $pattern) {
                                    if ($pattern && request()->routeIs($pattern)) {
                                        $isActive = true;
                                        break;
                                    }
                                }
                            @endphp
                            <li>
                                <a href="{{ $route ? route($route) : '#' }}"
                                   class="fms-nav-link {{ $isActive ? 'fms-nav-link-active' : '' }}"
                                   @if ($isActive) aria-current="page" @endif>
                                    @if (!empty($link['icon']))
                                        <svg class="fms-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $link['icon'] !!}</svg>
                                    @endif
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="mt-auto border-t border-black p-4 text-sm flex flex-col gap-4 bg-white">
        @if ($footer)
            <div class="leading-tight text-neutral-800">
                {!! $footer !!}
            </div>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="text-left w-full text-black font-medium hover:underline flex items-center">
                @if ($collapsible)
                    <svg class="fms-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                @endif
                Logout
            </button>
        </form>
    </div>
</aside>