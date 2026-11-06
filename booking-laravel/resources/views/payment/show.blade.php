@extends('layouts.app')

@section('title', 'Pembayaran '.$booking->kode_booking)

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('booking.index').'">Riwayat</a> / <a href="'.route('booking.show', $booking).'">'.e($booking->kode_booking).'</a> / Pembayaran',
    'title' => 'Pembayaran Online',
    'subtitle' => 'Bayar sekarang lewat QRIS, e-wallet, virtual account, atau transfer bank. Booking langsung dikonfirmasi setelah lunas.',
])

<div class="page-body">
    <div class="container">
        @if ($isDemo)
            <div class="alert alert-info d-flex align-items-start" data-reveal>
                <i class="fas fa-flask mr-2 mt-1"></i>
                <div><strong>Mode demo.</strong> Belum terhubung ke payment gateway, jadi tidak ada uang yang ditarik. Nomor VA dan QR di bawah hanya contoh.
                    Hubungkan Midtrans di file <code>.env</code> untuk menerima pembayaran sungguhan.</div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7 mb-4">
                @if ($payment)
                    @php($info = $payment->methodInfo())
                    <div class="card-soft pad-lg" data-reveal>
                        <div class="card-head">
                            <div class="d-flex align-items-center">
                                <span class="icon-chip solid mr-3"><i class="fas {{ $info['icon'] }}"></i></span>
                                <div>
                                    <h4 class="mb-0">{{ $info['label'] }}</h4>
                                    <small class="text-muted">No. pesanan {{ $payment->order_id }}</small>
                                </div>
                            </div>
                            <span class="badge badge-{{ $payment->statusTone() }}">{{ $payment->statusLabel() }}</span>
                        </div>

                        @if ($payment->proof)
                            <div class="pay-wait">
                                <i class="fas fa-hourglass-half fa-2x text-info mb-3"></i>
                                <h5>Bukti transfer sedang diverifikasi</h5>
                                <p class="text-muted mb-3">Admin akan memeriksa bukti Anda maksimal 1×24 jam. Status booking berubah otomatis setelah diverifikasi.</p>
                                <a href="{{ $payment->proofUrl() }}" target="_blank" rel="noopener" class="btn btn-light-soft btn-sm"><i class="fas fa-file-image mr-1"></i>Lihat bukti</a>
                            </div>
                        @else
                            <div class="pay-deadline mb-4">
                                <i class="far fa-clock"></i>
                                <span>Selesaikan pembayaran sebelum <strong>{{ $payment->expires_at->translatedFormat('d M Y, H:i') }} WIB</strong></span>
                                <span class="countdown" data-deadline="{{ $payment->expires_at->toIso8601String() }}"></span>
                            </div>

                            <div class="pay-amount mb-4">
                                <span>Total tagihan</span>
                                <strong>{{ $payment->formattedAmount() }}</strong>
                                <button type="button" class="btn btn-link btn-sm p-0" data-copy="{{ $payment->amount }}"><i class="far fa-copy mr-1"></i>Salin</button>
                            </div>

                            @if ($payment->snap_token)
                                {{-- Midtrans: halaman pembayaran resmi dibuka di popup Snap --}}
                                <p class="text-muted">Klik tombol di bawah untuk membuka halaman pembayaran aman dari Midtrans.</p>
                                <button type="button" class="btn btn-primary btn-lg btn-block" id="paySnap" data-token="{{ $payment->snap_token }}">
                                    <i class="fas fa-lock mr-2"></i>Bayar {{ $payment->formattedAmount() }}
                                </button>
                                @if ($payment->payload['redirect_url'] ?? null)
                                    <a href="{{ $payment->payload['redirect_url'] }}" class="d-block text-center small mt-2" rel="noopener">Popup tidak muncul? Buka halaman pembayaran</a>
                                @endif
                            @elseif ($info['tipe'] === 'va')
                                <div class="va-box mb-4">
                                    <div class="small text-muted font-weight-bold text-uppercase">Nomor Virtual Account {{ $info['bank'] }}</div>
                                    <div class="va-number" id="vaNumber">{{ trim(chunk_split($payment->va_number, 4, ' ')) }}</div>
                                    <button type="button" class="btn btn-primary btn-sm" data-copy="{{ $payment->va_number }}"><i class="far fa-copy mr-1"></i>Salin nomor VA</button>
                                </div>
                                <h6>Cara bayar</h6>
                                <ol class="pay-steps">
                                    <li>Buka m-banking / ATM {{ $info['bank'] }}, pilih <b>Transfer → Virtual Account</b>.</li>
                                    <li>Masukkan nomor VA di atas.</li>
                                    <li>Pastikan nama tagihan <b>{{ config('klinik.nama') }}</b> dan nominal <b>{{ $payment->formattedAmount() }}</b>.</li>
                                    <li>Konfirmasi pembayaran. Status di halaman ini berubah otomatis.</li>
                                </ol>
                            @elseif ($info['tipe'] === 'qr')
                                <div class="text-center mb-3">
                                    <div class="qr-box qr-pay" id="payQr" data-qr="{{ $payment->qr_string }}"></div>
                                    <div class="small text-muted mt-2">{{ $info['info'] ?? '' }}</div>
                                </div>
                                <ol class="pay-steps">
                                    <li>Buka aplikasi {{ $payment->method === 'qris' ? 'e-wallet atau m-banking' : $info['label'] }} Anda.</li>
                                    <li>Pilih <b>Scan / Bayar</b>, lalu pindai QR di atas.</li>
                                    <li>Periksa nominal <b>{{ $payment->formattedAmount() }}</b>, lalu konfirmasi.</li>
                                </ol>
                            @else
                                <h6>Transfer ke salah satu rekening klinik</h6>
                                @foreach (config('pembayaran.rekening') as $rek)
                                    <div class="bank-row">
                                        <span class="icon-chip sm"><i class="fas fa-building-columns"></i></span>
                                        <div class="flex-fill">
                                            <div class="cell-strong">{{ $rek['bank'] }} · {{ $rek['nomor'] }}</div>
                                            <small class="text-muted">a.n. {{ $rek['atas_nama'] }}</small>
                                        </div>
                                        <button type="button" class="btn btn-light-soft btn-sm" data-copy="{{ preg_replace('/\D/', '', $rek['nomor']) }}"><i class="far fa-copy"></i></button>
                                    </div>
                                @endforeach
                                <p class="small text-muted mt-3">Transfer tepat <b>{{ $payment->formattedAmount() }}</b>, lalu unggah bukti transfer (foto/screenshot atau PDF, maks 3MB).</p>
                                <form method="POST" action="{{ route('payment.proof', [$booking, $payment]) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="custom-file mb-3">
                                        <input type="file" class="custom-file-input @error('bukti') is-invalid @enderror" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp,application/pdf" required>
                                        <label class="custom-file-label" for="bukti">Pilih file bukti transfer…</label>
                                        @error('bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-upload mr-1"></i>Kirim Bukti Transfer</button>
                                </form>
                            @endif

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top">
                                <form method="POST" action="{{ route('payment.cancel', [$booking, $payment]) }}" class="mb-2">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-light-soft btn-sm"><i class="fas fa-arrows-rotate mr-1"></i>Ganti metode</button>
                                </form>
                                @if ($info['tipe'] !== 'manual')
                                    <div class="mb-2 small text-muted" id="payPolling"><i class="fas fa-circle-notch fa-spin mr-1"></i>Menunggu pembayaran…</div>
                                @endif
                            </div>

                            @if ($isDemo && $info['tipe'] !== 'manual')
                                <form method="POST" action="{{ route('payment.simulate', [$booking, $payment]) }}" class="demo-box mt-3">
                                    @csrf
                                    <div class="small mb-2"><i class="fas fa-flask mr-1"></i>Mode demo: anggap pasien sudah membayar.</div>
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check mr-1"></i>Simulasikan pembayaran berhasil</button>
                                </form>
                            @endif
                        @endif
                    </div>
                @else
                    <form method="POST" action="{{ route('payment.store', $booking) }}" class="card-soft pad-lg" data-reveal>
                        @csrf
                        <h4 class="mb-1">Pilih metode pembayaran</h4>
                        <p class="text-muted">Semua metode tanpa biaya tambahan.</p>
                        @error('method')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                        @foreach ($methods as $group => $items)
                            <div class="form-block-title mt-4"><h5>{{ $group }}</h5></div>
                            <div class="choice-grid pay-grid">
                                @foreach ($items as $key => $m)
                                    <label class="choice">
                                        <input type="radio" name="method" value="{{ $key }}" @checked(old('method') === $key) required>
                                        <span class="choice-box">
                                            <span class="icon-chip sm"><i class="fas {{ $m['icon'] }}"></i></span>
                                            <span><span class="t d-block">{{ $m['label'] }}</span>
                                                <span class="s">{{ $m['info'] ?? 'Dicek otomatis' }}</span></span>
                                        </span>
                                        <span class="choice-check"><i class="fas fa-check"></i></span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4"><i class="fas fa-lock mr-2"></i>Lanjut Bayar {{ \App\Models\Booking::rupiah($booking->sisaTagihan()) }}</button>
                    </form>
                @endif
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card-soft summary" data-reveal="right">
                    <h5 class="mb-3"><i class="fas fa-receipt text-primary mr-2"></i>Ringkasan Tagihan</h5>
                    <div class="summary-row"><span>Kode booking</span><span>{{ $booking->kode_booking }}</span></div>
                    <div class="summary-row"><span>Jadwal</span><span>{{ $booking->tanggal_booking->translatedFormat('d M Y') }}, {{ $booking->jam() }}</span></div>
                    <div class="summary-row"><span>Fisioterapis</span><span>{{ $booking->therapist?->name ?? 'Ditentukan klinik' }}</span></div>
                    <div class="mt-3">@include('partials.billing', ['booking' => $booking])</div>
                    <div class="summary-total"><span class="font-weight-bold">Dibayar sekarang</span><span class="price text-primary fw-800">{{ \App\Models\Booking::rupiah($booking->sisaTagihan()) }}</span></div>
                    <div class="secure-note mt-3"><i class="fas fa-shield-halved"></i> Pembayaran terenkripsi. Kami tidak pernah meminta PIN atau OTP Anda.</div>
                    <a href="{{ route('booking.show', $booking) }}" class="btn btn-light-soft btn-block mt-3">Bayar nanti di klinik</a>
                </div>

                @if ($history->where('status', '!=', 'pending')->isNotEmpty())
                    <div class="card-soft mt-4" data-reveal="right">
                        <h6 class="mb-3">Riwayat percobaan bayar</h6>
                        @foreach ($history->where('status', '!=', 'pending') as $h)
                            <div class="d-flex justify-content-between small py-1">
                                <span>{{ $h->methodLabel() }} · {{ $h->created_at->translatedFormat('d M H:i') }}</span>
                                <span class="badge badge-{{ $h->statusTone() }}">{{ $h->statusLabel() }}</span>
                            </div>
                            @if ($h->admin_note)<div class="small text-danger mb-1">Catatan admin: {{ $h->admin_note }}</div>@endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if ($payment?->snap_token)
        <script src="{{ \App\Support\PaymentGateway::snapJs() }}" data-client-key="{{ config('pembayaran.midtrans.client_key') }}"></script>
    @endif
    @if ($payment && ($payment->methodInfo()['tipe'] ?? '') === 'qr' && ! $payment->snap_token)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endif
    <script>
        (function () {
            // Salin teks
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const text = btn.dataset.copy;
                    const done = function () { const old = btn.innerHTML; btn.innerHTML = '<i class="fas fa-check mr-1"></i>Tersalin'; setTimeout(function () { btn.innerHTML = old; }, 1600); };
                    if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done, done); } else { done(); }
                });
            });

            // Nama file bukti
            const file = document.getElementById('bukti');
            if (file) file.addEventListener('change', function () { file.nextElementSibling.textContent = file.files[0] ? file.files[0].name : 'Pilih file…'; });

            // QR
            const qr = document.getElementById('payQr');
            if (qr && window.QRCode) new QRCode(qr, { text: qr.dataset.qr, width: 210, height: 210, colorDark: '#0f172a', colorLight: '#ffffff' });

            // Hitung mundur batas bayar
            const cd = document.querySelector('.countdown');
            if (cd) {
                const end = new Date(cd.dataset.deadline).getTime();
                const tick = function () {
                    const s = Math.max(0, Math.floor((end - Date.now()) / 1000));
                    const h = String(Math.floor(s / 3600)).padStart(2, '0'), m = String(Math.floor(s % 3600 / 60)).padStart(2, '0'), d = String(s % 60).padStart(2, '0');
                    cd.textContent = h + ':' + m + ':' + d;
                    if (s === 0) location.reload(); else setTimeout(tick, 1000);
                };
                tick();
            }

            // Cek status otomatis
            const polling = document.getElementById('payPolling');
            if (polling) {
                const check = function () {
                    fetch(@json(route('payment.status', $booking)), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) { if (d.lunas) { location.href = d.redirect; } else { setTimeout(check, 6000); } })
                        .catch(function () { setTimeout(check, 10000); });
                };
                setTimeout(check, 6000);
            }

            // Midtrans Snap
            const snapBtn = document.getElementById('paySnap');
            if (snapBtn) {
                snapBtn.addEventListener('click', function () {
                    if (!window.snap) { alert('Halaman pembayaran belum termuat. Periksa koneksi internet lalu coba lagi.'); return; }
                    window.snap.pay(snapBtn.dataset.token, {
                        onSuccess: function () { location.reload(); },
                        onPending: function () { location.reload(); },
                        onError: function () { location.reload(); },
                    });
                });
            }
        })();
    </script>
@endpush
