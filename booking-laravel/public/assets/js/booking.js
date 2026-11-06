// Form booking: memuat slot jam dari server dan memperbarui ringkasan.
(function () {
    const form = document.getElementById('bookingForm');
    if (!form) return;

    const slotGrid = document.getElementById('slotGrid');
    const dateInput = form.tanggal_booking;
    let selectedTime = form.dataset.selectedTime || '';
    let request = 0;

    const $ = (id) => document.getElementById(id);
    const checked = (name) => form.querySelector(`input[name="${name}"]:checked`);

    function formatDate(value) {
        if (!value) return '—';
        const d = new Date(value + 'T00:00:00');
        return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function updateSummary() {
        const service = checked('service_id');
        const therapist = checked('therapist_id');
        const time = checked('waktu_booking');

        $('sumService').textContent = service ? service.dataset.name : '—';
        $('sumDuration').textContent = service ? service.dataset.duration : '—';
        $('sumPrice').textContent = service ? service.dataset.price : 'Rp 0';
        $('sumTherapist').textContent = therapist ? therapist.dataset.name : 'Dipilihkan klinik';
        $('sumDate').textContent = formatDate(dateInput.value);
        $('sumTime').textContent = time ? time.value + ' WIB' : '—';

        const done = {
            service: !!service,
            therapist: !!service,
            schedule: !!(dateInput.value && time),
            patient: form.nama.value.trim() && form.email.value.trim() && /^08[0-9]{8,11}$/.test(form.no_hp.value.trim()),
        };
        form.querySelectorAll('.stepper .st').forEach((st) => st.classList.toggle('done', !!done[st.dataset.step]));
    }

    function renderSlots(slots) {
        slotGrid.innerHTML = '';
        if (!slots.length) {
            slotGrid.innerHTML = '<div class="slot-empty" style="grid-column:1/-1">Tidak ada slot di tanggal ini.</div>';
            return;
        }
        let anyAvailable = false;
        slots.forEach((s) => {
            const label = document.createElement('label');
            label.className = 'slot' + (s.available ? '' : ' disabled');
            label.title = s.available ? `Sisa ${s.remaining} tempat` : s.reason;

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'waktu_booking';
            input.value = s.time;
            input.disabled = !s.available;
            input.checked = s.available && s.time === selectedTime;

            const span = document.createElement('span');
            span.innerHTML = `${s.time}<small>${s.available ? 'Sisa ' + s.remaining : s.reason}</small>`;

            label.append(input, span);
            slotGrid.appendChild(label);
            if (s.available) anyAvailable = true;
        });
        if (!anyAvailable) {
            const note = document.createElement('div');
            note.className = 'slot-empty';
            note.style.gridColumn = '1/-1';
            note.textContent = 'Semua slot di tanggal ini sudah tidak tersedia. Coba tanggal lain.';
            slotGrid.appendChild(note);
        }
    }

    async function loadSlots() {
        if (!dateInput.value) return;
        const id = ++request;
        slotGrid.innerHTML = '<div class="slot-empty" style="grid-column:1/-1"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat jadwal...</div>';

        const therapist = checked('therapist_id');
        const params = new URLSearchParams({ tanggal: dateInput.value });
        if (therapist && therapist.value) params.set('therapist_id', therapist.value);

        try {
            const res = await fetch(`${form.dataset.slotsUrl}?${params}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (id === request) renderSlots(data.slots);
        } catch (e) {
            if (id === request) {
                slotGrid.innerHTML = '<div class="slot-empty" style="grid-column:1/-1">Gagal memuat jadwal. Periksa tanggal lalu coba lagi.</div>';
            }
        }
        updateSummary();
    }

    form.addEventListener('change', (e) => {
        if (e.target.name === 'waktu_booking') selectedTime = e.target.value;
        if (e.target === dateInput || e.target.name === 'therapist_id') loadSlots();
        updateSummary();
    });
    form.addEventListener('input', updateSummary);

    form.addEventListener('submit', (e) => {
        const errors = [];
        if (!checked('service_id')) errors.push('Silakan pilih layanan.');
        if (!dateInput.value) errors.push('Silakan pilih tanggal.');
        if (!checked('waktu_booking')) errors.push('Silakan pilih jam yang tersedia.');
        if (!form.nama.value.trim()) errors.push('Nama pasien wajib diisi.');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.value.trim())) errors.push('Format email tidak valid.');
        if (!/^08[0-9]{8,11}$/.test(form.no_hp.value.trim())) errors.push('Format nomor HP tidak valid. Gunakan format 08xxxxxxxxxx.');
        if (errors.length) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });

    loadSlots();
    updateSummary();
})();
