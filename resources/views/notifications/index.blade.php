@php
    // Show the page inside the signed-in user's own portal (sidebar + topbar); guests and others keep the plain page.
    $role = optional(auth()->user())->role;
    [$notifLayout, $notifSection] = match ($role) {
        'admin'         => ['layouts.admin', 'admin-content'],
        'college_staff' => ['layouts.college', 'college-content'],
        'org_staff'     => ['layouts.org', 'org-content'],
        default         => ['layouts.app', 'content'],
    };

    // Icon per notification type (matched by keyword, so new types still get a sensible icon)
    $notifIcon = function (?string $type) {
        $type = strtolower((string) $type);
        return match (true) {
            str_contains($type, 'maintenance') || str_contains($type, 'repair') => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4 2.5-2.5z"/>',
            str_contains($type, 'reject') || str_contains($type, 'disapprov') || str_contains($type, 'cancel') => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/>',
            str_contains($type, 'approv') || str_contains($type, 'complete') => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
            str_contains($type, 'booking') || str_contains($type, 'reserv') || str_contains($type, 'schedul') => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
            str_contains($type, 'payment') => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h3"/>',
            str_contains($type, 'form') || str_contains($type, 'request') || str_contains($type, 'facilit') => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>',
            default => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        };
    };

    // Group the current page by day
    $notifGroups = $notifications->getCollection()->groupBy(function ($n) {
        $d = $n->created_at;
        if ($d->isToday()) return 'Today';
        if ($d->isYesterday()) return 'Yesterday';
        if ($d->greaterThanOrEqualTo(now()->startOfWeek())) return 'Earlier this week';
        return 'Earlier';
    });
    $pageUnread = $notifications->getCollection()->where('is_read', false)->count();
    $totalUnread = max((int) ($unreadNotificationsCount ?? 0), $pageUnread); // never contradict what the list shows
@endphp

@extends($notifLayout)

@section('notifications-body')

<div class="notif-page {{ $notifLayout === 'layouts.app' ? 'is-standalone' : '' }} {{ $pageUnread === 0 ? 'no-unread-left' : '' }}" id="notif-page">

    <a href="{{ url()->previous() }}" class="notif-return">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back
    </a>

    <div class="notif-head">
        <div>
            <h1>Notifications</h1>
            <p id="notif-summary">
                @if ($totalUnread > 0)
                    You have <strong data-unread-total>{{ $totalUnread }}</strong> unread {{ \Illuminate\Support\Str::plural('notification', $totalUnread) }}.
                @else
                    You're all caught up.
                @endif
            </p>
        </div>

        @if ($notifications->count())
            <div class="notif-tabs" role="tablist" aria-label="Filter notifications">
                <button type="button" class="notif-tab" role="tab" aria-selected="true" data-filter="all">All</button>
                <button type="button" class="notif-tab" role="tab" aria-selected="false" data-filter="unread">
                    Unread
                    <span class="notif-tab-count" data-unread-page>{{ $pageUnread }}</span>
                </button>
            </div>
        @endif
    </div>

    @if ($notifications->count())
        @foreach ($notifGroups as $groupLabel => $items)
            <section class="notif-group {{ $items->where('is_read', false)->isEmpty() ? 'has-no-unread' : '' }}" aria-label="{{ $groupLabel }}">
                <h2 class="notif-group-title">{{ $groupLabel }}</h2>
                <ul class="notif-list">
                    @foreach ($items as $notification)
                        <li class="notif-item {{ $notification->is_read ? '' : 'is-unread' }}" id="notification-{{ $notification->id }}">
                            <span class="notif-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $notifIcon($notification->type) !!}</svg>
                            </span>
                            <div class="notif-body">
                                <div class="notif-top">
                                    <h3 class="notif-title">{{ $notification->title }}</h3>
                                    <time class="notif-time" datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ $notification->created_at->format('M d, Y g:i A') }}">
                                        {{ $notification->created_at->isToday() || $notification->created_at->isYesterday()
                                            ? $notification->created_at->format('g:i A')
                                            : $notification->created_at->format('M j, g:i A') }}
                                    </time>
                                </div>
                                <p class="notif-message">{{ \Illuminate\Support\Str::limit($notification->message, 200) }}</p>
                                @unless ($notification->is_read)
                                    <div class="notif-actions">
                                        <span class="notif-new">New</span>
                                        <button type="button" class="notif-mark" onclick="markAsRead({{ $notification->id }}, event)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                            Mark as read
                                        </button>
                                    </div>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        <div class="notif-empty notif-filter-empty">
            <div class="notif-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>
            </div>
            <h2>No unread notifications</h2>
            <p>Everything on this page has been read.</p>
        </div>

        <div class="notif-pagination">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="notif-empty">
            <div class="notif-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            </div>
            <h2>You're all caught up</h2>
            <p>Updates about your requests, reservations and maintenance tickets will appear here.</p>
        </div>
    @endif
</div>

<script>
    // Filter tabs (current page only)
    document.querySelectorAll('.notif-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.notif-tab').forEach(t => t.setAttribute('aria-selected', String(t === tab)));
            document.getElementById('notif-page').classList.toggle('show-unread', tab.dataset.filter === 'unread');
        });
    });

    function markAsRead(notificationId, event) {
        const button = event.currentTarget;
        button.disabled = true;

        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const item = document.getElementById(`notification-${notificationId}`);
                item.classList.remove('is-unread');
                const actions = item.querySelector('.notif-actions');
                if (actions) actions.remove();

                // Keep the counters (tab, summary, group, bell badge) in step
                const page = document.getElementById('notif-page');
                const pageCount = document.querySelector('[data-unread-page]');
                if (pageCount) pageCount.textContent = Math.max(0, parseInt(pageCount.textContent, 10) - 1);
                const group = item.closest('.notif-group');
                if (group && !group.querySelector('.notif-item.is-unread')) group.classList.add('has-no-unread');
                if (!page.querySelector('.notif-item.is-unread')) page.classList.add('no-unread-left');

                const total = document.querySelector('[data-unread-total]');
                if (total) {
                    const left = Math.max(0, parseInt(total.textContent, 10) - 1);
                    if (left === 0) {
                        document.getElementById('notif-summary').textContent = "You're all caught up.";
                    } else {
                        document.getElementById('notif-summary').innerHTML = `You have <strong data-unread-total>${left}</strong> unread notification${left === 1 ? '' : 's'}.`;
                    }
                }
                const badge = document.querySelector('.fms-bell-badge');
                if (badge) {
                    const n = parseInt(badge.textContent, 10);
                    if (!isNaN(n)) { if (n <= 1) badge.remove(); else badge.textContent = n - 1; }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            const page = document.getElementById('notif-page');
            if (page && !page.querySelector('.notif-inline-error')) {
                const notice = document.createElement('div');
                notice.className = 'notif-inline-error';
                notice.setAttribute('role', 'alert');
                notice.textContent = 'Could not mark this notification as read. Please try again.';
                page.prepend(notice);
            }
        });
    }
</script>
@endsection

@section($notifSection)
    @yield('notifications-body')
@endsection
