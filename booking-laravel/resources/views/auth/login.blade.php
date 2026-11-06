@extends('layouts.auth')

@section('title', 'Masuk')
@section('heading', 'Selamat datang kembali 👋')
@section('subheading', 'Masuk untuk mengelola jadwal fisioterapi Anda.')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-icon"><i class="fas fa-user"></i>
                <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control" required autofocus autocomplete="username" placeholder="Username Anda">
            </div>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label for="password">Password</label>
                <a href="{{ route('password.request') }}" class="small font-weight-bold">Lupa password?</a>
            </div>
            <div class="input-icon position-relative"><i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••" style="padding-right:3rem">
                <button type="button" class="btn btn-link position-absolute text-muted" style="right:4px;top:50%;transform:translateY(-50%)" data-toggle-password="password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="custom-control custom-checkbox mb-4">
            <input type="checkbox" class="custom-control-input" id="remember" name="remember">
            <label class="custom-control-label font-weight-normal" for="remember">Ingat saya</label>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Masuk <i class="fas fa-arrow-right ml-1"></i></button>
    </form>
    <div class="auth-links text-center">Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a></div>
@endsection
