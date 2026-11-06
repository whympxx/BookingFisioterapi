@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Buat password baru')
@section('subheading', 'Gunakan minimal 6 karakter.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-icon"><i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password baru</label>
            <div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Ulangi password baru</label>
            <div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Simpan Password</button>
    </form>
@endsection
