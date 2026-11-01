{{-- Needs partials.number-of-persons, qty_* equipment inputs, and venue checkboxes (facility_ids[]) carrying
     data-capacity / data-equipment from App\Support\VenueRates. --}}
<script>
// Picking venues fills in their combined seating capacity and suggested equipment (package tables/fans,
// a sound system each, a rostrum for the big halls); chairs follow the number of persons. All stay editable.
(function () {
    const persons = document.getElementById('number_of_persons');
    if (!persons) return;
    const venueBoxes = Array.from(document.querySelectorAll('input[name="facility_ids[]"]'));
    const setQty = (id, value) => {
        const input = document.getElementById(id);
        if (!input) return;
        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };
    venueBoxes.forEach(box => box.addEventListener('change', function () {
        const venues = venueBoxes.filter(venue => venue.checked);
        const equipment = { qty_table: 0, qty_fan: 0, qty_rostrum: 0, qty_sound: 0 };
        venues.forEach(venue => {
            const items = JSON.parse(venue.dataset.equipment || '{}');
            Object.keys(equipment).forEach(key => { equipment[key] += items[key] || 0; });
        });
        Object.keys(equipment).forEach(key => setQty(key, equipment[key]));
        const total = venues.reduce((sum, venue) => sum + (parseInt(venue.dataset.capacity, 10) || 0), 0);
        persons.value = total || '';
        persons.dispatchEvent(new Event('input', { bubbles: true }));
    }));
    persons.addEventListener('input', () => setQty('qty_monobloc', parseInt(persons.value, 10) || 0));
    document.querySelectorAll('.ga-step-btn').forEach(btn => btn.addEventListener('click', function () {
        persons.value = Math.min(100000, Math.max(1, (parseInt(persons.value, 10) || 0) + Number(btn.dataset.step)));
        persons.dispatchEvent(new Event('input', { bubbles: true }));
    }));
})();
</script>
