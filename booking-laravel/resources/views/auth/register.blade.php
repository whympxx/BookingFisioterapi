@extends('layouts.auth')

@section('title', 'Daftar')
@section('heading', 'Buat akun pasien')
@section('subheading', 'Gratis. Booking sesi pertama Anda dalam hitungan menit.')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-icon"><i class="fas fa-at"></i>
                <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control" required autocomplete="username" placeholder="huruf, angka, - atau _">
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-icon"><i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" placeholder="nama@email.com">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-sm-6">
                <label for="password">Password</label>
                <div class="input-icon"><i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="new-password" placeholder="min. 6 karakter">
                </div>
            </div>
            <div class="form-group col-sm-6">
                <label for="password_confirmation">Ulangi password</label>
                <div class="input-icon"><i class="fas fa-lock"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">Daftar <i class="fas fa-arrow-right ml-1"></i></button>
    </form>
    <div class="auth-links text-center">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
@endsection
