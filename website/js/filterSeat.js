const eventSelect = document.getElementById('eventSelect');
const seatSelect = document.getElementById('seatSelect');

function setSeatOptions(options) {
    seatSelect.innerHTML = '';
    if (!options || options.length === 0) {
	console.log(options);
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'No available seats for this event';
        seatSelect.appendChild(opt);
        seatSelect.disabled = true;
        return;
    }
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Select a seat…';
    seatSelect.appendChild(placeholder);

    options.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.seat_id;
        const meta = [s.venue, (s.section || ''), (s.row_label || '')].filter(Boolean).join(' ');
        opt.textContent = `${s.seat_label} - ${meta}`;
        seatSelect.appendChild(opt);
    });
    seatSelect.disabled = false;
}

eventSelect.addEventListener('change', async () => {
    const eventId = eventSelect.value;
    seatSelect.disabled = true;
    seatSelect.innerHTML = '';
    const loading = document.createElement('option');
    loading.value = '';
    loading.textContent = eventId ? 'Loading seats…' : 'Select an event first…';
    seatSelect.appendChild(loading);

    if (!eventId) return;

    try {
        const res = await fetch(`../utils/fetch_seats_by_event.php?event_id=${encodeURIComponent(eventId)}`);
        if (!res.ok) throw new Error('Failed to fetch seats');
        const data = await res.json();
	console.log(res);
        setSeatOptions(data);
    } catch (err) {
        seatSelect.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Could not load seats (try again)';
        seatSelect.appendChild(opt);
        seatSelect.disabled = true;
    }
});