{{-- Admin booking forms (Create Direct Reservation, View / Modify): equipment tiles, one-day date picker
     and venue availability board — same behaviour as the College/Org Facilities Utilization Request.
     Display only; it never changes what the form submits.
     Expects: #date_activity, #start_time, #end_time, #dp-*, #vb-day(-text), .vb-card, .eq-tile, input[name="facility_ids[]"]
     $ignore (optional): ['date' => Y-m-d, 'start' => H:i, 'end' => H:i, 'venues' => [names]] — the booking being edited,
     so it doesn't show up as a clash with itself. --}}
<script>
function adjustQty(fieldId, delta) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    let value = parseInt(input.value || '0', 10);
    value += delta;
    if (value < 0) value = 0;
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

window.bkPicker = (function () {
    const $ = (id) => document.getElementById(id);
    const pad = (n) => String(n).padStart(2, '0');
    const esc = (t) => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const venueBoxes = () => [...document.querySelectorAll('input[name="facility_ids[]"]')];
    const pickedVenues = () => venueBoxes().filter(b => b.checked).map(b => b.dataset.name);
    const ignore = @json($ignore ?? null);
    const ignoreVenues = ignore ? ignore.venues.map(v => v.toUpperCase()) : [];

    // ---- Equipment: highlight tiles in use, running total, quick-start sets ----
    (function () {
        const tiles = [...document.querySelectorAll('.eq-tile')];
        if (!tiles.length) return;
        function sync() {
            let pieces = 0, kinds = 0;
            tiles.forEach(tile => {
                let v = parseInt(tile.querySelector('.eq-count').value || '0', 10);
                if (isNaN(v) || v < 0) v = 0;
                tile.classList.toggle('is-on', v > 0);
                pieces += v; if (v > 0) kinds++;
            });
            if ($('eq-total')) {
                $('eq-total').textContent = pieces;
                $('eq-total-text').textContent = pieces
                    ? (pieces === 1 ? 'item' : 'items') + ' · ' + kinds + ' ' + (kinds === 1 ? 'type' : 'types')
                    : 'items selected';
            }
        }
        tiles.forEach(t => t.querySelector('.eq-count').addEventListener('input', sync));
        document.querySelectorAll('.eq-quick [data-preset]').forEach(btn => btn.addEventListener('click', () => {
            const preset = JSON.parse(btn.dataset.preset || '{}');
            tiles.forEach(tile => {
                const input = tile.querySelector('.eq-count');
                input.value = preset[tile.dataset.field] || 0;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }));
        sync();
    })();

    // ---- Date picker (one day). Booked days come from the admin Reservation calendar page. ----
    const cache = {};   // 'YYYY-MM' -> { counts: {date: n}, items: {date: [...]} }
    (function () {
        const input = $('date_activity');
        const grid = $('dp-grid');
        if (!input || !grid) return;
        const todayKey = new Date().toLocaleDateString('en-CA');
        const calendarUrl = @json(route('calendar.data'));
        const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        const keyOf = (y, m, d) => y + '-' + pad(m + 1) + '-' + pad(d);
        const fmt = (key, opts) => new Date(key + 'T00:00').toLocaleDateString(undefined, opts || { weekday: 'short', month: 'short', day: 'numeric' });
        const mins = (hm) => { if (!hm) return null; const [h, m] = hm.split(':').map(Number); return h * 60 + m; };
        const clock12 = (hm) => { const [h, m] = hm.split(':').map(Number); return ((h % 12) || 12) + ':' + pad(m) + (h < 12 ? ' AM' : ' PM'); };
        function describe(it) {
            const a = mins(it.start), b = mins(it.end);
            if (a !== null && b !== null && b > a) {
                const len = b - a, hrs = Math.round(len / 30) / 2;
                it.hours = hrs + (hrs === 1 ? ' hr' : ' hrs');
                it.until = clock12(it.end);
                it.whole = len >= 360;
                it.span = it.whole ? 'Whole day' : 'Half day' + (b <= 780 ? ' · Morning' : (a >= 720 ? ' · Afternoon' : ''));
            }
            return it;
        }
        const isIgnored = (date, it) => ignore && date === ignore.date && it.start === ignore.start && it.end === ignore.end
            && it.facility.split(',').some(f => ignoreVenues.includes(f.trim().toUpperCase()));
        const clockIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
        const personIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>';
        window.dpBookingHtml = function (it, opts) {
            opts = opts || {};
            let h = '<span class="bk">'
                + '<span class="bk-row"><span class="bk-time">' + clockIcon + esc(it.time || 'Time not set') + '</span>'
                + '<span class="bk-tag is-booked">Booked</span></span>';
            if (opts.facility) h += '<span class="bk-facility">' + esc(it.facility) + '</span>';
            if (it.event) h += '<span class="bk-event">' + esc(it.event) + '</span>';
            if (it.who) h += '<span class="bk-who">' + personIcon + esc(it.who) + '</span>';
            if (it.span || it.until) {
                h += '<span class="bk-row bk-foot">'
                    + (it.span ? '<span class="bk-tag ' + (it.whole ? 'is-whole' : 'is-half') + '">' + esc(it.span) + '</span>' : '<span></span>')
                    + (it.until ? '<span class="bk-until">Until <strong>' + esc(it.until) + '</strong> · ' + esc(it.hours) + '</span>' : '')
                    + '</span>';
            }
            return h + '</span>';
        };

        let view = (input.value || todayKey).slice(0, 7).split('-').map(Number);
        view = { y: view[0], m: view[1] - 1 };

        function loadMonth(y, m) {
            const ym = y + '-' + pad(m + 1);
            if (cache[ym]) return Promise.resolve(cache[ym]);
            return window.loadReservationCalendarMonth(calendarUrl, ym)
                .then(data => {
                    Object.keys(data.items).forEach(date => {
                        data.items[date] = data.items[date].map(describe).filter(it => {
                            if (!isIgnored(date, it)) return true;
                            if (data.counts[date]) data.counts[date]--;
                            if (!data.counts[date]) delete data.counts[date];
                            return false;
                        });
                    });
                    return (cache[ym] = data);
                });
        }

        function render() {
            const { y, m } = view;
            $('dp-month').textContent = monthNames[m] + ' ' + y;
            // Can't page back past this month, or past the booking's own (older) date when editing
            const floor = input.value && input.value < todayKey ? input.value.slice(0, 7) : todayKey.slice(0, 7);
            $('dp-prev').disabled = (y + '-' + pad(m + 1)) <= floor;
            grid.classList.add('is-loading');
            $('dp-status').textContent = 'Loading booked dates…';
            loadMonth(y, m).then(draw, () => {
                draw({ counts: {}, items: {} });
                $('dp-status').textContent = "Couldn't load booked dates. You can still pick a date.";
            });
        }

        function draw(data) {
            const { y, m } = view;
            const first = new Date(y, m, 1).getDay();
            const days = new Date(y, m + 1, 0).getDate();
            grid.innerHTML = '';
            for (let i = 0; i < first; i++) {
                const blank = document.createElement('span'); blank.className = 'dp-day is-out'; grid.append(blank);
            }
            for (let d = 1; d <= days; d++) {
                const key = keyOf(y, m, d);
                const count = data.counts[key] || 0;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dp-day' + (count ? ' has-events' : '') + (key === todayKey ? ' is-today' : '');
                btn.dataset.date = key;
                btn.dataset.count = count;
                btn.disabled = key < todayKey && key !== input.value;
                btn.innerHTML = d + (count ? '<span class="dp-count">' + count + '</span>' : '');
                if (count) btn.title = (data.items[key] || []).map(it => it.facility + ' · ' + it.time).join('\n') || count + ' reservation(s)';
                btn.addEventListener('click', () => {
                    input.value = key;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                grid.append(btn);
            }
            grid.classList.remove('is-loading');
            const booked = Object.keys(data.counts).length;
            $('dp-status').textContent = booked ? booked + ' day' + (booked > 1 ? 's' : '') + ' this month already have reservations.' : 'No reservations yet this month.';
            paintGrid();
            announce();
        }

        function paintGrid() {
            grid.querySelectorAll('.dp-day[data-date]').forEach(btn => {
                const k = btn.dataset.date, count = +btn.dataset.count, on = k === input.value;
                btn.classList.toggle('is-selected', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                btn.setAttribute('aria-label', fmt(k, { weekday: 'long', month: 'long', day: 'numeric' })
                    + (count ? ', ' + count + ' reservation' + (count > 1 ? 's' : '') : ', no reservations')
                    + (btn.disabled ? ', not available' : ''));
            });
        }

        // "On this day" panel + tell the venue board which day is chosen
        function announce() {
            const key = input.value;
            const box = $('dp-dayinfo');
            if (!key) {
                box.innerHTML = '<h3>On this day</h3><p>Pick a date to see what\'s already booked.</p>';
                document.dispatchEvent(new CustomEvent('dp:day', { detail: { date: '', items: [] } }));
                return;
            }
            const [yy, mm] = key.split('-').map(Number);
            loadMonth(yy, mm - 1).catch(() => null).then(() => {
                const data = cache[key.slice(0, 7)] || { counts: {}, items: {} };
                const items = data.items[key] || [];
                const count = data.counts[key] || 0;
                const venues = pickedVenues().map(v => v.toUpperCase());
                let html = '<h3>' + fmt(key, { weekday: 'short', month: 'long', day: 'numeric' }) + '</h3>';
                if (!count && !items.length) {
                    html += '<p class="dp-free"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>No ' + (ignore ? 'other ' : '') + 'reservations — this date is open.</p>';
                } else {
                    const list = items.length ? items : [{ time: '', facility: count + ' reservation(s)' }];
                    let clash = false;
                    html += '<ul>' + list.map(it => {
                        const hit = venues.some(v => it.facility.toUpperCase().includes(v));
                        if (hit) clash = true;
                        return '<li class="bk-item' + (hit ? ' is-clash' : '') + '">' + window.dpBookingHtml(it, { facility: true }) + '</li>';
                    }).join('') + '</ul>';
                    if (clash) html += '<p class="dp-warn">A venue you picked already has a booking this day. Check the times don\'t overlap.</p>';
                }
                box.innerHTML = html;
                document.dispatchEvent(new CustomEvent('dp:day', { detail: { date: key, items } }));
            });
        }

        $('dp-prev').addEventListener('click', () => { view.m--; if (view.m < 0) { view.m = 11; view.y--; } render(); });
        $('dp-next').addEventListener('click', () => { view.m++; if (view.m > 11) { view.m = 0; view.y++; } render(); });
        input.addEventListener('change', () => {
            const key = input.value;
            if (key) {
                const [yy, mm] = key.split('-').map(Number);
                if (yy !== view.y || mm - 1 !== view.m) { view = { y: yy, m: mm - 1 }; render(); return; }
            }
            paintGrid(); announce();
        });
        venueBoxes().forEach(b => b.addEventListener('change', announce));
        render();
    })();

    // ---- Venue availability board (display only) ----
    (function () {
        const cards = [...document.querySelectorAll('.vb-card')];
        if (!cards.length) return;
        const toMin = (t) => { const m = /(\d{1,2}):(\d{2})\s*(AM|PM)/i.exec(t || ''); if (!m) return null; let h = +m[1] % 12; if (/pm/i.test(m[3])) h += 12; return h * 60 + +m[2]; };
        const rangeOf = (txt) => { const parts = String(txt).split(/[–-]/); return [toMin(parts[0]), toMin(parts[1])]; };
        const hhmm = (v) => { if (!v) return null; const [h, m] = v.split(':').map(Number); return h * 60 + m; };
        let current = { date: '', items: [] };

        function paint() {
            const start = hhmm($('start_time').value), end = hhmm($('end_time').value);
            const text = $('vb-day-text'), pill = $('vb-day');
            if (current.date) {
                text.textContent = 'Availability on ' + new Date(current.date + 'T00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
                pill.classList.add('is-set');
            } else {
                text.textContent = 'Pick a date in step 1 to see booked times';
                pill.classList.remove('is-set');
            }
            cards.forEach(card => {
                const name = card.dataset.name.toUpperCase();
                const input = card.querySelector('input');
                const slotsBox = card.querySelector('.vb-slots');
                const status = card.querySelector('.vb-status');
                // A booking row can list several facilities ("A, B"), so match on each name
                const mine = current.items.filter(it => it.facility.split(',').map(x => x.trim().toUpperCase()).includes(name));
                card.classList.toggle('is-booked', mine.length > 0);
                card.querySelector('.vb-count-num').textContent = current.date ? mine.length : '–';
                if (input.disabled) { card.classList.remove('is-clash'); return; }
                if (!current.date) slotsBox.innerHTML = '<span class="vb-idle">Pick a date to see times</span>';
                else if (!mine.length) slotsBox.innerHTML = '<span class="vb-free">Free all day</span>';
                else slotsBox.innerHTML = mine.map(it => window.dpBookingHtml(it)).join('');
                status.textContent = !current.date || !mine.length ? 'Available' : (mine.some(it => it.whole) ? 'Booked whole day' : 'Partly booked');
                status.className = 'pk-pill vb-status ' + (!current.date || !mine.length ? 'is-good' : (mine.some(it => it.whole) ? 'is-bad' : 'is-warn'));
                const clash = start !== null && end !== null && mine.some(it => { const [a, b] = rangeOf(it.time); return a !== null && b !== null && a < end && b > start; });
                card.classList.toggle('is-clash', clash);
            });
        }
        document.addEventListener('dp:day', (e) => { current = e.detail; paint(); });
        ['start_time', 'end_time'].forEach(id => $(id).addEventListener('input', paint));
        paint();
    })();

    return { pickedVenues, venueBoxes, pad };
})();
</script>
