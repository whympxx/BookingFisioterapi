@extends('layouts.app')

@section('title', 'Riwayat Booking')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('dashboard').'">Beranda</a> / Riwayat',
    'title' => 'Riwayat Booking',
    'subtitle' => 'Pantau status, buka tiket, batalkan, atau beri ulasan untuk sesi Anda.',
])

<div class="page-body">
    <div class="container">
        <div class="card-soft mb-4 d-flex flex-wrap justify-content-between align-items-center" data-reveal>
            <div class="pills mb-2 mb-md-0">
                <a href="{{ route('booking.index') }}" class="pill {{ ! $status ? 'active' : '' }}">Semua</a>
                @foreach (\App\Models\Booking::STATUSES as $s)
                    <a href="{{ route('booking.index', ['status' => $s]) }}" class="pill {{ $status === $s ? 'active' : '' }}">{{ $s }}</a>
                @endforeach
            </div>
            <a href="{{ route('booking.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Booking Baru</a>
        </div>

        <div class="row">
            @forelse ($bookings as $booking)
                <div class="col-md-6 col-lg-4 mb-4" data-reveal style="--d: {{ ($loop->index % 3) * 0.08 }}s">
                    <div class="card-soft bk-card" data-tilt="5">
                        <div class="bk-top">
                            <div>
                                <div class="bk-code">{{ $booking->kode_booking }}</div>
                                <h5 class="mb-0 mt-1">{{ $booking->service->name }}</h5>
                            </div>
                            <span class="badge badge-{{ $booking->badgeClass() }}"><i class="fas {{ $booking->statusIcon() }} mr-1"></i>{{ $booking->status }}</span>
                        </div>
                        <div class="bk-body">
                            <div class="bk-meta">
                                <div><i class="fas fa-calendar"></i>{{ $booking->tanggal_booking->translatedFormat('l, d M Y') }}</div>
                                <div><i class="far fa-clock"></i>{{ $booking->jam() }} WIB</div>
                                <div><i class="fas fa-user"></i>{{ $booking->nama }}</div>
                                <div><i class="fas fa-user-nurse"></i>{{ $booking->therapist?->name ?? 'Ditentukan klinik' }}</div>
                                <div><i class="fas fa-wallet"></i>{{ $booking->formattedTotal() }} <span class="badge badge-{{ $booking->paymentBadge() }} ml-1">{{ $booking->status_pembayaran }}</span></div>
                                @if ($booking->rating)
                                    <div><i class="fas fa-star" style="color:var(--amber)"></i>Rating {{ $booking->rating }}/5</div>
                                @endif
                            </div>
                        </div>
                        <div class="bk-foot">
                            <a href="{{ route('booking.show', $booking) }}" class="btn btn-light-soft btn-sm"><i class="fas fa-ticket mr-1"></i>Tiket</a>
                            @if ($booking->canBePaid() && $booking->status_pembayaran === 'Belum Bayar')
                                <a href="{{ route('payment.show', $booking) }}" class="btn btn-success btn-sm"><i class="fas fa-credit-card mr-1"></i>Bayar</a>
                            @endif
                            @if ($booking->canBeRated())
                                <a href="{{ route('booking.show', $booking) }}#ulasan" class="btn btn-primary btn-sm"><i class="fas fa-star mr-1"></i>Beri Ulasan</a>
                            @endif
                            @if ($booking->canBeCancelled())
                                <form method="POST" action="{{ route('booking.cancel', $booking) }}" data-confirm="Batalkan booking ini?">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card-soft pad-lg text-center" data-reveal>
                        <div class="icon-chip solid mx-auto mb-3" style="width:80px;height:80px;font-size:2rem;border-radius:24px"><i class="fas fa-calendar-plus"></i></div>
                        <h4>{{ $status ? "Tidak ada booking berstatus $status" : 'Belum ada riwayat booking' }}</h4>
                        <p class="text-muted">Jadwalkan sesi fisioterapi pertama Anda sekarang.</p>
                        <a href="{{ route('booking.create') }}" class="btn btn-primary">Buat Booking</a>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="d-flex justify-content-center">{{ $bookings->links('pagination::bootstrap-4') }}</div>
    </div>
</div>
@endsection
