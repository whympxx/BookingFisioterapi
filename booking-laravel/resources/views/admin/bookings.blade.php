@extends('layouts.app')

@section('title', 'Data Booking')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Booking',
    'title' => 'Data Booking Pasien',
    'subtitle' => 'Konfirmasi booking, tetapkan fisioterapis, dan perbarui status sesi.',
])

<div class="page-body">
    <div class="container-fluid px-lg-5">
        <div class="row">
            @foreach (\App\Models\Booking::STATUS_BADGES as $label => $tone)
                <div class="col-6 col-lg-3 mb-4" data-reveal style="--d: {{ $loop->index * 0.08 }}s">
                    <a href="{{ route('admin.bookings.index', ['status' => $label]) }}" class="stat-tile {{ $status === $label ? 'active' : '' }}">
                        <span class="icon-chip tone-{{ $tone }}"><i class="fas {{ \App\Models\Booking::STATUS_ICONS[$label] }}"></i></span>
                        <div><div class="val">{{ $counts[$label] ?? 0 }}</div><div class="lbl">{{ $label }}</div></div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card-soft" data-reveal>
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="form-row align-items-end mb-3">
                <div class="col-md-4 mb-2">
                    <label for="q" class="small">Cari</label>
                    <div class="input-icon"><i class="fas fa-magnifying-glass"></i>
                        <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Nama, email, HP, atau kode">
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label for="status" class="small">Status</label>
                    <select id="status" name="status" class="custom-select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Booking::STATUSES as $s)
                            <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <label for="tanggal" class="small">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary flex-fill mr-1"><i class="fas fa-filter"></i> Filter</button>
                    @if ($search || $status || $tanggal)
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-light-soft" title="Reset filter"><i class="fas fa-xmark"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Kode</th><th>Pasien</th><th>Jadwal</th><th>Layanan</th><th>Catatan</th><th style="min-width:340px">Terapis & Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td><span class="bk-code">{{ $booking->kode_booking }}</span></td>
                                <td>
                                    <div class="cell-strong">{{ $booking->nama }}</div>
                                    <small class="text-muted d-block">{{ $booking->no_hp }} · {{ $booking->email }}</small>
                                    <small class="text-muted">Akun: {{ $booking->user->username }}</small>
                                </td>
                                <td>
                                    <div class="cell-strong">{{ $booking->tanggal_booking->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">{{ $booking->jam() }} WIB</small>
                                </td>
                                <td>{{ $booking->service->name }}
                                    <div class="mt-1">
                                        <span class="badge badge-{{ $booking->paymentBadge() }}">{{ $booking->status_pembayaran }}</span>
                                        <small class="text-muted">{{ $booking->formattedTotal() }}</small>
                                        @if ($booking->medicines->isNotEmpty())
                                            <small class="d-block text-muted"><i class="fas fa-pills mr-1"></i>termasuk {{ $booking->medicines->count() }} obat ({{ \App\Models\Booking::rupiah($booking->biayaObat()) }})</small>
                                        @endif
                                    </div>
                                    @php($sisaBayar = $booking->isPaid() ? 0 : $booking->sisaTagihan())
                                    @if ($sisaBayar > 0 && $booking->status !== 'Dibatalkan')
                                        @if ($sisaBayar !== $booking->totalBayar())
                                            <small class="d-block text-danger font-weight-bold">Sisa {{ \App\Models\Booking::rupiah($sisaBayar) }}</small>
                                        @endif
                                        <form method="POST" action="{{ route('admin.bookings.cash', $booking) }}" class="mt-1" data-confirm="Catat pembayaran tunai {{ \App\Models\Booking::rupiah($sisaBayar) }} untuk {{ $booking->kode_booking }}?">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0"><i class="fas fa-money-bill-wave mr-1"></i>Tandai lunas (tunai)</button>
                                        </form>
                                    @endif
                                    @if ($booking->rating)
                                        <div class="stars small">{{ str_repeat('★', $booking->rating) }}</div>
                                    @endif
                                </td>
                                <td style="max-width:220px"><small>{{ $booking->catatan ?: '—' }}</small></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="d-flex align-items-center">
                                        @csrf @method('PATCH')
                                        <select name="therapist_id" class="custom-select custom-select-sm mr-2" aria-label="Terapis untuk {{ $booking->kode_booking }}">
                                            <option value="">— Terapis —</option>
                                            @foreach ($therapists as $t)
                                                <option value="{{ $t->id }}" @selected($booking->therapist_id === $t->id)>{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="status" class="custom-select custom-select-sm mr-2" aria-label="Status {{ $booking->kode_booking }}">
                                            @foreach (\App\Models\Booking::STATUSES as $s)
                                                <option value="{{ $s }}" @selected($booking->status === $s)>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" title="Simpan"><i class="fas fa-floppy-disk"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Tidak ada data booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">{{ $bookings->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
