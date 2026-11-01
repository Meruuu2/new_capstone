{{-- Repair ticket conversation between the admins and the college staff who sent the ticket.
     Used on the Admin ticket page and the College ticket page (layouts/partials/ticket-detail).
     $ticket    : MaintenanceTicket with messages.user loaded
     $fetchUrl  : GET route returning new messages as JSON (?after=<id>); used for fallback polling and after real-time events
     $sendUrl   : POST route that adds a message (works as a plain form without JavaScript)
     $otherSide : who is on the other end, e.g. "GSU admins" or "John C. Amar" --}}
@php
    $closed = $ticket->isClosed();
    $closedLabel = $ticket->status === 'success' ? 'Completed' : 'Not completed';
    $initialsOf = fn ($name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $lastId = $ticket->messages->max('id') ?? 0;
@endphp

<section class="rqd-card tcv" id="conversation" aria-labelledby="tcv-title"
         data-fetch="{{ $fetchUrl }}" data-send="{{ $sendUrl }}" data-last="{{ $lastId }}" data-ticket-id="{{ $ticket->id }}" data-closed="{{ $closed ? '1' : '0' }}">
    <div class="tcv-head">
        <div class="u-minw-0">
            <h2 class="rqd-card-title tcv-title" id="tcv-title">Conversation</h2>
            <p class="tcv-with">With {{ $otherSide }}</p>
        </div>
        <span class="tcv-live {{ $closed ? 'is-off' : '' }}" id="tcv-live">
            <i aria-hidden="true"></i><span>{{ $closed ? 'Closed' : 'Live' }}</span>
        </span>
    </div>

    <ol class="tcv-list" id="tcv-list" aria-live="polite" aria-label="Messages">
        @foreach ($ticket->messages as $m)
            @php
                $mine = $m->user_id === auth()->id();
                $isAdmin = (bool) $m->user?->isAdmin();
                $role = $isAdmin ? ($m->user->position ?: 'GSU Admin') : ($m->user?->college_name ?: 'College staff');
            @endphp
            <li class="tcv-msg {{ $mine ? 'is-mine' : '' }} {{ $isAdmin ? 'is-admin' : 'is-college' }}" data-id="{{ $m->id }}">
                <span class="tcv-avatar" aria-hidden="true">{{ $initialsOf($m->user?->name ?? '?') }}</span>
                <div class="tcv-bubble-wrap">
                    <p class="tcv-meta"><strong>{{ $mine ? 'You' : ($m->user?->name ?? 'Deleted account') }}</strong><span>{{ $role }}</span><time datetime="{{ $m->created_at?->toIso8601String() }}">{{ $m->created_at?->format('M j, g:i A') }}</time></p>
                    <p class="tcv-bubble">{{ $m->body }}</p>
                </div>
            </li>
        @endforeach
    </ol>
    <p class="tcv-empty" id="tcv-empty" @if ($ticket->messages->isNotEmpty()) hidden @endif>
        No messages yet. Ask a question or share an update — {{ $otherSide }} will see it here.
    </p>

    <p class="tcv-closed" id="tcv-closed" @if (!$closed) hidden @endif>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
        <span>This ticket is <strong id="tcv-closed-label">{{ $closedLabel }}</strong>, so the conversation is read-only.</span>
    </p>

    <form method="POST" action="{{ $sendUrl }}" class="tcv-form" id="tcv-form" @if ($closed) hidden @endif>
        @csrf
        <label for="tcv-body" class="pk-sr">Message</label>
        <textarea id="tcv-body" name="body" rows="1" maxlength="2000" required class="tcv-input"
                  placeholder="Write a message…" aria-describedby="tcv-hint">{{ old('body') }}</textarea>
        <button type="submit" class="tcv-send" id="tcv-send">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg>
            <span>Send</span>
        </button>
    </form>
    <p class="tcv-hint" id="tcv-hint" @if ($closed) hidden @endif>Enter to send · Shift + Enter for a new line</p>
    @error('body')<p class="pk-error">{{ $message }}</p>@enderror
    <p class="pk-error" id="tcv-error" role="alert" hidden></p>
</section>

<script>
(function () {
    const box = document.getElementById('conversation');
    if (!box) return;
    const list = document.getElementById('tcv-list');
    const empty = document.getElementById('tcv-empty');
    const form = document.getElementById('tcv-form');
    const input = document.getElementById('tcv-body');
    const send = document.getElementById('tcv-send');
    const errorEl = document.getElementById('tcv-error');
    const csrf = form.querySelector('input[name="_token"]').value;
    let lastId = parseInt(box.dataset.last || '0', 10);
    let closed = box.dataset.closed === '1';
    let busy = false;

    const initials = (name) => (name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('') || '?';
    const atBottom = () => list.scrollHeight - list.scrollTop - list.clientHeight < 60;
    const toBottom = () => { list.scrollTop = list.scrollHeight; };

    function render(m) {
        if (list.querySelector('[data-id="' + m.id + '"]')) return;
        const li = document.createElement('li');
        li.className = 'tcv-msg' + (m.mine ? ' is-mine' : '') + (m.side === 'admin' ? ' is-admin' : ' is-college') + ' is-new';
        li.dataset.id = m.id;
        const avatar = document.createElement('span');
        avatar.className = 'tcv-avatar'; avatar.setAttribute('aria-hidden', 'true'); avatar.textContent = initials(m.author);
        const wrap = document.createElement('div'); wrap.className = 'tcv-bubble-wrap';
        const meta = document.createElement('p'); meta.className = 'tcv-meta';
        const who = document.createElement('strong'); who.textContent = m.mine ? 'You' : m.author;
        const role = document.createElement('span'); role.textContent = m.role;
        const time = document.createElement('time'); time.dateTime = m.iso || ''; time.textContent = m.time || '';
        meta.append(who, role, time);
        const bubble = document.createElement('p'); bubble.className = 'tcv-bubble'; bubble.textContent = m.body;
        wrap.append(meta, bubble);
        li.append(avatar, wrap);
        list.append(li);
        lastId = Math.max(lastId, m.id);
        empty.hidden = true;
    }

    function setClosed(status) {
        closed = true;
        form.hidden = true;
        document.getElementById('tcv-hint').hidden = true;
        document.getElementById('tcv-closed').hidden = false;
        document.getElementById('tcv-closed-label').textContent = status === 'success' ? 'Completed' : 'Not completed';
        const live = document.getElementById('tcv-live');
        live.classList.add('is-off');
        live.querySelector('span').textContent = 'Closed';
    }

    // An admin moved the ticket back to Pending / Ongoing: the conversation opens again
    function setOpen() {
        closed = false;
        form.hidden = false;
        document.getElementById('tcv-hint').hidden = false;
        document.getElementById('tcv-closed').hidden = true;
        const live = document.getElementById('tcv-live');
        live.classList.remove('is-off');
        live.querySelector('span').textContent = 'Live';
    }

    function grow() { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 160) + 'px'; }

    function connectRealtime() {
        if (!window.Echo) {
            window.addEventListener('echo:ready', connectRealtime, { once: true });
            return;
        }

        window.Echo.private('repair-ticket.' + box.dataset.ticketId)
            .listen('.repair-ticket.message-created', function (event) {
                if (Number(event.ticket_id) === Number(box.dataset.ticketId)) poll(true);
            });
    }

    async function poll(force = false) {
        if (document.hidden && !force) return;
        try {
            const r = await fetch(box.dataset.fetch + '?after=' + lastId, { headers: { Accept: 'application/json' } });
            if (!r.ok) return;
            const data = await r.json();
            const stick = atBottom();
            (data.messages || []).forEach(render);
            if (data.messages && data.messages.length && stick) toBottom();
            if (data.closed && !closed) setClosed(data.status);
            if (!data.closed && closed) setOpen();
        } catch (e) { /* offline for a moment: try again on the next tick */ }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const body = input.value.trim();
        if (!body || busy || closed) return;
        busy = true; send.disabled = true; errorEl.hidden = true;
        try {
            const r = await fetch(box.dataset.send, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ body: body })
            });
            const data = await r.json().catch(() => ({}));
            if (r.ok) {
                render(data.message);
                input.value = ''; grow(); toBottom();
            } else {
                errorEl.textContent = (data.errors && data.errors.body && data.errors.body[0]) || data.message
                    || (r.status === 429 ? 'You\'re sending messages too fast. Wait a moment and try again.' : 'Couldn\'t send your message. Try again.');
                errorEl.hidden = false;
                if (r.status === 422 && /closed/i.test(data.message || '')) setClosed();
            }
        } catch (err) {
            errorEl.textContent = 'Couldn\'t send your message. Check your connection and try again.';
            errorEl.hidden = false;
        } finally {
            busy = false; send.disabled = false; input.focus();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit(); }
    });
    input.addEventListener('input', grow);

    toBottom();
    grow();
    connectRealtime();
    setInterval(poll, 10000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
})();
</script>
