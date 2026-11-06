@extends('layouts.app')

@section('title', 'Kelola Obat')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Obat',
    'title' => 'Kelola Obat & Suplemen',
    'subtitle' => 'Daftar ini tampil di halaman Obat dan bisa dianjurkan fisioterapis ke pasien (kecuali obat keras).',
])

<div class="page-body">
    <div class="container">
        <div class="card-soft" data-reveal>
            <div class="card-head">
                <h5>{{ $medicines->count() }} obat</h5>
                <div>
                    <a href="{{ route('medicines.index') }}" class="btn btn-light-soft btn-sm" target="_blank"><i class="fas fa-eye mr-1"></i>Lihat halaman</a>
                    <a href="{{ route('admin.medicines.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Tambah Obat</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>Obat</th><th>Harga</th><th>Golongan</th><th>Untuk keluhan</th><th>Dianjurkan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($medicines as $m)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($m->imageUrl())
                                            <img src="{{ $m->imageUrl() }}" alt="" class="med-thumb mr-3" loading="lazy">
                                        @else
                                            <span class="icon-chip sm mr-3"><i class="fas {{ $m->icon }}"></i></span>
                                        @endif
                                        <div><div class="cell-strong">{{ $m->name }}</div><small class="text-muted">{{ $m->kategori }} · {{ $m->bentuk }}</small></div>
                                    </div>
                                </td>
                                <td class="text-nowrap"><div class="cell-strong">Rp {{ number_format($m->harga, 0, ',', '.') }}</div><small class="text-muted">{{ $m->satuan }}</small></td>
                                <td><span class="badge badge-{{ $m->golonganInfo()['warna'] }}">{{ $m->golonganInfo()['label'] }}</span></td>
                                <td style="max-width:260px"><small>{{ implode(', ', $m->kebutuhanLabels()) }}</small></td>
                                <td>{{ $m->bookings_count }}×</td>
                                <td><span class="badge badge-{{ $m->is_active ? 'success' : 'secondary' }}">{{ $m->is_active ? 'Tampil' : 'Disembunyikan' }}</span></td>
                                <td class="text-right text-nowrap">
                                    <a href="{{ route('admin.medicines.edit', $m) }}" class="btn btn-light-soft btn-sm"><i class="fas fa-pen"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.medicines.toggle', $m) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $m->is_active ? 'btn-outline-danger' : 'btn-outline-primary' }}">{{ $m->is_active ? 'Sembunyikan' : 'Tampilkan' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
