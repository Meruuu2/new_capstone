{{-- "Other equipment": free-text items not in the list above, each with a quantity.
     Submits other_equipment[i][name] / other_equipment[i][qty]. Pass $others (saved rows) when editing. --}}
@php
    $otherRows = old('other_equipment', $others ?? []);
    $otherRows = is_array($otherRows) ? array_values(array_filter($otherRows, 'is_array')) : [];
@endphp
<div class="eqo" id="eqo">
    <div class="eqo-head">
        <div>
            <h3 class="eqo-title">Other equipment</h3>
            <p class="eqo-sub">Need something not listed above? Type it in with the quantity.</p>
        </div>
        <button type="button" class="pk-btn-outline eqo-add" id="eqo-add">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Add item
        </button>
    </div>
    <div class="eqo-list" id="eqo-list">
        @foreach ($otherRows as $i => $row)
            <div class="eqo-row">
                <input type="text" class="pk-input" name="other_equipment[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" maxlength="100" placeholder="e.g., Extension cord, Projector screen" aria-label="Other equipment name">
                <input type="number" class="pk-input eqo-qty eq-count" name="other_equipment[{{ $i }}][qty]" value="{{ $row['qty'] ?? 1 }}" min="1" max="10000" inputmode="numeric" aria-label="Quantity" data-label="{{ $row['name'] ?? '' }}">
                <button type="button" class="eqo-remove" aria-label="Remove item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
        @endforeach
    </div>
    <p class="eqo-empty" id="eqo-empty" @if (count($otherRows)) hidden @endif>No other equipment added.</p>
    @if ($errors->has('other_equipment') || $errors->has('other_equipment.*'))
        <p class="pk-error">{{ $errors->first('other_equipment') ?: collect($errors->get('other_equipment.*'))->flatten()->first() }}</p>
    @endif
    <template id="eqo-template">
        <div class="eqo-row">
            <input type="text" class="pk-input" data-name="name" maxlength="100" placeholder="e.g., Extension cord, Projector screen" aria-label="Other equipment name">
            <input type="number" class="pk-input eqo-qty eq-count" data-name="qty" value="1" min="1" max="10000" inputmode="numeric" aria-label="Quantity" data-label="">
            <button type="button" class="eqo-remove" aria-label="Remove item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
<script>
(function () {
    const list = document.getElementById('eqo-list');
    const empty = document.getElementById('eqo-empty');
    const addBtn = document.getElementById('eqo-add');
    const MAX = 10;
    let next = list.children.length;
    // Changes here must re-run the form's summary, which listens for bubbling input events.
    const notify = () => list.dispatchEvent(new Event('input', { bubbles: true }));
    function refresh() {
        empty.hidden = list.children.length > 0;
        addBtn.disabled = list.children.length >= MAX;
    }
    addBtn.addEventListener('click', () => {
        const row = document.getElementById('eqo-template').content.firstElementChild.cloneNode(true);
        row.querySelectorAll('[data-name]').forEach(el => { el.name = 'other_equipment[' + next + '][' + el.dataset.name + ']'; });
        next++;
        list.appendChild(row);
        row.querySelector('input').focus();
        refresh();
    });
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('.eqo-remove');
        if (!btn) return;
        btn.closest('.eqo-row').remove();
        refresh();
        notify();
        addBtn.focus();
    });
    // Keep the qty field's label in step with the typed name (the form summaries read data-label).
    list.addEventListener('input', (e) => {
        if (e.target.type !== 'text') return;
        e.target.closest('.eqo-row').querySelector('.eqo-qty').dataset.label = e.target.value.trim();
    });
    refresh();
})();
</script>
