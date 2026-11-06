@extends('layouts.app')

@section('title', 'Panel Terapis')

@section('content')
@include('partials.page-hero', [
    'eyebrow' => now()->translatedFormat('l, d F Y'),
    'eyebrowIcon' => 'fa-calendar-day',
    'title' => 'Halo, '.$therapist->name.' 👋',
    'subtitle' => 'Jadwal pasien Anda hari ini, catatan terapi, dan anjuran obat dalam satu tempat.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            @foreach ([
                ['hari_ini', 'Pasien hari ini', 'fa-user-clock', 'info'],
                ['minggu_ini', 'Sesi minggu ini', 'fa-calendar-week', 'warning'],
                ['selesai', 'Sesi selesai', 'fa-circle-check', 'success'],
                ['pasien', 'Total pasien', 'fa-users', 'secondary'],
            ] as $i => [$key, $label, $icon, $tone])
                <div class="col-6 col-lg-3 mb-4" data-reveal style="--d: {{ $i * 0.08 }}s">
                    <div class="stat-tile" data-tilt="8">
                        <span class="icon-chip tone-{{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                        <div><div class="val" data-count="{{ $stats[$key] }}">0</div><div class="lbl">{{ $label }}</div></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4" data-reveal="left">
                <div class="card-soft h-100">
                    <div class="card-head">
                        <h4><i class="fas fa-user-clock text-primary mr-2"></i>Jadwal Hari Ini</h4>
                        <a href="{{ route('panel.schedule') }}" class="btn btn-light-soft btn-sm">Semua jadwal</a>
                    </div>
                    @forelse ($today as $b)
                        <a href="{{ route('panel.patient', $b) }}" class="appt text-reset text-decoration-none">
                            <div class="appt-date"><div class="d" style="font-size:1.05rem">{{ $b->jam() }}</div><div class="m">WIB</div></div>
                            <div class="appt-info">
                                <h6>{{ $b->nama }}</h6>
                                <small>{{ $b->service->name }} · {{ $b->kode_booking }}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $b->badgeClass() }}">{{ $b->status }}</span>
                                <div><span class="badge badge-{{ $b->paymentBadge() }} mt-1">{{ $b->status_pembayaran }}</span></div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-mug-hot fa-2x mb-2 d-block"></i>Tidak ada pasien hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-5 mb-4" data-reveal="right">
                <div class="card-soft h-100">
                    <div class="card-head"><h4><i class="fas fa-calendar-days text-primary mr-2"></i>Berikutnya</h4></div>
                    @forelse ($upcoming as $b)
                        <a href="{{ route('panel.patient', $b) }}" class="appt text-reset text-decoration-none">
                            <div class="appt-date"><div class="d">{{ $b->tanggal_booking->format('d') }}</div><div class="m">{{ $b->tanggal_booking->translatedFormat('M') }}</div></div>
                            <div class="appt-info"><h6>{{ $b->nama }}</h6><small>{{ $b->jam() }} WIB · {{ $b->service->name }}</small></div>
                        </a>
                    @empty
                        <p class="text-muted mb-0">Belum ada jadwal berikutnya.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card-soft" data-reveal>
            <div class="card-head">
                <h4><i class="fas fa-star text-warning mr-2"></i>Ulasan Pasien</h4>
                <span class="badge badge-warning" style="font-size:.95rem">★ {{ $rating ?? '—' }}</span>
            </div>
            <div class="row">
                @forelse ($reviews as $r)
                    <div class="col-md-6 mb-3">
                        <div class="review-line h-100">
                            <div class="stars">@for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $r->rating ? 'fas' : 'far' }} fa-star"></i>@endfor</div>
                            <small class="text-muted">{{ $r->nama }} · {{ $r->service->name }}</small>
                            <p class="mb-0 mt-1">{{ $r->ulasan ?: 'Tanpa komentar.' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">Belum ada ulasan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
