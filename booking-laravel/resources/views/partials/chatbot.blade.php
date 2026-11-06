@php
    $chatbotData = [
        'klinik' => [
            'nama' => config('klinik.nama'),
            'telepon' => config('klinik.telepon'),
            'email' => config('klinik.email'),
            'alamat' => config('klinik.alamat'),
            'jam' => config('klinik.jam_operasional_label'),
            'biayaHomeVisit' => config('klinik.biaya_home_visit'),
            'jarakHomeVisit' => config('klinik.jarak_home_visit_km'),
        ],
        'layanan' => \App\Models\Service::active()->orderBy('id')->get()->map(fn ($s) => [
            'nama' => $s->name,
            'harga' => $s->formattedPrice(),
            'durasi' => $s->duration,
            'deskripsi' => $s->description,
            'fitur' => $s->features,
        ])->values(),
        'bookingUrl' => route('booking.create'),
    ];
@endphp

<button type="button" class="chatbot-toggle" id="chatbotToggle" aria-label="Buka asisten chat">
    <i class="fas fa-comment-medical"></i>
</button>

<div class="chatbot-modal" id="chatbotModal" role="dialog" aria-label="Asisten Fisioterapi">
    <div class="chatbot-header">
        <div class="d-flex align-items-center">
            <span class="icon-chip sm mr-2" style="background:rgba(255,255,255,.2);color:#fff"><i class="fas fa-user-nurse"></i></span>
            <div>
                <h6>Asisten Fisioterapi</h6>
                <small>Biasanya membalas seketika</small>
            </div>
        </div>
        <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Tutup chat">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages" aria-live="polite"></div>
    <div class="chatbot-input">
        <form id="chatbotForm">
            <input type="text" id="chatbotInput" placeholder="Ketik pertanyaan Anda..." autocomplete="off" aria-label="Pertanyaan">
            <button type="submit" aria-label="Kirim"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<script>window.CHATBOT_DATA = @json($chatbotData);</script>
