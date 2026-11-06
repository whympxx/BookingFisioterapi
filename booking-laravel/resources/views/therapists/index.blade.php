@extends('layouts.app')

@section('title', 'Tim Fisioterapis')

@section('content')
@include('partials.page-hero', [
    'eyebrow' => 'Tim Kami',
    'eyebrowIcon' => 'fa-user-nurse',
    'title' => 'Fisioterapis Berlisensi',
    'subtitle' => 'Pilih fisioterapis sesuai keluhan Anda. Lihat profil, keahlian, dan ulasan pasien sebelum booking.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            @forelse ($therapists as $therapist)
                <div class="col-sm-6 col-lg-3 mb-4 d-flex" data-reveal style="--d: {{ ($loop->index % 4) * 0.08 }}s">
                    <article class="card-soft doc-card text-center w-100" data-tilt="7">
                        <div class="doc-cover"></div>
                        @if ($therapist->photoUrl())
                            <img src="{{ $therapist->photoUrl() }}" alt="{{ $therapist->name }}" class="avatar-3d mx-auto doc-avatar">
                        @else
                            <div class="avatar-3d mx-auto doc-avatar" aria-hidden="true">{{ $therapist->initials() }}</div>
                        @endif
                        <h5 class="mb-1">{{ $therapist->name }}</h5>
                        <div class="text-primary font-weight-bold small mb-3">{{ $therapist->specialization }}</div>
                        <div class="doc-stats mb-3">
                            <div><b>{{ $therapist->experience_years }}</b><span>tahun</span></div>
                            <div><b>{{ $therapist->sesi_count }}</b><span>sesi</span></div>
                            <div><b>{{ $therapist->rating_avg ? number_format($therapist->rating_avg, 1) : '—' }}</b><span>★ rating</span></div>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('therapists.show', $therapist) }}" class="btn btn-light-soft btn-sm">Profil</a>
                            <a href="{{ route('booking.create', ['terapis' => $therapist->id]) }}" class="btn btn-primary btn-sm">Booking</a>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12"><div class="card-soft text-center text-muted py-5">Belum ada data fisioterapis.</div></div>
            @endforelse
        </div>
    </div>
</div>
@endsection
