<script>
(function () {
    const panel = document.getElementById('time-availability');
    const form = document.getElementById('rental-form') || document.getElementById('request-form');
    if (!panel || !form) return;

    let schedule = { start: '', end: '', days: [] };
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');
    const dateInput = document.getElementById('date_activity');
    const facilityInputs = Array.from(form.querySelectorAll('input[name="facility_ids[]"]'));
    const message = panel.querySelector('p');

    function minutes(value) {
        if (!value) return null;
        const [hours, mins] = value.split(':').map(Number);
        return hours * 60 + mins;
    }

    function bookedRange(item) {
        const parts = String(item.time || '').split(/[–-]/);
        const parse = value => {
            const match = /(\d{1,2}):(\d{2})\s*(AM|PM)/i.exec(value || '');
            if (!match) return null;
            let hours = Number(match[1]) % 12;
            if (/pm/i.test(match[3])) hours += 12;
            return hours * 60 + Number(match[2]);
        };
        const from = parse(parts[0]);
        const until = parse(parts[1]);
        return from === null || until === null ? [0, 1440] : [from, until];
    }

    function describeDate(date) {
        return new Date(date + 'T00:00').toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function render() {
        const selected = facilityInputs.filter(input => input.checked).map(input => input.dataset.name.toUpperCase());
        const from = minutes(startInput.value);
        const until = minutes(endInput.value);
        const endDateInput = document.getElementById('date_end');
        const selectedStart = dateInput.value;
        const selectedEnd = endDateInput && !endDateInput.disabled && endDateInput.value
            ? endDateInput.value
            : selectedStart;
        const conflicts = [];

        if (schedule.start === selectedStart && schedule.end === selectedEnd && selected.length && from !== null && until !== null && until > from) {
            schedule.days.forEach(day => (day.items || []).forEach(item => {
                const bookedFacilities = String(item.facility || '').split(',').map(name => name.trim().toUpperCase());
                if (!bookedFacilities.some(name => selected.includes(name))) return;
                const [bookedFrom, bookedUntil] = bookedRange(item);
                if (bookedFrom < until && bookedUntil > from) {
                    conflicts.push({
                        date: day.date,
                        facility: bookedFacilities.find(name => selected.includes(name)),
                        time: item.time || 'All day'
                    });
                }
            }));
        }

        panel.classList.remove('is-available', 'is-conflict');
        panel.dataset.conflict = 'false';

        if (!selectedStart) {
            panel.querySelector('strong').textContent = 'Availability check';
            message.textContent = 'Choose a date, facility and time to check for conflicts.';
            return;
        }

        if (schedule.start !== selectedStart || schedule.end !== selectedEnd) {
            panel.querySelector('strong').textContent = 'Loading availability';
            message.textContent = 'Checking reservations for the selected dates.';
            return;
        }

        if (!selected.length || from === null || until === null) {
            panel.querySelector('strong').textContent = 'Availability check';
            message.textContent = 'Select facilities and enter start and end times. Existing reservations are listed on the venue cards.';
            return;
        }

        if (until <= from) {
            panel.classList.add('is-conflict');
            panel.dataset.conflict = 'true';
            panel.querySelector('strong').textContent = 'End time must be later';
            message.textContent = 'Choose an end time after the start time.';
            return;
        }

        if (conflicts.length) {
            panel.classList.add('is-conflict');
            panel.dataset.conflict = 'true';
            panel.querySelector('strong').textContent = 'Time unavailable';
            const details = conflicts.slice(0, 3).map(conflict =>
                conflict.facility + ' · ' + describeDate(conflict.date) + ' · ' + conflict.time
            );
            const remainder = conflicts.length > details.length ? ' +' + (conflicts.length - details.length) + ' more conflict(s)' : '';
            message.textContent = 'Overlaps an existing reservation: ' + details.join('; ') + remainder + '. Choose a different time.';
            return;
        }

        panel.classList.add('is-available');
        panel.querySelector('strong').textContent = 'Time available';
        message.textContent = 'No overlapping reservations for the selected facilities and ' +
            (schedule.days.length > 1 ? 'all ' + schedule.days.length + ' event days.' : 'selected day.');
    }

    document.addEventListener('dp:range', event => {
        schedule = {
            start: event.detail.start || '',
            end: event.detail.end || '',
            days: event.detail.days || []
        };
        render();
    });
    [startInput, endInput, dateInput, ...facilityInputs].forEach(input => {
        input.addEventListener('input', render);
        input.addEventListener('change', render);
    });

    form.addEventListener('submit', event => {
        if (panel.dataset.conflict !== 'true') return;
        event.preventDefault();
        event.stopImmediatePropagation();
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, true);

    render();
})();
</script>
