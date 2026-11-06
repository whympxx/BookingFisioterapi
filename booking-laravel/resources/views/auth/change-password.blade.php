@extends('layouts.app')

@section('title', 'Ubah Password')

@php($back = auth()->user()->isAdmin() ? route('admin.dashboard') : route('profile.edit'))

@section('content')
@include('partials.page-hero', [
    'eyebrow' => 'Keamanan Akun',
    'eyebrowIcon' => 'fa-shield-halved',
    'title' => 'Ubah Password',
    'subtitle' => 'Gunakan minimal 6 karakter dan jangan pakai password yang sama dengan akun lain.',
])

<div class="page-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card-soft pad-lg" data-reveal="zoom">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="password_lama">Password lama</label>
                            <div class="input-icon"><i class="fas fa-lock"></i>
                                <input type="password" id="password_lama" name="password_lama" class="form-control @error('password_lama') is-invalid @enderror" required autocomplete="current-password">
                            </div>
                            @error('password_lama')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Password baru</label>
                            <div class="input-icon"><i class="fas fa-key"></i>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6" autocomplete="new-password">
                            </div>
                            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Ulangi password baru</label>
                            <div class="input-icon"><i class="fas fa-key"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ $back }}" class="btn btn-light-soft">Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk mr-1"></i>Simpan Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
