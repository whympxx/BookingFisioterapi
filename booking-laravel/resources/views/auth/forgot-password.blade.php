@extends('layouts.auth')

@section('title', 'Lupa Password')
@section('heading', 'Lupa password?')
@section('subheading', 'Masukkan email akun Anda. Kami akan mengirim link untuk membuat password baru.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-icon"><i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus placeholder="nama@email.com">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Kirim Link Reset</button>
    </form>
    <div class="auth-links text-center">Ingat password? <a href="{{ route('login') }}">Masuk</a></div>
@endsection
