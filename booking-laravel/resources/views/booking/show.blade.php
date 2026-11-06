@extends('layouts.app')

@section('title', 'Tiket '.$booking->kode_booking)

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('booking.index').'">Riwayat</a> / '.e($booking->kode_booking),
    'title' => 'Tiket Booking',
    'subtitle' => 'Tunjukkan tiket ini (atau QR code-nya) kepada petugas saat tiba di klinik.',
])

<div class="page-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ticket" data-tilt="4" data-reveal="zoom">
                    <div class="ticket-head">
                        <span class="orb" style="width:160px;height:160px;right:-40px;top:-60px;opacity:.3"></span>
                        <div class="d-flex justify-content-between align-items-start flex-wrap position-relative">
                            <div class="mb-3">
                                <small style="opacity:.8;font-weight:700;letter-spacing:.1em">KODE BOOKING</small>
                                <div class="ticket-code">{{ $booking->kode_booking }}</div>
                                <h2>{{ $booking->service->name }}</h2>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $booking->badgeClass() }}" style="font-size:.9rem"><i class="fas {{ $booking->statusIcon() }} mr-1"></i>{{ $booking->status }}</span>
                                <div class="mt-2"><span class="badge badge-light" style="font-size:.8rem"><i class="fas fa-wallet mr-1"></i>{{ $booking->status_pembayaran }}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-cut"></div>
                    <div class="ticket-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="ticket-grid">
                                    <div><div class="k">Pasien</div><div class="v">{{ $booking->nama }}</div></div>
                                    <div><div class="k">No. HP</div><div class="v">{{ $booking->no_hp }}</div></div>
                                    <div><div class="k">Tanggal</div><div class="v">{{ $booking->tanggal_booking->translatedFormat('l, d F Y') }}</div></div>
                                    <div><div class="k">Jam</div><div class="v">{{ $booking->jam() }} WIB</div></div>
                                    <div><div class="k">Fisioterapis</div><div class="v">{{ $booking->therapist?->name ?? 'Ditentukan klinik' }}</div></div>
                                    <div><div class="k">Biaya</div><div class="v">{{ $booking->formattedTotal() }}</div></div>
                                    <div><div class="k">Pembayaran</div><div class="v">
                                        <span class="badge badge-{{ $booking->paymentBadge() }}">{{ $booking->status_pembayaran }}</span>
                                        @if ($booking->isPaid() && $booking->latestPayment)
                                            <small class="d-block text-muted mt-1">{{ $booking->latestPayment->methodLabel() }} · {{ $booking->latestPayment->paid_at?->translatedFormat('d M Y H:i') }}</small>
                                        @endif
                                    </div></div>
                                </div>
                                @if ($booking->catatan)
                                    <div class="mt-3"><div class="k text-muted small font-weight-bold text-uppercase">Catatan</div><div>{{ $booking->catatan }}</div></div>
                                @endif
                            </div>
                            <div class="col-md-4 text-center mt-4 mt-md-0">
                                <div class="qr-box" id="qrcode" data-code="{{ $booking->kode_booking }}" aria-label="QR code {{ $booking->kode_booking }}"></div>
                                <div class="small text-muted mt-2">Pindai di meja pendaftaran</div>
                            </div>
                        </div>
                    </div>
                    <div class="bk-foot no-print justify-content-between">
                        <a href="{{ route('booking.index') }}" class="btn btn-light-soft btn-sm"><i class="fas fa-arrow-left mr-1"></i>Riwayat</a>
                        <div>
                            @if ($booking->canBeCancelled())
                                <form method="POST" action="{{ route('booking.cancel', $booking) }}" class="d-inline" data-confirm="Batalkan booking ini?">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                                </form>
                            @endif
                            @if ($booking->canBePaid())
                                <a href="{{ route('payment.show', $booking) }}" class="btn btn-success btn-sm"><i class="fas fa-credit-card mr-1"></i>{{ $booking->status_pembayaran === 'Menunggu Verifikasi' ? 'Status Pembayaran' : 'Bayar Online' }}</a>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print mr-1"></i>Cetak / Simpan PDF</button>
                        </div>
                    </div>
                </div>

                @if ($booking->status !== 'Dibatalkan')
                    @php($sisa = $booking->sisaTagihan())
                    @php($adaTambahanObat = $booking->medicines->isNotEmpty() && $booking->totalDibayar() > 0 && $sisa > 0)
                    <div class="card-soft pad-lg mt-4" data-reveal style="{{ $booking->canBePaid() && $booking->status_pembayaran === 'Belum Bayar' ? 'border-left:5px solid var(--green)' : '' }}">
                        <div class="card-head">
                            <h4><i class="fas fa-receipt text-primary mr-2"></i>Rincian Tagihan</h4>
                            <span class="badge badge-{{ $booking->paymentBadge() }}">{{ $booking->status_pembayaran }}</span>
                        </div>
                        @if ($adaTambahanObat)
                            <div class="alert alert-warning py-2 small"><i class="fas fa-pills mr-1"></i>Fisioterapis menambahkan obat ke tagihan Anda. Silakan bayar sisa tagihannya.</div>
                        @elseif ($booking->medicines->isNotEmpty())
                            <p class="small text-muted">Obat yang dianjurkan fisioterapis sudah termasuk dalam tagihan ini dan dapat diambil di klinik.</p>
                        @endif
                        @include('partials.billing', ['booking' => $booking])
                        @if ($booking->canBePaid() && $booking->status_pembayaran === 'Belum Bayar' && $sisa > 0)
                            <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 no-print">
                                <small class="text-muted mb-2">QRIS, GoPay, ShopeePay, Virtual Account BCA/BNI/BRI/Mandiri, atau transfer bank.</small>
                                <a href="{{ route('payment.show', $booking) }}" class="btn btn-success mb-2"><i class="fas fa-credit-card mr-1"></i>Bayar {{ \App\Models\Booking::rupiah($sisa) }}</a>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($booking->catatan_terapis || $booking->medicines->isNotEmpty())
                    <div class="card-soft pad-lg mt-4" data-reveal>
                        <h4 class="mb-3"><i class="fas fa-notes-medical text-primary mr-2"></i>Catatan & Anjuran Fisioterapis</h4>
                        @if ($booking->catatan_terapis)
                            <div class="therapist-note mb-3">{!! nl2br(e($booking->catatan_terapis)) !!}
                                <div class="small text-muted mt-2">— {{ $booking->therapist?->name }}</div>
                            </div>
                        @endif
                        @if ($booking->medicines->isNotEmpty())
                            <h6 class="mt-3">Obat / suplemen yang dianjurkan</h6>
                            @foreach ($booking->medicines as $med)
                                @include('partials.medicine-row', ['medicine' => $med, 'aturan' => $med->pivot->aturan_pakai])
                            @endforeach
                            <p class="small text-muted mt-3 mb-0"><i class="fas fa-circle-info mr-1"></i>Ikuti aturan pakai di atas atau di kemasan. Hentikan dan hubungi dokter bila muncul reaksi alergi atau keluhan baru.</p>
                        @endif
                    </div>
                @elseif ($needs)
                    <div class="card-soft mt-4 d-flex flex-wrap align-items-center justify-content-between no-print" data-reveal>
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <span class="icon-chip mr-3"><i class="fas fa-pills"></i></span>
                            <div>
                                <h5 class="mb-1">Obat yang umum dianjurkan untuk {{ $booking->service->name }}</h5>
                                <p class="text-muted mb-0">Fisioterapis akan memberi anjuran khusus setelah sesi Anda.</p>
                            </div>
                        </div>
                        <a href="{{ route('medicines.index', ['layanan' => $booking->service->slug]) }}" class="btn btn-light-soft">Lihat anjuran</a>
                    </div>
                @endif

                @if ($booking->status === 'Selesai')
                    <div class="card-soft pad-lg mt-4 no-print" id="ulasan" data-reveal>
                        @if ($booking->canBeRated())
                            <h4 class="mb-1">Beri ulasan sesi Anda</h4>
                            <p class="text-muted">Ulasan Anda membantu pasien lain dan tim kami.</p>
                            <form method="POST" action="{{ route('booking.review', $booking) }}">
                                @csrf
                                <div class="rate mb-3" role="radiogroup" aria-label="Rating">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" @checked(old('rating') == $i) required>
                                        <label for="star{{ $i }}" title="{{ $i }} bintang"><i class="fas fa-star"></i></label>
                                    @endfor
                                </div>
                                @error('rating')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                <textarea name="ulasan" class="form-control mb-3" rows="3" maxlength="500" placeholder="Ceritakan pengalaman Anda (opsional)">{{ old('ulasan') }}</textarea>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i>Kirim Ulasan</button>
                            </form>
                        @else
                            <h5 class="mb-2">Ulasan Anda</h5>
                            <div class="stars mb-2">@for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $booking->rating ? 'fas' : 'far' }} fa-star"></i>@endfor</div>
                            <p class="mb-0 text-muted">{{ $booking->ulasan ?: 'Tanpa komentar.' }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            const box = document.getElementById('qrcode');
            if (box && window.QRCode) {
                new QRCode(box, { text: box.dataset.code, width: 132, height: 132, colorDark: '#0f766e', colorLight: '#ffffff' });
            }
        })();
    </script>
@endpush
