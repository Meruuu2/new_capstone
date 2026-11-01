{{-- Equipment tiles (same UI as the College / Org request forms): running total, quick-start sets, ± steppers.
     Inputs keep the qty_* names the facilities form submits. --}}
@php
    $equipmentFields = [
        'qty_monobloc' => 'Monobloc Chair',
        'qty_table'    => 'Table',
        'qty_fan'      => 'Electric Fan',
        'qty_rostrum'  => 'Rostrum',
        'qty_flag'     => 'Flag & School Color',
        'qty_sound'    => 'Sound',
        'qty_led'      => 'LED Wall',
    ];
    $equipmentIcons = [
        'qty_monobloc' => '<path d="M7 3h10v8H7z"/><path d="M5 11h14M7 11v10M17 11v10M7 16h10"/>',
        'qty_table'    => '<path d="M3 8h18M5 8v12M19 8v12M3 8l2-3h14l2 3"/>',
        'qty_fan'      => '<circle cx="12" cy="10" r="1.6"/><path d="M12 8.4c0-3 1-5 3-5s2 3-1.4 5.2M13.4 11c2.6 1.5 3.9 3.3 2.9 5s-3.4-.3-4-3.7M10.6 10.9c-2.6 1.5-4.8 1.6-5.8-.1s2-2.8 5.2-1.6"/><path d="M12 12v8M8 21h8"/>',
        'qty_rostrum'  => '<path d="M6 8h12l-1.5 13h-9z"/><path d="M4 5h16v3H4zM12 5V3"/>',
        'qty_flag'     => '<path d="M5 21V4M5 4h11l-2 4 2 4H5"/>',
        'qty_sound'    => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="14" r="3"/><circle cx="12" cy="7.5" r="1"/>',
        'qty_led'      => '<rect x="2" y="4" width="20" height="13" rx="1.5"/><path d="M8 21h8M12 17v4M6 8h4M6 11h7"/>',
    ];
@endphp
<div class="eq-bar">
    <div class="eq-total" aria-live="polite"><strong id="eq-total">0</strong><span id="eq-total-text">items selected</span></div>
    <div class="eq-quick">
        <span class="eq-quick-label">Quick start</span>
        <button type="button" data-preset='{"qty_monobloc":20,"qty_table":2,"qty_fan":2}'>Small meeting</button>
        <button type="button" data-preset='{"qty_monobloc":100,"qty_table":4,"qty_rostrum":1,"qty_flag":1,"qty_sound":1}'>Seminar</button>
        <button type="button" data-preset='{"qty_monobloc":300,"qty_table":10,"qty_fan":4,"qty_rostrum":1,"qty_flag":1,"qty_sound":1,"qty_led":1}'>Big program</button>
        <button type="button" data-preset='{}'>Clear</button>
    </div>
</div>
<div class="eq-grid">
    @foreach($equipmentFields as $name => $label)
        <div class="eq-tile" data-field="{{ $name }}">
            <div class="eq-head">
                <span class="eq-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $equipmentIcons[$name] !!}</svg></span>
                <label for="{{ $name }}" class="eq-name">{{ $label }}</label>
            </div>
            <div class="eq-ctrl">
                <button type="button" class="eq-round" data-qty="{{ $name }}" data-delta="-1" aria-label="{{ $label }} minus 1">&minus;</button>
                <input type="number" class="eq-count" name="{{ $name }}" id="{{ $name }}" min="0" inputmode="numeric" value="{{ old($name, 0) }}" data-label="{{ $label }}">
                <button type="button" class="eq-round" data-qty="{{ $name }}" data-delta="1" aria-label="{{ $label }} plus 1">+</button>
            </div>
            <div class="eq-more">
                <button type="button" data-qty="{{ $name }}" data-delta="-10" aria-label="{{ $label }} minus 10">&minus;10</button>
                <button type="button" data-qty="{{ $name }}" data-delta="10" aria-label="{{ $label }} plus 10">+10</button>
            </div>
        </div>
    @endforeach
</div>
<script>
// Equipment: ± buttons, highlight tiles in use, running total, quick-start sets
(function () {
    const tiles = Array.from(document.querySelectorAll('.eq-tile'));
    const fire = (input) => input.dispatchEvent(new Event('input', { bubbles: true }));
    function sync() {
        let pieces = 0, kinds = 0;
        tiles.forEach(tile => {
            const v = Math.max(0, parseInt(tile.querySelector('.eq-count').value || '0', 10) || 0);
            tile.classList.toggle('is-on', v > 0);
            pieces += v; if (v > 0) kinds++;
        });
        document.getElementById('eq-total').textContent = pieces;
        document.getElementById('eq-total-text').textContent = pieces
            ? (pieces === 1 ? 'item' : 'items') + ' · ' + kinds + ' ' + (kinds === 1 ? 'type' : 'types')
            : 'items selected';
    }
    tiles.forEach(tile => tile.querySelector('.eq-count').addEventListener('input', sync));
    document.querySelectorAll('.eq-tile [data-qty]').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.qty);
        input.value = Math.max(0, (parseInt(input.value || '0', 10) || 0) + Number(btn.dataset.delta));
        fire(input);
    }));
    document.querySelectorAll('.eq-quick [data-preset]').forEach(btn => btn.addEventListener('click', () => {
        const preset = JSON.parse(btn.dataset.preset || '{}');
        tiles.forEach(tile => {
            const input = tile.querySelector('.eq-count');
            input.value = preset[tile.dataset.field] || 0;
            fire(input);
        });
    }));
    sync();
})();
</script>
