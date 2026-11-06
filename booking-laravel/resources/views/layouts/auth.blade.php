<!DOCTYPE html>
<html lang="id" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d9488">
    <title>@yield('title') · {{ config('klinik.nama') }}</title>
    <link rel="icon" href="{{ asset('assets/img/ikon_fisioterapi.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-4.6.2-dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}?v=5">
</head>
<body class="no-nav">
<div class="auth-wrap">
    <aside class="auth-visual">
        <span class="orb sky" style="width:200px;height:200px;right:-60px;top:-60px;opacity:.35"></span>
        <span class="orb" style="width:120px;height:120px;left:-40px;bottom:60px;opacity:.35"></span>
        <a class="brand position-relative" href="{{ route('home') }}">
            <span class="brand-mark"><i class="fas fa-spa"></i></span>
            <span>{{ config('klinik.nama') }}</span>
        </a>
        <div class="stage-3d" data-spine3d data-variant="light"></div>
        <div class="position-relative">
            <h2>Pulih lebih cepat bersama fisioterapis profesional.</h2>
            <p class="mb-0">Booking online, pilih terapis, dan pantau jadwal Anda di satu tempat.</p>
        </div>
    </aside>

    <section class="auth-form-side">
        <div class="auth-form" data-reveal>
            <a class="brand auth-mobile-brand" href="{{ route('home') }}">
                <span class="brand-mark"><i class="fas fa-spa"></i></span>
                <span>{{ config('klinik.nama') }}</span>
            </a>
            <h1>@yield('heading')</h1>
            <p class="sub">@yield('subheading')</p>

            @if (session('success'))
                <div class="alert alert-success"><i class="fas fa-circle-check mr-1"></i>{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div><i class="fas fa-circle-exclamation mr-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')

            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="text-muted small"><i class="fas fa-arrow-left mr-1"></i>Kembali ke beranda</a>
            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="{{ asset('assets/js/spine3d.js') }}?v=2"></script>
<script src="{{ asset('assets/js/app.js') }}?v=2"></script>
<script>
    // Tombol lihat/sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.togglePassword);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });
</script>
</body>
</html>
