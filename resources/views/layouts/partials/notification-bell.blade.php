{{-- Notification bell (icon + unread badge). Shared by the admin, college, org and guest portals. --}}
@php
    $bellCount  = (int) ($unreadNotificationsCount ?? 0);
    $bellActive = request()->routeIs('notifications.*');
    $bellLabel  = 'Notifications' . ($bellCount > 0 ? ", {$bellCount} unread" : '');
@endphp

@once
@endonce

<a href="{{ route('notifications.index') }}"
   class="fms-bell {{ $bellActive ? 'is-active' : '' }}"
   aria-label="{{ $bellLabel }}"
   title="{{ $bellLabel }}"
   @if ($bellActive) aria-current="page" @endif>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
    </svg>
    @if ($bellCount > 0)
        <span class="fms-bell-badge" aria-hidden="true">{{ $bellCount > 99 ? '99+' : $bellCount }}</span>
    @endif
</a>
