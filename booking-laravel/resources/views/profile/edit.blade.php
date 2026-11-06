@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('dashboard').'">Beranda</a> / Profil',
    'title' => 'Profil Saya',
    'subtitle' => 'Data ini dipakai untuk mengisi form booking secara otomatis.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card-soft profile-card" data-reveal="left">
                    <div class="profile-cover"><span class="orb" style="width:120px;height:120px;right:-30px;top:-40px;opacity:.35"></span></div>
                    <img src="{{ $user->profilePicUrl() }}" alt="Foto profil" class="profile-photo">
                    <div class="p-4">
                        <h4 class="mb-0">{{ $user->displayName() }}</h4>
                        <div class="text-muted mb-3">{{ '@'.$user->username }}</div>
                        <div class="row text-center mb-3">
                            <div class="col"><div class="h4 mb-0 fw-800">{{ $totalBooking }}</div><small class="text-muted">Booking</small></div>
                            <div class="col"><div class="h4 mb-0 fw-800">{{ $totalSelesai }}</div><small class="text-muted">Sesi selesai</small></div>
                        </div>
                        <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <img id="photoPreview" class="photo-preview" alt="Pratinjau foto baru">
                            <div class="custom-file mb-2 text-left">
                                <input type="file" class="custom-file-input" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/gif" required data-preview-target="photoPreview">
                                <label class="custom-file-label" for="profile_pic">Pilih foto...</label>
                            </div>
                            @error('profile_pic')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            <small class="d-block text-muted mb-3">JPG, PNG, GIF · maks. 2MB</small>
                            <button type="submit" class="btn btn-light-soft btn-block"><i class="fas fa-camera mr-1"></i>Ganti Foto</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card-soft pad-lg" data-reveal="right">
                    <div class="card-head"><h4><i class="fas fa-id-card text-primary mr-2"></i>Data Diri</h4></div>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Username</label>
                                <input type="text" class="form-control" value="{{ $user->username }}" disabled>
                                <small class="text-muted">Username tidak bisa diubah.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nama_lengkap">Nama lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="form-control @error('nama_lengkap') is-invalid @enderror" maxlength="100">
                                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="no_hp">Nomor HP</label>
                                <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
                            <a href="{{ route('profile.password') }}" class="mb-2"><i class="fas fa-key mr-1"></i>Ubah password</a>
                            <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-floppy-disk mr-1"></i>Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('profile_pic').addEventListener('change', function () {
        this.nextElementSibling.textContent = this.files[0] ? this.files[0].name : 'Pilih foto...';
    });
</script>
@endpush
