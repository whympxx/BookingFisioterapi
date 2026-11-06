@extends('layouts.app')

@section('title', 'Kelola Terapis')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Terapis',
    'title' => 'Kelola Fisioterapis',
    'subtitle' => 'Terapis aktif tampil di halaman utama dan bisa dipilih pasien saat booking.',
])

<div class="page-body">
    <div class="container">
        <div class="d-flex justify-content-end mb-3" data-reveal>
            <a href="{{ route('admin.therapists.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Tambah Terapis</a>
        </div>
        <div class="row">
            @forelse ($therapists as $therapist)
                <div class="col-md-6 col-lg-4 mb-4" data-reveal style="--d: {{ ($loop->index % 3) * 0.08 }}s">
                    <div class="card-soft h-100 text-center" data-tilt="6" style="{{ $therapist->is_active ? '' : 'opacity:.6' }}">
                        @if ($therapist->photoUrl())
                            <img src="{{ $therapist->photoUrl() }}" alt="" class="avatar-3d mx-auto">
                        @else
                            <div class="avatar-3d mx-auto">{{ $therapist->initials() }}</div>
                        @endif
                        <h5 class="mb-1">{{ $therapist->name }}</h5>
                        <div class="text-primary font-weight-bold small mb-2">{{ $therapist->specialization }}</div>
                        <div class="mb-3">
                            <span class="badge badge-soft">{{ $therapist->experience_years }} th</span>
                            <span class="badge badge-info">{{ $therapist->bookings_count }} booking</span>
                            <span class="badge badge-warning">★ {{ $therapist->rating_avg ? number_format($therapist->rating_avg, 1) : '—' }}</span>
                            <span class="badge badge-{{ $therapist->is_active ? 'success' : 'secondary' }}">{{ $therapist->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="small text-muted mb-3">
                            @if ($therapist->user)
                                <i class="fas fa-user-lock mr-1"></i>Login: <b>{{ $therapist->user->username }}</b>
                            @else
                                <i class="fas fa-user-slash mr-1"></i>Belum punya akun login
                            @endif
                        </div>
                        <a href="{{ route('admin.therapists.edit', $therapist) }}" class="btn btn-light-soft btn-sm"><i class="fas fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('admin.therapists.toggle', $therapist) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $therapist->is_active ? 'btn-outline-danger' : 'btn-outline-primary' }}">{{ $therapist->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="card-soft text-center text-muted py-5">Belum ada data terapis.</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
