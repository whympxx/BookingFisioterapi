@extends('layouts.app')

@section('title', 'Beranda')

@php($user = auth()->user())

@section('content')
@include('partials.page-hero', [
    'eyebrow' => now()->translatedFormat('l, d F Y'),
    'eyebrowIcon' => 'fa-calendar-day',
    'title' => 'Halo, '.$user->displayName().' 👋',
    'subtitle' => 'Semoga hari Anda sehat. Kelola jadwal fisioterapi Anda di sini.',
])

<div class="page-body">
    <div class="container">
        {{-- Statistik --}}
        <div class="row">
            @foreach ([
                ['Menunggu', 'warning', 'fa-hourglass-half'],
                ['Dikonfirmasi', 'info', 'fa-calendar-check'],
                ['Selesai', 'success', 'fa-circle-check'],
            ] as $i => [$label, $tone, $icon])
                <div class="col-md-4 mb-4" data-reveal style="--d: {{ $i * 0.1 }}s">
                    <a href="{{ route('booking.index', ['status' => $label]) }}" class="stat-tile" data-tilt="8">
                        <span class="icon-chip tone-{{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                        <div>
                            <div class="val" data-count="{{ $stats[$label] ?? 0 }}">0</div>
                            <div class="lbl">Booking {{ strtolower($label) }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            {{-- Jadwal mendatang --}}
            <div class="col-lg-7 mb-4" data-reveal="left">
                <div class="card-soft h-100">
                    <div class="card-head">
                        <h4><i class="fas fa-calendar-days text-primary mr-2"></i>Jadwal Mendatang</h4>
                        <a href="{{ route('booking.index') }}" class="btn btn-light-soft btn-sm">Lihat semua</a>
                    </div>
                    @forelse ($upcoming as $b)
                        <a href="{{ route('booking.show', $b) }}" class="appt text-reset text-decoration-none">
                            <div class="appt-date">
                                <div class="d">{{ $b->tanggal_booking->format('d') }}</div>
                                <div class="m">{{ $b->tanggal_booking->translatedFormat('M') }}</div>
                            </div>
                            <div class="appt-info">
                                <h6>{{ $b->service->name }}</h6>
                                <small><i class="far fa-clock mr-1"></i>{{ $b->jam() }} WIB
                                    @if ($b->therapist) · <i class="fas fa-user-nurse mr-1"></i>{{ $b->therapist->name }} @endif
                                </small>
                            </div>
                            <span class="badge badge-{{ $b->badgeClass() }}">{{ $b->status }}</span>
                        </a>
                    @empty
                        <div class="text-center py-4">
                            <div class="icon-chip mx-auto mb-3" style="width:72px;height:72px;font-size:1.8rem"><i class="fas fa-calendar-plus"></i></div>
                            <h5>Belum ada jadwal</h5>
                            <p class="text-muted">Mulai program pemulihan Anda hari ini.</p>
                            <a href="{{ route('booking.create') }}" class="btn btn-primary">Buat Booking</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Aksi cepat --}}
            <div class="col-lg-5 mb-4" data-reveal="right">
                <div class="cta h-100" style="padding:2rem">
                    <span class="orb sky" style="width:140px;height:140px;right:-40px;top:-40px;opacity:.35"></span>
                    <div class="position-relative">
                        <span class="eyebrow" style="background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.25)"><i class="fas fa-bolt"></i> Aksi Cepat</span>
                        <h3 class="text-white mt-3">Butuh sesi berikutnya?</h3>
                        <p>Pilih slot jam yang masih kosong dan dapatkan tiket booking dengan QR code.</p>
                        <a href="{{ route('booking.create') }}" class="btn btn-white mb-2 mr-2"><i class="fas fa-calendar-plus mr-2"></i>Booking Baru</a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-light mb-2" style="border-radius:14px;border-width:2px">Profil Saya</a>
                    </div>
                </div>
            </div>
        </div>

        @if ($toReview)
            <div class="card-soft mb-4 d-flex flex-wrap align-items-center justify-content-between" data-reveal style="border-left:5px solid var(--amber)">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <span class="icon-chip tone-warning mr-3"><i class="fas fa-star"></i></span>
                    <div>
                        <h5 class="mb-1">Bagaimana sesi {{ $toReview->service->name }} Anda?</h5>
                        <p class="text-muted mb-0">Beri rating agar kami terus meningkatkan layanan.</p>
                    </div>
                </div>
                <a href="{{ route('booking.show', $toReview) }}#ulasan" class="btn btn-primary">Beri Ulasan</a>
            </div>
        @endif

        {{-- Layanan unggulan --}}
        <div class="d-flex justify-content-between align-items-end mt-4 mb-4" data-reveal>
            <div>
                <span class="eyebrow"><i class="fas fa-star"></i> Unggulan</span>
                <h2 class="section-title mb-0" style="font-size:1.8rem">Layanan favorit pasien</h2>
            </div>
            <a href="{{ route('layanan') }}" class="btn btn-light-soft d-none d-md-inline-block">Semua layanan <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        <div class="row">
            @foreach ($services as $service)
                <div class="col-lg-4 col-md-6 mb-4" data-reveal style="--d: {{ $loop->index * 0.1 }}s">@include('partials.service-card')</div>
            @endforeach
        </div>
    </div>
</div>
@endsection
