@extends('layouts.app')

@section('title', 'Obat & Anjuran')

@section('content')
@include('partials.page-hero', [
    'eyebrow' => 'Panduan Obat Fisioterapi',
    'eyebrowIcon' => 'fa-pills',
    'title' => 'Obat & Suplemen yang Dianjurkan',
    'subtitle' => 'Pilih keluhan Anda untuk melihat obat pereda nyeri, gel, dan suplemen yang umum dianjurkan, lengkap dengan dosis dan peringatannya.',
])

<div class="page-body">
    <div class="container">
        <div class="disclaimer mb-4" data-reveal>
            <i class="fas fa-user-doctor"></i>
            <div>
                <strong>Informasi edukasi, bukan pengganti resep.</strong>
                Selalu baca label kemasan dan tanyakan ke dokter atau apoteker, terutama jika Anda hamil, punya penyakit kronis, atau sedang minum obat lain.
                Obat bertanda <span class="badge badge-danger">Obat Keras (Resep)</span> hanya boleh dipakai dengan resep dokter.
                Segera ke IGD bila nyeri sangat hebat, muncul lemah/kebas mendadak, atau setelah benturan keras.
            </div>
        </div>

        @if ($fromTherapist->isNotEmpty())
            <div class="card-soft pad-lg mb-4" data-reveal style="border-left:5px solid var(--primary)">
                <h4 class="mb-1"><i class="fas fa-user-nurse text-primary mr-2"></i>Anjuran dari fisioterapis Anda</h4>
                <p class="text-muted">Berdasarkan pemeriksaan pada sesi terakhir Anda.</p>
                @foreach ($fromTherapist as $b)
                    <div class="small font-weight-bold text-muted text-uppercase mt-3 mb-2">
                        {{ $b->service->name }} · {{ $b->tanggal_booking->translatedFormat('d M Y') }} · {{ $b->therapist?->name }}
                    </div>
                    @foreach ($b->medicines as $med)
                        @include('partials.medicine-row', ['medicine' => $med, 'aturan' => $med->pivot->aturan_pakai])
                    @endforeach
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('medicines.index') }}" class="card-soft pad-lg mb-4" data-reveal id="finder">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="fas fa-wand-magic-sparkles text-primary mr-2"></i>Cari anjuran sesuai kebutuhan</h4>
                <div class="input-icon mt-2 mt-md-0" style="min-width:240px">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Cari nama obat…">
                </div>
            </div>

            <div class="form-block-title"><span class="icon-chip"><b>1</b></span><h5>Apa keluhan Anda?</h5></div>
            <div class="chip-grid mb-4">
                @foreach ($allNeeds as $key => $need)
                    <label class="chip-check">
                        <input type="checkbox" name="kebutuhan[]" value="{{ $key }}" @checked(in_array($key, $needs, true))>
                        <span><i class="fas {{ $need['icon'] }}"></i>{{ $need['label'] }}</span>
                    </label>
                @endforeach
            </div>

            <div class="form-block-title"><span class="icon-chip"><b>2</b></span><h5>Ada kondisi berikut? <small class="text-muted font-weight-normal">(opsional)</small></h5></div>
            <div class="chip-grid mb-4">
                @foreach ($allConditions as $key => $label)
                    <label class="chip-check warn">
                        <input type="checkbox" name="kondisi[]" value="{{ $key }}" @checked(in_array($key, $conditions, true))>
                        <span><i class="fas fa-heart-circle-exclamation"></i>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="d-flex flex-wrap">
                <button type="submit" class="btn btn-primary mr-2 mb-2"><i class="fas fa-filter mr-1"></i>Tampilkan anjuran</button>
                @if ($filtered)
                    <a href="{{ route('medicines.index') }}" class="btn btn-light-soft mb-2">Reset</a>
                @endif
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-end mb-3" data-reveal>
            <div>
                <span class="eyebrow"><i class="fas fa-circle-check"></i> {{ $filtered ? 'Hasil' : 'Semua' }}</span>
                <h2 class="section-title mb-0" style="font-size:1.6rem">
                    {{ $filtered ? 'Cocok untuk kebutuhan Anda' : 'Daftar obat & suplemen' }}
                    <small class="text-muted" style="font-size:1rem">({{ $suitable->count() }})</small>
                </h2>
            </div>
        </div>
        <div class="row">
            @forelse ($suitable as $medicine)
                <div class="col-md-6 col-lg-4 mb-4 d-flex" data-reveal style="--d: {{ ($loop->index % 3) * 0.08 }}s">
                    @include('partials.medicine-card', ['medicine' => $medicine, 'conflicts' => []])
                </div>
            @empty
                <div class="col-12 mb-4"><div class="card-soft text-center text-muted py-5"><i class="fas fa-box-open fa-2x d-block mb-2"></i>Tidak ada obat yang cocok. Coba pilih keluhan lain atau konsultasikan langsung dengan fisioterapis kami.</div></div>
            @endforelse
        </div>

        @if ($caution->isNotEmpty())
            <div class="mt-3 mb-3" data-reveal>
                <span class="eyebrow" style="background:#fff1f2;color:#be123c;border-color:#fecdd3"><i class="fas fa-triangle-exclamation"></i> Perlu konsultasi</span>
                <h2 class="section-title mb-0" style="font-size:1.6rem">Hindari atau tanyakan dokter dulu <small class="text-muted" style="font-size:1rem">({{ $caution->count() }})</small></h2>
                <p class="text-muted">Obat ini cocok untuk keluhan Anda, tetapi berisiko dengan kondisi yang Anda pilih.</p>
            </div>
            <div class="row">
                @foreach ($caution as $medicine)
                    <div class="col-md-6 col-lg-4 mb-4 d-flex" data-reveal style="--d: {{ ($loop->index % 3) * 0.08 }}s">
                        @include('partials.medicine-card', ['medicine' => $medicine, 'conflicts' => $medicine->conflictsWith($conditions)])
                    </div>
                @endforeach
            </div>
        @endif

        <div class="cta mt-4" data-reveal="zoom">
            <span class="orb sky" style="width:160px;height:160px;right:-40px;top:-50px;opacity:.35"></span>
            <div class="row align-items-center position-relative">
                <div class="col-lg-8">
                    <h3 class="text-white">Obat hanya meredakan gejala. Pemulihan butuh terapi.</h3>
                    <p class="mb-lg-0">Fisioterapis kami memeriksa penyebab keluhan, memberi latihan, dan menganjurkan obat yang sesuai kondisi Anda.</p>
                </div>
                <div class="col-lg-4 text-lg-right">
                    <a href="{{ auth()->check() ? route('booking.create') : route('register') }}" class="btn btn-white"><i class="fas fa-calendar-plus mr-2"></i>Booking Konsultasi</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
