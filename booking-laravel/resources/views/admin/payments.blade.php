@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Pembayaran',
    'title' => 'Pembayaran Pasien',
    'subtitle' => 'Pantau pembayaran online dan verifikasi bukti transfer manual.',
])

<div class="page-body">
    <div class="container-fluid px-lg-5">
        <div class="row">
            @foreach ([
                ['Total diterima', 'Rp '.number_format($summary['diterima'], 0, ',', '.'), 'fa-sack-dollar', 'success', 'paid'],
                ['Bulan ini', 'Rp '.number_format($summary['bulan_ini'], 0, ',', '.'), 'fa-calendar', 'info', 'paid'],
                ['Perlu verifikasi', $summary['verifikasi'], 'fa-file-circle-check', 'warning', 'verifikasi'],
                ['Menunggu dibayar', $summary['menunggu'], 'fa-hourglass-half', 'secondary', 'pending'],
            ] as $i => [$label, $val, $icon, $tone, $f])
                <div class="col-6 col-lg-3 mb-4" data-reveal style="--d: {{ $i * 0.08 }}s">
                    <a href="{{ route('admin.payments.index', ['filter' => $f]) }}" class="stat-tile {{ $filter === $f ? 'active' : '' }}">
                        <span class="icon-chip tone-{{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                        <div><div class="val" style="font-size:1.25rem">{{ $val }}</div><div class="lbl">{{ $label }}</div></div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card-soft" data-reveal>
            <form method="GET" class="form-row align-items-end mb-3">
                <div class="col-md-5 mb-2">
                    <label for="q" class="small">Cari</label>
                    <div class="input-icon"><i class="fas fa-magnifying-glass"></i>
                        <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="No. pesanan, kode booking, atau nama">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label for="filter" class="small">Status</label>
                    <select id="filter" name="filter" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (['verifikasi' => 'Perlu verifikasi', 'pending' => 'Menunggu dibayar', 'paid' => 'Berhasil', 'failed' => 'Gagal / dibatalkan', 'expired' => 'Kedaluwarsa'] as $k => $l)
                            <option value="{{ $k }}" @selected($filter === $k)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary flex-fill mr-1"><i class="fas fa-filter"></i> Filter</button>
                    @if ($filter || $search)
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-light-soft"><i class="fas fa-xmark"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>Waktu</th><th>Pesanan</th><th>Pasien</th><th>Metode</th><th>Nominal</th><th>Status</th><th class="text-right" style="min-width:260px">Aksi</th></tr></thead>
                    <tbody>
                        @forelse ($payments as $p)
                            <tr>
                                <td><div class="cell-strong">{{ $p->created_at->translatedFormat('d M Y') }}</div><small class="text-muted">{{ $p->created_at->format('H:i') }}</small></td>
                                <td><span class="bk-code">{{ $p->booking->kode_booking }}</span><small class="d-block text-muted">{{ $p->order_id }}</small></td>
                                <td><div class="cell-strong">{{ $p->booking->nama }}</div><small class="text-muted">{{ $p->booking->service->name }}</small></td>
                                <td><i class="fas {{ $p->methodInfo()['icon'] }} text-primary mr-1"></i>{{ $p->methodLabel() }}</td>
                                <td class="cell-strong">{{ $p->formattedAmount() }}</td>
                                <td>
                                    <span class="badge badge-{{ $p->statusTone() }}">{{ $p->statusLabel() }}</span>
                                    @if ($p->paid_at)<small class="d-block text-muted">{{ $p->paid_at->translatedFormat('d M H:i') }}</small>@endif
                                    @if ($p->admin_note)<small class="d-block text-danger">{{ $p->admin_note }}</small>@endif
                                </td>
                                <td class="text-right">
                                    @if ($p->proof)
                                        <a href="{{ $p->proofUrl() }}" target="_blank" rel="noopener" class="btn btn-light-soft btn-sm mb-1"><i class="fas fa-file-image"></i> Bukti</a>
                                    @endif
                                    @if ($p->status === 'pending' && $p->proof)
                                        <form method="POST" action="{{ route('admin.payments.verify', $p) }}" class="d-inline" data-confirm="Verifikasi pembayaran {{ $p->booking->kode_booking }} sebesar {{ $p->formattedAmount() }}?">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-success btn-sm mb-1"><i class="fas fa-check"></i> Verifikasi</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.payments.reject', $p) }}" class="d-inline-flex mt-1">
                                            @csrf @method('PATCH')
                                            <input type="text" name="admin_note" class="form-control form-control-sm mr-1" placeholder="Alasan tolak" maxlength="255" style="max-width:150px">
                                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-xmark"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-receipt fa-2x d-block mb-2"></i>Belum ada pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">{{ $payments->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
</div>
@endsection
