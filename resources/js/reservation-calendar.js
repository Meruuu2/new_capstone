const monthCache = new Map();

window.ReservationCalendar = {
    async loadMonth(url, month) {
        const key = `${url}:${month}`;
        if (!monthCache.has(key)) {
            monthCache.set(key, fetch(`${url}?month=${encodeURIComponent(month)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            }).then(async response => {
                if (!response.ok) {
                    throw new Error(`Calendar data request failed (${response.status}).`);
                }

                const payload = await response.json();
                if (payload.month !== month || !payload.days || typeof payload.days !== 'object') {
                    throw new Error('Calendar data response was invalid.');
                }

                const counts = {};
                const items = {};
                Object.entries(payload.days).forEach(([date, day]) => {
                    counts[date] = Number(day.count) || 0;
                    items[date] = Array.isArray(day.items) ? day.items : [];
                });

                return { counts, items };
            }).catch(error => {
                monthCache.delete(key);
                throw error;
            }));
        }

        const monthData = await monthCache.get(key);

        return {
            counts: { ...monthData.counts },
            items: Object.fromEntries(
                Object.entries(monthData.items).map(([date, items]) => [
                    date,
                    items.map(item => ({ ...item })),
                ]),
            ),
        };
    },
};

window.dispatchEvent(new Event('reservation-calendar:ready'));
