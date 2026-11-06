@extends('layouts.app')

@section('title', 'Pasien '.$booking->nama)

@php($chosen = old('obat', $booking->medicines->pluck('id')->all()))
@php($aturanLama = $booking->medicines->mapWithKeys(fn ($m) => [$m->id => $m->pivot->aturan_pakai])->all())
@php($jumlahLama = $booking->medicines->mapWithKeys(fn ($m) => [$m->id => $m->pivot->jumlah])->all())
@php($hargaLama = $booking->medicines->mapWithKeys(fn ($m) => [$m->id => $m->pivot->harga])->all())

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('panel.dashboard').'">Panel Terapis</a> / <a href="'.route('panel.schedule').'">Jadwal</a> / '.e($booking->kode_booking),
    'title' => $booking->nama,
    'subtitle' => $booking->service->name.' · '.$booking->tanggal_booking->translatedFormat('l, d F Y').' · '.$booking->jam().' WIB',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card-soft mb-4" data-reveal="left">
                    <h5 class="mb-3"><i class="fas fa-id-card text-primary mr-2"></i>Data Pasien</h5>
                    <div class="summary-row"><span>Kode</span><span>{{ $booking->kode_booking }}</span></div>
                    <div class="summary-row"><span>No. HP</span><span><a href="tel:{{ $booking->no_hp }}">{{ $booking->no_hp }}</a></span></div>
                    <div class="summary-row"><span>Email</span><span class="text-break">{{ $booking->email }}</span></div>
                    <div class="summary-row"><span>Status</span><span><span class="badge badge-{{ $booking->badgeClass() }}">{{ $booking->status }}</span></span></div>
                    <div class="summary-row"><span>Pembayaran</span><span><span class="badge badge-{{ $booking->paymentBadge() }}">{{ $booking->status_pembayaran }}</span></span></div>
                    <div class="mt-3">
                        <div class="small text-muted font-weight-bold text-uppercase mb-1">Keluhan dari pasien</div>
                        <div>{{ $booking->catatan ?: 'Tidak ada catatan.' }}</div>
                    </div>
                </div>

                <div class="card-soft mb-4" data-reveal="left">
                    <h5 class="mb-3"><i class="fas fa-receipt text-primary mr-2"></i>Tagihan Pasien</h5>
                    @include('partials.billing', ['booking' => $booking])
                    <p class="small text-muted mt-2 mb-0">Obat yang Anda anjurkan otomatis masuk ke tagihan ini dan bisa dibayar pasien secara online.</p>
                </div>

                <div class="card-soft" data-reveal="left">
                    <h5 class="mb-3"><i class="fas fa-clock-rotate-left text-primary mr-2"></i>Riwayat Sesi</h5>
                    @forelse ($history as $h)
                        <div class="history-item">
                            <div class="d-flex justify-content-between">
                                <b class="small">{{ $h->tanggal_booking->translatedFormat('d M Y') }}</b>
                                <span class="badge badge-{{ $h->badgeClass() }}">{{ $h->status }}</span>
                            </div>
                            <div class="small text-muted">{{ $h->service->name }} · {{ $h->therapist?->name ?? '—' }}</div>
                            @if ($h->catatan_terapis)<div class="small mt-1">{{ \Illuminate\Support\Str::limit($h->catatan_terapis, 140) }}</div>@endif
                            @if ($h->medicines->isNotEmpty())<div class="small text-primary mt-1"><i class="fas fa-pills mr-1"></i>{{ $h->medicines->pluck('name')->join(', ') }}</div>@endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Ini sesi pertama pasien.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <form method="POST" action="{{ route('panel.patient.update', $booking) }}" class="card-soft pad-lg" data-reveal>
                    @csrf @method('PUT')
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="form-block">
                        <div class="form-block-title"><span class="icon-chip"><i class="fas fa-notes-medical"></i></span><h5>Catatan terapi</h5></div>
                        <textarea name="catatan_terapis" rows="6" class="form-control" maxlength="3000"
                                  placeholder="Hasil pemeriksaan, tindakan hari ini, dan program latihan di rumah. Catatan ini bisa dibaca pasien di tiketnya.">{{ old('catatan_terapis', $booking->catatan_terapis) }}</textarea>
                    </div>

                    <div class="form-block">
                        <div class="form-block-title"><span class="icon-chip"><i class="fas fa-pills"></i></span><h5>Anjuran obat / suplemen</h5></div>
                        <p class="small text-muted">
                            Obat yang ditandai <span class="badge badge-soft">Sesuai layanan</span> umum dipakai untuk {{ $booking->service->name }}.
                            Obat keras tidak tersedia di sini; bila perlu, arahkan pasien ke dokter untuk resep.
                        </p>
                        <div class="rx-list">
                            @foreach ($medicines as $m)
                                @php($on = in_array($m->id, $chosen))
                                @php($match = (bool) array_intersect($suggested, $m->kebutuhan ?? []))
                                @php($hargaPakai = $hargaLama[$m->id] ?? $m->harga)
                                <div class="rx-item {{ $on ? 'on' : '' }}" data-price="{{ $hargaPakai }}">
                                    <label class="d-flex align-items-start mb-0">
                                        <input type="checkbox" name="obat[]" value="{{ $m->id }}" class="mt-1 mr-3" data-rx @checked($on)>
                                        @if ($m->imageUrl())
                                            <img src="{{ $m->imageUrl() }}" alt="" class="med-thumb mr-3" loading="lazy">
                                        @endif
                                        <span class="flex-fill">
                                            <span class="cell-strong">{{ $m->name }}</span>
                                            @if ($match)<span class="badge badge-soft ml-1">Sesuai layanan</span>@endif
                                            <span class="badge badge-{{ $m->golonganInfo()['warna'] }} ml-1">{{ $m->golonganInfo()['label'] }}</span>
                                            <span class="d-block small font-weight-bold text-primary">{{ \App\Models\Booking::rupiah($hargaPakai) }} / {{ $m->satuan }}</span>
                                            <span class="d-block small text-muted">{{ $m->kategori }} · {{ \Illuminate\Support\Str::limit($m->kegunaan, 90) }}</span>
                                            @if ($m->hindari_jika)
                                                <span class="d-block small text-danger"><i class="fas fa-triangle-exclamation mr-1"></i>Hindari jika: {{ collect($m->hindari_jika)->map(fn ($k) => strtolower(config('obat.kondisi.'.$k)))->join(', ') }}</span>
                                            @endif
                                        </span>
                                    </label>
                                    <div class="rx-rule mt-2">
                                        <div class="d-flex">
                                            <div class="rx-qty mr-2">
                                                <label class="small text-muted mb-0" for="jumlah{{ $m->id }}">Jumlah</label>
                                                <input type="number" id="jumlah{{ $m->id }}" name="jumlah[{{ $m->id }}]" class="form-control form-control-sm" min="1" max="20"
                                                       value="{{ old('jumlah.'.$m->id, $jumlahLama[$m->id] ?? 1) }}" data-qty>
                                            </div>
                                            <div class="flex-fill">
                                                <label class="small text-muted mb-0" for="aturan{{ $m->id }}">Aturan pakai</label>
                                                <input type="text" id="aturan{{ $m->id }}" name="aturan[{{ $m->id }}]" class="form-control form-control-sm"
                                                       value="{{ old('aturan.'.$m->id, $aturanLama[$m->id] ?? '') }}" maxlength="255"
                                                       placeholder="mis. {{ \Illuminate\Support\Str::limit($m->dosis, 60) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="rx-total mt-3" data-base="{{ $booking->biayaLayanan() }}" data-paid="{{ $booking->totalDibayar() }}">
                            <div class="d-flex justify-content-between"><span>Sesi {{ $booking->service->name }}</span><b>{{ \App\Models\Booking::rupiah($booking->biayaLayanan()) }}</b></div>
                            <div class="d-flex justify-content-between"><span>Obat dianjurkan</span><b id="rxMed">Rp 0</b></div>
                            <div class="d-flex justify-content-between total"><span>Total tagihan pasien</span><b id="rxTotal">{{ $booking->formattedTotal() }}</b></div>
                            @if ($booking->totalDibayar() > 0)
                                <div class="d-flex justify-content-between small text-muted"><span>Sudah dibayar pasien</span><span>{{ \App\Models\Booking::rupiah($booking->totalDibayar()) }}</span></div>
                                <div class="d-flex justify-content-between small"><span>Pasien perlu membayar lagi</span><b id="rxDue">Rp 0</b></div>
                            @endif
                        </div>
                    </div>

                    <div class="form-block mb-3">
                        <div class="form-block-title"><span class="icon-chip"><i class="fas fa-flag-checkered"></i></span><h5>Status sesi</h5></div>
                        <div class="choice-grid">
                            @foreach (['' => ['Tidak diubah', 'Tetap '.$booking->status], 'Dikonfirmasi' => ['Dikonfirmasi', 'Pasien akan datang'], 'Selesai' => ['Sesi selesai', 'Pasien bisa memberi ulasan']] as $val => [$t, $s])
                                <label class="choice">
                                    <input type="radio" name="status" value="{{ $val }}" @checked(old('status', '') === $val)>
                                    <span class="choice-box"><span><span class="t d-block">{{ $t }}</span><span class="s">{{ $s }}</span></span></span>
                                    <span class="choice-check"><i class="fas fa-check"></i></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('panel.schedule') }}" class="btn btn-light-soft">Kembali</a>
                        <button type="submit" class="btn btn-primary" @disabled($booking->status === 'Dibatalkan')><i class="fas fa-floppy-disk mr-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const fmt = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };
            const box = document.querySelector('.rx-total');
            const update = function () {
                let med = 0;
                document.querySelectorAll('.rx-item').forEach(function (item) {
                    if (!item.querySelector('[data-rx]').checked) return;
                    const qty = Math.max(1, parseInt(item.querySelector('[data-qty]').value, 10) || 1);
                    med += qty * parseInt(item.dataset.price, 10);
                });
                const total = parseInt(box.dataset.base, 10) + med;
                document.getElementById('rxMed').textContent = fmt(med);
                document.getElementById('rxTotal').textContent = fmt(total);
                const due = document.getElementById('rxDue');
                if (due) due.textContent = fmt(Math.max(0, total - parseInt(box.dataset.paid, 10)));
            };
            document.querySelectorAll('[data-rx]').forEach(function (cb) {
                cb.addEventListener('change', function () { cb.closest('.rx-item').classList.toggle('on', cb.checked); update(); });
            });
            document.querySelectorAll('[data-qty]').forEach(function (q) { q.addEventListener('input', update); });
            update();
        })();
    </script>
@endpush
