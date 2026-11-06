<!DOCTYPE html>
<html lang="id" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ config('klinik.nama') }} — layanan fisioterapi profesional, booking online mudah dan cepat.">
    <meta name="theme-color" content="#0d9488">
    <title>@yield('title') · {{ config('klinik.nama') }}</title>
    <link rel="icon" href="{{ asset('assets/img/ikon_fisioterapi.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-4.6.2-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v=5">
    @stack('styles')
</head>
<body>
@php($authUser = auth()->user())

<header class="nav-glass">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="brand" href="{{ $authUser ? route($authUser->homeRoute()) : route('home') }}">
                <span class="brand-mark"><i class="fas fa-spa"></i></span>
                <span>{{ config('klinik.nama') }}<small>{{ match ($authUser?->role) { 'admin' => 'Panel Admin', 'terapis' => 'Panel Fisioterapis', default => 'Klinik Fisioterapi' } }}</small></span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu">
                <i class="fas fa-bars text-primary"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ml-auto align-items-lg-center">
                    @if ($authUser?->isAdmin())
                        @foreach ([
                            ['admin.dashboard', 'admin.dashboard', 'Dasbor'],
                            ['admin.bookings.index', 'admin.bookings.*', 'Booking'],
                            ['admin.payments.index', 'admin.payments.*', 'Pembayaran'],
                            ['admin.services.index', 'admin.services.*', 'Layanan'],
                            ['admin.therapists.index', 'admin.therapists.*', 'Terapis'],
                            ['admin.medicines.index', 'admin.medicines.*', 'Obat'],
                        ] as [$route, $pattern, $label])
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a></li>
                        @endforeach
                    @elseif ($authUser?->isTherapist())
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('panel.dashboard') ? 'active' : '' }}" href="{{ route('panel.dashboard') }}">Dasbor</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('panel.schedule', 'panel.patient') ? 'active' : '' }}" href="{{ route('panel.schedule') }}">Jadwal Pasien</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('medicines.index') ? 'active' : '' }}" href="{{ route('medicines.index') }}">Daftar Obat</a></li>
                    @elseif ($authUser)
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('booking.create') ? 'active' : '' }}" href="{{ route('booking.create') }}">Booking</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('booking.index', 'booking.show', 'payment.*') ? 'active' : '' }}" href="{{ route('booking.index') }}">Riwayat</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('therapists.*') ? 'active' : '' }}" href="{{ route('therapists.index') }}">Terapis</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('medicines.index') ? 'active' : '' }}" href="{{ route('medicines.index') }}">Obat</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('layanan') ? 'active' : '' }}" href="{{ route('layanan') }}">Layanan</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('therapists.*') ? 'active' : '' }}" href="{{ route('therapists.index') }}">Terapis</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('medicines.index') ? 'active' : '' }}" href="{{ route('medicines.index') }}">Obat</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#faq">FAQ</a></li>
                    @endif

                    @if ($authUser)
                        <li class="nav-item dropdown ml-lg-2">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown"
                               role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ $authUser->profilePicUrl() }}" alt="" class="nav-avatar mr-2">
                                <span>{{ $authUser->username }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                                @if ($authUser->role === 'user')
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user"></i> Profil Saya</a>
                                @elseif ($authUser->isAdmin())
                                    <a class="dropdown-item" href="{{ route('admin.users.create') }}"><i class="fas fa-user-shield"></i> Tambah Admin</a>
                                @endif
                                <a class="dropdown-item" href="{{ route('profile.password') }}"><i class="fas fa-key"></i> Ubah Password</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-right-from-bracket"></i> Logout</button>
                                </form>
                            </div>
                        </li>
                    @else
                        <li class="nav-item ml-lg-2"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
                        <li class="nav-item ml-lg-1"><a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">Daftar Gratis</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</header>

@include('partials.flash')

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <a class="brand mb-3" href="{{ route('home') }}">
                    <span class="brand-mark"><i class="fas fa-spa"></i></span>
                    <span>{{ config('klinik.nama') }}</span>
                </a>
                <p class="mt-3">Fisioterapi profesional untuk pemulihan cedera, nyeri, stroke, dan tumbuh kembang anak — di klinik atau di rumah Anda.</p>
            </div>
            <div class="col-6 col-lg-2 mb-4">
                <h5>Menu</h5>
                <ul>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('layanan') }}">Layanan</a></li>
                    <li><a href="{{ $authUser?->role === 'user' ? route('booking.create') : route('login') }}">Booking</a></li>
                    <li><a href="{{ route('therapists.index') }}">Fisioterapis</a></li>
                    <li><a href="{{ route('medicines.index') }}">Obat & Anjuran</a></li>
                    <li><a href="{{ route('home') }}#faq">FAQ</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3 mb-4">
                <h5>Kontak</h5>
                <div class="contact-line"><i class="fas fa-phone"></i><a href="tel:{{ config('klinik.telepon_link') }}">{{ config('klinik.telepon') }}</a></div>
                <div class="contact-line"><i class="fas fa-envelope"></i><a href="mailto:{{ config('klinik.email') }}">{{ config('klinik.email') }}</a></div>
                <div class="contact-line"><i class="fas fa-location-dot"></i><span>{{ config('klinik.alamat') }}</span></div>
            </div>
            <div class="col-lg-3 mb-4">
                <h5>Jam Operasional</h5>
                @foreach (config('klinik.jam_operasional_label') as $hari => $jam)
                    <div class="hours-row"><span>{{ $hari }}</span><span>{{ $jam }}</span></div>
                @endforeach
            </div>
        </div>
        <div class="footer-bottom d-flex flex-wrap justify-content-between">
            <span>&copy; {{ date('Y') }} {{ config('klinik.nama') }}. Semua hak dilindungi.</span>
            <span>Dibuat dengan <i class="fas fa-heart text-danger"></i> untuk pemulihan Anda</span>
        </div>
    </div>
</footer>

@unless ($authUser?->isAdmin())
    @include('partials.chatbot')
@endunless

<script src="{{ asset('assets/bootstrap-4.6.2-dist/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('assets/bootstrap-4.6.2-dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}?v=3"></script>
@unless ($authUser?->isAdmin())
    <script src="{{ asset('assets/chatbot.js') }}?v=2"></script>
@endunless
@stack('scripts')
</body>
</html>
