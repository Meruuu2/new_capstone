{{-- Flash messages as a pop-up toast (top-right).
     Success (session 'status') closes by itself after 5 seconds; errors (session 'error') stay until closed. --}}
@php
    $toasts = [];
    // A submitted facility request shows its ticket pop-up instead (portal-shell), so skip the duplicate toast there
    $ticketShown = session('status') === 'Facilities utilization request submitted to GSU.'
        && auth()->check() && (auth()->user()->isCollegeStaff() || auth()->user()->isOrgStaff());
    if (session('status') && ! $ticketShown) $toasts[] = ['type' => 'success', 'title' => 'Success', 'text' => session('status')];
    if (session('error'))  $toasts[] = ['type' => 'error',   'title' => 'Something went wrong', 'text' => session('error')];
@endphp
@if (count($toasts))
<div class="ua-toasts" aria-live="polite">
    @foreach ($toasts as $toast)
        <div class="ua-toast {{ $toast['type'] === 'error' ? 'is-error' : '' }}" role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}" data-autoclose="{{ $toast['type'] === 'success' ? 5000 : 0 }}">
            <span class="ua-toast-icon" aria-hidden="true">
                @if ($toast['type'] === 'error')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                @endif
            </span>
            <div>
                <p class="ua-toast-title">{{ $toast['title'] }}</p>
                <p class="ua-toast-text">{{ $toast['text'] }}</p>
            </div>
            <button type="button" class="ua-toast-close" aria-label="Dismiss notification">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
            @if ($toast['type'] === 'success')<span class="ua-toast-timer" aria-hidden="true"></span>@endif
        </div>
    @endforeach
</div>
<script>
(function () {
    document.querySelectorAll('.ua-toast').forEach(function (toast) {
        let closed = false;
        const close = function () {
            if (closed) return; closed = true;
            toast.classList.add('is-leaving');
            setTimeout(function () { toast.remove(); }, 300);
        };
        toast.querySelector('.ua-toast-close').addEventListener('click', close);
        const ms = parseInt(toast.dataset.autoclose, 10);
        if (!ms) return;
        // The countdown bar drives the timing, so hovering pauses both
        const bar = toast.querySelector('.ua-toast-timer');
        if (bar) bar.addEventListener('animationend', close);
        else setTimeout(close, ms);
    });
})();
</script>
@endif
