@extends('layouts.app')

@section('title', 'Fisioterapi Profesional & Booking Online')

@php
    $authUser = auth()->user();
    $bookUrl = $authUser && ! $authUser->isAdmin() ? route('booking.create') : route($authUser ? 'admin.dashboard' : 'register');
@endphp

@section('content')
{{-- ============ HERO ============ --}}
<section class="hero">
    <div class="hero-grid-bg"></div>
    <span class="orb" style="width:120px;height:120px;left:-40px;top:120px"></span>
    <span class="orb sky" style="width:60px;height:60px;left:42%;bottom:40px;animation-delay:-6s"></span>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6" data-reveal="left">
                <span class="eyebrow"><i class="fas fa-shield-heart"></i> Fisioterapis Bersertifikat</span>
                <h1>Bergerak lagi tanpa nyeri, <span class="text-gradient">pulih lebih cepat.</span></h1>
                <p class="lead">Program fisioterapi personal untuk cedera, nyeri punggung, stroke, hingga tumbuh kembang anak. Pilih layanan, terapis, dan jam yang pas — semua online dalam 1 menit.</p>
                <div class="hero-actions">
                    <a href="{{ $bookUrl }}" class="btn btn-primary btn-lg"><i class="fas fa-calendar-plus mr-2"></i>Booking Sekarang</a>
                    <a href="#layanan" class="btn btn-outline-primary btn-lg">Lihat Layanan</a>
                </div>
                <div class="hero-trust">
                    <div class="item"><i class="fas fa-user-doctor"></i> Terapis berlisensi</div>
                    <div class="item"><i class="fas fa-house-medical"></i> Bisa home visit</div>
                    <div class="item"><i class="fas fa-gift"></i> Konsultasi pertama gratis</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="stage-3d" data-spine3d data-reveal="zoom">
                    <div class="stage-glow"></div>
                    <img src="{{ asset('assets/img/hero-fisioterapi.jpg') }}" alt="Sesi fisioterapi" class="stage-fallback">
                    <div class="float-card fc-1">
                        <span class="ic" style="background:var(--gradient)"><i class="fas fa-heart-pulse"></i></span>
                        <div><strong>{{ $services->count() }} Layanan</strong>Terapi spesialis</div>
                    </div>
                    <div class="float-card fc-2">
                        <span class="ic" style="background:#f59e0b"><i class="fas fa-star"></i></span>
                        <div><strong>{{ $numbers['rating'] > 0 ? number_format($numbers['rating'], 1).' / 5' : 'Baru' }}</strong>Rating pasien</div>
                    </div>
                    <div class="float-card fc-3">
                        <span class="ic" style="background:#0ea5e9"><i class="fas fa-clock"></i></span>
                        <div><strong>Buka 7 hari</strong>Senin – Minggu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ ANGKA ============ --}}
<section class="section-sm">
    <div class="container">
        <div class="numbers" data-reveal>
            <div class="row text-center position-relative">
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="num" data-count="{{ $numbers['pasien'] }}">0</div><div class="lbl">Pasien terdaftar</div>
                </div>
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="num" data-count="{{ $numbers['sesi'] }}">0</div><div class="lbl">Sesi selesai</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="num" data-count="{{ $numbers['terapis'] }}">0</div><div class="lbl">Fisioterapis</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="num" data-count="{{ $services->count() }}">0</div><div class="lbl">Jenis layanan</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ LAYANAN ============ --}}
<section class="section" id="layanan">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="eyebrow"><i class="fas fa-stethoscope"></i> Layanan Kami</span>
            <h2 class="section-title">Terapi yang dirancang untuk <span class="text-gradient">tubuh Anda</span></h2>
            <p class="section-sub">Setiap program disesuaikan dengan kondisi, usia, dan target pemulihan Anda.</p>
        </div>
        <div class="row">
            @foreach ($services as $service)
                <div class="col-lg-4 col-md-6 mb-4" data-reveal style="--d: {{ ($loop->index % 3) * 0.1 }}s">
                    @include('partials.service-card')
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CARA BOOKING ============ --}}
<section class="section bg-soft" id="cara-booking">
    <div class="cube-3d d-none d-lg-block" style="left:6%;top:80px" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <div class="plus-3d d-none d-lg-block" style="right:8%;bottom:80px" aria-hidden="true"></div>
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="eyebrow"><i class="fas fa-route"></i> Cara Booking</span>
            <h2 class="section-title">Empat langkah menuju pemulihan</h2>
            <p class="section-sub">Tanpa antre, tanpa telepon. Pantau status booking Anda kapan saja.</p>
        </div>
        <div class="row">
            @foreach ([
                ['fa-user-plus', 'Buat akun', 'Daftar gratis dengan username dan email Anda.'],
                ['fa-hand-holding-medical', 'Pilih layanan', 'Pilih jenis terapi dan fisioterapis favorit Anda.'],
                ['fa-calendar-days', 'Pilih jadwal', 'Lihat slot jam yang masih kosong secara langsung.'],
                ['fa-ticket', 'Datang & pulih', 'Tunjukkan tiket booking (QR) saat tiba di klinik.'],
            ] as $i => [$icon, $title, $text])
                <div class="col-md-6 col-lg-3 mb-4" data-reveal style="--d: {{ $i * 0.12 }}s">
                    <div class="card-soft step-card" data-tilt="10">
                        <div class="step-num"><i class="fas {{ $icon }}"></i><b>{{ $i + 1 }}</b></div>
                        <h4>{{ $title }}</h4>
                        <p>{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ KEUNGGULAN ============ --}}
<section class="section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-5 mb-lg-0" data-reveal="left">
                <div class="position-relative" style="perspective:1000px">
                    <img src="{{ asset('assets/img/dashboard-fisioterapi.jpg') }}" alt="Fisioterapis membantu pasien" class="img-fluid"
                         style="border-radius:28px;box-shadow:var(--shadow-lg);transform:rotateY(10deg) rotateX(4deg)" loading="lazy">
                    <div class="float-card" style="right:-10px;bottom:-20px">
                        <span class="ic" style="background:var(--gradient)"><i class="fas fa-house-medical"></i></span>
                        <div><strong>Home Visit</strong>s.d. {{ config('klinik.jarak_home_visit_km') }} km</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1" data-reveal="right">
                <span class="eyebrow"><i class="fas fa-award"></i> Mengapa Kami</span>
                <h2 class="section-title">Perawatan yang personal, hasil yang terukur</h2>
                <p class="section-sub mb-4">Kami menggabungkan terapi manual, latihan fungsional, dan edukasi agar Anda pulih dan tidak cedera lagi.</p>
                @foreach ([
                    ['fa-user-doctor', 'Tenaga ahli bersertifikat', 'Fisioterapis berpengalaman minimal 5 tahun di rumah sakit & klinik.'],
                    ['fa-chart-line', 'Evaluasi berkala', 'Kemajuan Anda dicatat dan program disesuaikan setiap sesi.'],
                    ['fa-house-medical', 'Klinik atau rumah', 'Datang ke klinik atau terapis yang datang ke rumah Anda.'],
                    ['fa-wallet', 'Harga transparan', 'Harga per sesi jelas sejak awal, bayar setelah sesi selesai.'],
                ] as [$icon, $title, $text])
                    <div class="feature-row mb-4">
                        <span class="icon-chip"><i class="fas {{ $icon }}"></i></span>
                        <div><h5>{{ $title }}</h5><p>{{ $text }}</p></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============ TERAPIS ============ --}}
@if ($therapists->isNotEmpty())
<section class="section bg-soft" id="terapis">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="eyebrow"><i class="fas fa-user-nurse"></i> Tim Kami</span>
            <h2 class="section-title">Kenali fisioterapis Anda</h2>
            <p class="section-sub">Pilih terapis sesuai spesialisasi saat booking, atau biarkan kami memilihkan yang terbaik.</p>
        </div>
        <div class="row">
            @foreach ($therapists as $therapist)
                <div class="col-sm-6 col-lg-3 mb-4" data-reveal style="--d: {{ $loop->index * 0.1 }}s">
                    @include('partials.therapist-card')
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ ULASAN ============ --}}
@if ($reviews->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-head" data-reveal>
            <span class="eyebrow"><i class="fas fa-comment-dots"></i> Kata Pasien</span>
            <h2 class="section-title">Cerita pemulihan mereka</h2>
        </div>
        <div class="row">
            @foreach ($reviews as $review)
                <div class="col-md-6 col-lg-4 mb-4" data-reveal style="--d: {{ ($loop->index % 3) * 0.1 }}s">
                    <div class="card-soft review-card" data-tilt="6">
                        <div class="stars" aria-label="Rating {{ $review->rating }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>@endfor
                        </div>
                        <blockquote>“{{ $review->ulasan }}”</blockquote>
                        <div class="review-who">
                            <div class="avatar-3d sm">{{ strtoupper(mb_substr($review->nama, 0, 1)) }}</div>
                            <div>
                                <strong class="d-block text-dark">{{ \Illuminate\Support\Str::of($review->nama)->explode(' ')->first() }}</strong>
                                <small class="text-muted">{{ $review->service->name }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
<section class="section {{ $reviews->isNotEmpty() ? 'bg-soft' : '' }}" id="faq">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4" data-reveal="left">
                <span class="eyebrow"><i class="fas fa-circle-question"></i> FAQ</span>
                <h2 class="section-title">Pertanyaan yang sering diajukan</h2>
                <p class="section-sub">Belum menemukan jawabannya? Tanyakan ke asisten chat di pojok kanan bawah atau hubungi kami.</p>
                <a href="tel:{{ config('klinik.telepon_link') }}" class="btn btn-light-soft mt-2"><i class="fas fa-phone mr-2"></i>{{ config('klinik.telepon') }}</a>
            </div>
            <div class="col-lg-8" data-reveal="right" id="faqList">
                @foreach ([
                    ['Apakah perlu rujukan dokter?', 'Tidak wajib. Fisioterapis kami akan melakukan pemeriksaan awal pada sesi pertama. Jika Anda punya hasil pemeriksaan atau rontgen, bawa saat datang.'],
                    ['Berapa lama satu sesi terapi?', 'Antara 45 sampai 120 menit tergantung jenis layanan. Durasi setiap layanan tertera di kartu layanan.'],
                    ['Bagaimana jika saya ingin membatalkan?', 'Booking berstatus "Menunggu" bisa dibatalkan sendiri dari menu Riwayat. Jika sudah dikonfirmasi, hubungi kami.'],
                    ['Apakah bisa terapi di rumah?', 'Bisa. Tulis permintaan home visit di kolom catatan saat booking. Ada tambahan biaya transport Rp '.number_format(config('klinik.biaya_home_visit'), 0, ',', '.').' untuk jarak maksimal '.config('klinik.jarak_home_visit_km').' km.'],
                    ['Metode pembayaran apa saja?', 'Tunai, transfer bank, e-wallet, dan kartu debit/kredit. Pembayaran dilakukan setelah sesi selesai.'],
                ] as $i => [$q, $a])
                    <div class="faq-item">
                        <button class="faq-q" type="button" data-toggle="collapse" data-target="#faq{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq{{ $i }}">
                            {{ $q }} <i class="fas fa-plus"></i>
                        </button>
                        <div id="faq{{ $i }}" class="collapse {{ $i === 0 ? 'show' : '' }}" data-parent="#faqList">
                            <div class="faq-a">{{ $a }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="section pt-0">
    <div class="container">
        <div class="cta" data-reveal="zoom">
            <span class="orb sky" style="width:180px;height:180px;right:-50px;top:-60px;opacity:.4"></span>
            <div class="cube-3d d-none d-md-block" style="right:14%;bottom:40px" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>
            <div class="row align-items-center position-relative">
                <div class="col-lg-8">
                    <h2>Siap memulai perjalanan pemulihan Anda?</h2>
                    <p class="mb-lg-0">Konsultasi pertama gratis. Pilih jadwal yang tersedia dan dapatkan tiket booking Anda sekarang.</p>
                </div>
                <div class="col-lg-4 text-lg-right">
                    <a href="{{ $bookUrl }}" class="btn btn-white btn-lg"><i class="fas fa-calendar-plus mr-2"></i>Booking Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="{{ asset('assets/js/spine3d.js') }}?v=2"></script>
@endpush
