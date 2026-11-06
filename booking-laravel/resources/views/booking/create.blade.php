@extends('layouts.app')

@section('title', 'Booking')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('dashboard').'">Beranda</a> / Booking',
    'title' => 'Booking Sesi Fisioterapi',
    'subtitle' => 'Pilih layanan, terapis, dan jam yang masih tersedia. Hanya butuh 1 menit.',
])

<div class="page-body">
    <div class="container">
        <form method="POST" action="{{ route('booking.store') }}" id="bookingForm" novalidate
              data-slots-url="{{ route('booking.slots') }}"
              data-selected-time="{{ old('waktu_booking') }}">
            @csrf
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card-soft pad-lg" data-reveal>
                        <div class="stepper" aria-hidden="true">
                            <div class="st" data-step="service"><b>1</b>Layanan</div>
                            <div class="st" data-step="therapist"><b>2</b>Terapis</div>
                            <div class="st" data-step="schedule"><b>3</b>Jadwal</div>
                            <div class="st" data-step="patient"><b>4</b>Data Pasien</div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong><i class="fas fa-circle-exclamation mr-1"></i>Booking belum bisa disimpan:</strong>
                                <ul class="mb-0 mt-1 pl-3">
                                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- 1. Layanan --}}
                        <fieldset class="form-block">
                            <legend class="form-block-title">
                                <span class="icon-chip"><i class="fas fa-stethoscope"></i></span><h5>Pilih layanan</h5>
                            </legend>
                            <div class="choice-grid">
                                @foreach ($services as $service)
                                    <label class="choice">
                                        <input type="radio" name="service_id" value="{{ $service->id }}" required
                                               data-name="{{ $service->name }}" data-price="{{ $service->formattedPrice() }}" data-duration="{{ $service->duration }}"
                                               @checked((string) old('service_id', $selectedService) === (string) $service->id)>
                                        <span class="choice-box">
                                            <span class="icon-chip sm"><i class="fas {{ $service->icon }}"></i></span>
                                            <span>
                                                <span class="t d-block">{{ $service->name }}</span>
                                                <span class="s">{{ $service->formattedPrice() }} · {{ $service->duration }}</span>
                                            </span>
                                        </span>
                                        <span class="choice-check"><i class="fas fa-check"></i></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- 2. Terapis --}}
                        <fieldset class="form-block">
                            <legend class="form-block-title">
                                <span class="icon-chip"><i class="fas fa-user-nurse"></i></span><h5>Pilih fisioterapis <small class="text-muted font-weight-normal">(opsional)</small></h5>
                            </legend>
                            <div class="choice-grid">
                                <label class="choice">
                                    <input type="radio" name="therapist_id" value="" data-name="Dipilihkan klinik" @checked(! old('therapist_id', $selectedTherapist))>
                                    <span class="choice-box">
                                        <span class="icon-chip sm"><i class="fas fa-wand-magic-sparkles"></i></span>
                                        <span><span class="t d-block">Pilihkan untuk saya</span><span class="s">Terapis yang tersedia</span></span>
                                    </span>
                                    <span class="choice-check"><i class="fas fa-check"></i></span>
                                </label>
                                @foreach ($therapists as $therapist)
                                    <label class="choice">
                                        <input type="radio" name="therapist_id" value="{{ $therapist->id }}" data-name="{{ $therapist->name }}"
                                               @checked((string) old('therapist_id', $selectedTherapist) === (string) $therapist->id)>
                                        <span class="choice-box">
                                            @if ($therapist->photoUrl())
                                                <img src="{{ $therapist->photoUrl() }}" alt="" class="avatar-3d sm">
                                            @else
                                                <span class="avatar-3d sm">{{ $therapist->initials() }}</span>
                                            @endif
                                            <span><span class="t d-block">{{ $therapist->name }}</span><span class="s">{{ $therapist->specialization }}</span></span>
                                        </span>
                                        <span class="choice-check"><i class="fas fa-check"></i></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- 3. Jadwal --}}
                        <fieldset class="form-block">
                            <legend class="form-block-title">
                                <span class="icon-chip"><i class="fas fa-calendar-days"></i></span><h5>Pilih tanggal & jam</h5>
                            </legend>
                            <div class="form-group" style="max-width:280px">
                                <label for="tanggal_booking">Tanggal</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="tanggal_booking" name="tanggal_booking" value="{{ old('tanggal_booking', today()->format('Y-m-d')) }}"
                                           min="{{ today()->format('Y-m-d') }}" max="{{ today()->addMonths(2)->format('Y-m-d') }}" class="form-control" required>
                                </div>
                            </div>
                            <label class="d-block mb-2">Jam tersedia</label>
                            <div id="slotGrid" class="slot-grid" role="radiogroup" aria-label="Jam tersedia">
                                <div class="slot-empty" style="grid-column:1/-1">Memuat jadwal...</div>
                            </div>
                            <small class="form-text text-muted mt-2"><i class="fas fa-circle-info mr-1"></i>Setiap slot berdurasi 1 jam. Slot bercoret sudah penuh, lewat, atau terapis sedang sibuk.</small>
                        </fieldset>

                        {{-- 4. Data pasien --}}
                        <fieldset class="form-block mb-0">
                            <legend class="form-block-title">
                                <span class="icon-chip"><i class="fas fa-id-card"></i></span><h5>Data pasien</h5>
                            </legend>
                            <div class="form-group">
                                <label for="nama">Nama lengkap pasien</label>
                                <div class="input-icon"><i class="fas fa-user"></i>
                                    <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama_lengkap) }}" class="form-control @error('nama') is-invalid @enderror" placeholder="Nama sesuai identitas" required maxlength="100">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email">Email</label>
                                    <div class="input-icon"><i class="fas fa-envelope"></i>
                                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="no_hp">Nomor HP / WhatsApp</label>
                                    <div class="input-icon"><i class="fas fa-phone"></i>
                                        <input type="tel" id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxxxxxxxx" pattern="08[0-9]{8,11}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="catatan">Keluhan / catatan <small class="text-muted font-weight-normal">(opsional)</small></label>
                                <textarea id="catatan" name="catatan" class="form-control" rows="3" maxlength="1000" placeholder="Contoh: nyeri lutut kanan sejak 2 minggu, minta home visit...">{{ old('catatan') }}</textarea>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="col-lg-4 mb-4">
                    <div class="card-soft pad-lg summary" data-reveal="right">
                        <h4 class="mb-3">Ringkasan</h4>
                        <div class="summary-row"><span>Layanan</span><span id="sumService">—</span></div>
                        <div class="summary-row"><span>Durasi</span><span id="sumDuration">—</span></div>
                        <div class="summary-row"><span>Terapis</span><span id="sumTherapist">Dipilihkan klinik</span></div>
                        <div class="summary-row"><span>Tanggal</span><span id="sumDate">—</span></div>
                        <div class="summary-row"><span>Jam</span><span id="sumTime">—</span></div>
                        <div class="summary-total">
                            <span class="text-muted font-weight-bold">Estimasi biaya</span>
                            <span class="price" id="sumPrice">Rp 0</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block mt-4"><i class="fas fa-circle-check mr-2"></i>Konfirmasi Booking</button>
                        <p class="small text-muted text-center mt-3 mb-0"><i class="fas fa-lock mr-1"></i>Bayar di klinik setelah sesi selesai.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/booking.js') }}?v=2"></script>
@endpush
