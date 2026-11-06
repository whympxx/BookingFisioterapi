@extends('layouts.app')

@section('title', 'Layanan')

@section('content')
@include('partials.page-hero', [
    'eyebrow' => 'Layanan Fisioterapi',
    'eyebrowIcon' => 'fa-stethoscope',
    'title' => 'Pilih terapi yang sesuai kebutuhan Anda',
    'subtitle' => 'Harga per sesi sudah termasuk konsultasi. Home visit +Rp '.number_format(config('klinik.biaya_home_visit'), 0, ',', '.').'.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            @foreach ($services as $service)
                <div class="col-lg-4 col-md-6 mb-4" data-reveal style="--d: {{ ($loop->index % 3) * 0.1 }}s">
                    @include('partials.service-card')
                </div>
            @endforeach
        </div>

        <div class="card-soft pad-lg mt-4" data-reveal>
            <div class="row align-items-center">
                <div class="col-md-8 d-flex align-items-center mb-3 mb-md-0">
                    <span class="icon-chip solid mr-3"><i class="fas fa-circle-question"></i></span>
                    <div>
                        <h4 class="mb-1">Bingung memilih layanan?</h4>
                        <p class="text-muted mb-0">Tanyakan ke asisten chat atau pilih "Rehabilitasi Pasca Cedera" — terapis akan mengevaluasi kondisi Anda di sesi pertama.</p>
                    </div>
                </div>
                <div class="col-md-4 text-md-right">
                    <a href="tel:{{ config('klinik.telepon_link') }}" class="btn btn-outline-primary"><i class="fas fa-phone mr-2"></i>Hubungi Kami</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
