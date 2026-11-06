@extends('layouts.app')

@php($editing = $therapist->exists)

@section('title', $editing ? 'Edit Terapis' : 'Tambah Terapis')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.therapists.index').'">Terapis</a> / '.($editing ? 'Edit' : 'Baru'),
    'title' => $editing ? 'Edit '.$therapist->name : 'Tambah Fisioterapis',
])

<div class="page-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-soft pad-lg" data-reveal>
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form method="POST" action="{{ $editing ? route('admin.therapists.update', $therapist) : route('admin.therapists.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if ($editing) @method('PUT') @endif
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="name">Nama lengkap & gelar</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $therapist->name) }}" class="form-control" required maxlength="100" placeholder="Ftr. Nama Lengkap">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="experience_years">Pengalaman (tahun)</label>
                                <input type="number" id="experience_years" name="experience_years" value="{{ old('experience_years', $therapist->experience_years) }}" class="form-control" required min="0" max="60">
                            </div>
                            <div class="form-group col-12">
                                <label for="specialization">Spesialisasi</label>
                                <input type="text" id="specialization" name="specialization" value="{{ old('specialization', $therapist->specialization) }}" class="form-control" required maxlength="100">
                            </div>
                            <div class="form-group col-12">
                                <label for="bio">Bio singkat</label>
                                <textarea id="bio" name="bio" class="form-control" rows="3" maxlength="500">{{ old('bio', $therapist->bio) }}</textarea>
                            </div>
                            <div class="form-group col-12">
                                <label for="photo">Foto <small class="text-muted font-weight-normal">(opsional, maks 2MB)</small></label>
                                <input type="file" id="photo" name="photo" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div class="account-box mb-4">
                            <h6 class="mb-1"><i class="fas fa-user-lock text-primary mr-2"></i>Akun login halaman terapis</h6>
                            <p class="small text-muted mb-3">
                                @if ($therapist->user)
                                    Terapis login di halaman yang sama dengan pasien, lalu otomatis masuk ke Panel Terapis. Kosongkan password bila tidak ingin mengganti.
                                @else
                                    Opsional. Isi agar terapis bisa login, melihat jadwal pasien, dan menulis catatan serta anjuran obat.
                                @endif
                            </p>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="{{ old('username', $therapist->user?->username) }}" class="form-control" maxlength="50" autocomplete="off">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $therapist->user?->email) }}" class="form-control" maxlength="100" autocomplete="off">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="password">{{ $therapist->user ? 'Password baru' : 'Password' }}</label>
                                    <input type="password" id="password" name="password" class="form-control" minlength="6" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="custom-control custom-switch mb-4">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $therapist->is_active))>
                            <label class="custom-control-label font-weight-normal" for="is_active">Aktif (bisa dipilih pasien)</label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.therapists.index') }}" class="btn btn-light-soft">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk mr-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
