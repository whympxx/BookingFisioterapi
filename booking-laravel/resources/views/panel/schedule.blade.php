@extends('layouts.app')

@section('title', 'Jadwal Pasien')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('panel.dashboard').'">Panel Terapis</a> / Jadwal',
    'title' => 'Jadwal Pasien Saya',
    'subtitle' => 'Semua pasien yang ditetapkan ke Anda. Klik pasien untuk menulis catatan terapi dan anjuran obat.',
])

<div class="page-body">
    <div class="container">
        <div class="card-soft" data-reveal>
            <form method="GET" class="form-row align-items-end mb-3">
                <div class="col-6 col-md-4 mb-2">
                    <label for="status" class="small">Status</label>
                    <select id="status" name="status" class="custom-select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Booking::STATUSES as $s)
                            <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 mb-2">
                    <label for="tanggal" class="small">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-4 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary flex-fill mr-1"><i class="fas fa-filter"></i> Filter</button>
                    @if ($status || $tanggal)
                        <a href="{{ route('panel.schedule') }}" class="btn btn-light-soft"><i class="fas fa-xmark"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>Jadwal</th><th>Pasien</th><th>Layanan</th><th>Status</th><th>Catatan</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            <tr>
                                <td><div class="cell-strong">{{ $b->tanggal_booking->translatedFormat('d M Y') }}</div><small class="text-muted">{{ $b->jam() }} WIB</small></td>
                                <td><div class="cell-strong">{{ $b->nama }}</div><small class="text-muted">{{ $b->no_hp }}</small></td>
                                <td>{{ $b->service->name }}</td>
                                <td><span class="badge badge-{{ $b->badgeClass() }}">{{ $b->status }}</span></td>
                                <td>@if ($b->catatan_terapis)<span class="badge badge-soft"><i class="fas fa-check mr-1"></i>Ada</span>@else<span class="text-muted small">—</span>@endif</td>
                                <td class="text-right"><a href="{{ route('panel.patient', $b) }}" class="btn btn-light-soft btn-sm">Buka <i class="fas fa-arrow-right ml-1"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada pasien.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">{{ $bookings->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
