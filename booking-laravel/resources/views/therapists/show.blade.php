@extends('layouts.app')

@section('title', $therapist->name)

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('therapists.index').'">Fisioterapis</a> / '.e($therapist->name),
    'title' => $therapist->name,
    'subtitle' => $therapist->specialization,
])

<div class="page-body">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card-soft profile-card" data-reveal="left">
                    <div class="profile-cover"></div>
                    @if ($therapist->photoUrl())
                        <img src="{{ $therapist->photoUrl() }}" alt="{{ $therapist->name }}" class="profile-photo">
                    @else
                        <div class="profile-photo doc-initials mx-auto">{{ $therapist->initials() }}</div>
                    @endif
                    <div class="p-4">
                        <h4 class="mb-1">{{ $therapist->name }}</h4>
                        <div class="text-primary font-weight-bold mb-3">{{ $therapist->specialization }}</div>
                        <div class="doc-stats mb-4">
                            <div><b>{{ $therapist->experience_years }}</b><span>tahun</span></div>
                            <div><b>{{ $sesi }}</b><span>sesi selesai</span></div>
                            <div><b>{{ $rating ?? '—' }}</b><span>★ {{ $ratingCount }} ulasan</span></div>
                        </div>
                        <a href="{{ route('booking.create', ['terapis' => $therapist->id]) }}" class="btn btn-primary btn-block"><i class="fas fa-calendar-plus mr-2"></i>Booking dengan {{ explode(' ', preg_replace('/^(Ftr\.|dr\.)\s*/i', '', $therapist->name))[0] }}</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card-soft pad-lg mb-4" data-reveal>
                    <h4><i class="fas fa-user-nurse text-primary mr-2"></i>Tentang</h4>
                    <p class="mb-4">{{ $therapist->bio ?: 'Fisioterapis berlisensi yang siap membantu pemulihan Anda.' }}</p>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <h6><i class="fas fa-award text-primary mr-2"></i>Keahlian</h6>
                            <div class="med-tags"><span>{{ $therapist->specialization }}</span><span>Konsultasi & evaluasi</span><span>Program latihan di rumah</span></div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <h6><i class="far fa-clock text-primary mr-2"></i>Jadwal praktik</h6>
                            @foreach (config('klinik.jam_operasional_label') as $hari => $jam)
                                <div class="d-flex justify-content-between small py-1 border-bottom"><span>{{ $hari }}</span><b>{{ $jam }}</b></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card-soft pad-lg" data-reveal>
                    <h4 class="mb-3"><i class="fas fa-star text-warning mr-2"></i>Ulasan Pasien</h4>
                    @forelse ($reviews as $review)
                        <div class="review-line">
                            <div class="d-flex justify-content-between flex-wrap">
                                <b>{{ \Illuminate\Support\Str::mask($review->nama, '*', 2, max(0, mb_strlen($review->nama) - 3)) }}</b>
                                <span class="stars">@for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>@endfor</span>
                            </div>
                            <small class="text-muted">{{ $review->service->name }} · {{ $review->rated_at?->translatedFormat('d M Y') }}</small>
                            @if ($review->ulasan)<p class="mb-0 mt-1">{{ $review->ulasan }}</p>@endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada ulasan untuk fisioterapis ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
