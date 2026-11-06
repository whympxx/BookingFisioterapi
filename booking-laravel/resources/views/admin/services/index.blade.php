@extends('layouts.app')

@section('title', 'Kelola Layanan')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Layanan',
    'title' => 'Kelola Layanan',
    'subtitle' => 'Ubah harga, durasi, dan deskripsi. Perubahan langsung tampil di website dan chatbot.',
])

<div class="page-body">
    <div class="container">
        <div class="card-soft" data-reveal>
            <div class="card-head">
                <h5>{{ $services->count() }} layanan</h5>
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Tambah Layanan</a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead><tr><th>Layanan</th><th>Harga</th><th>Durasi</th><th>Booking</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="icon-chip sm mr-3"><i class="fas {{ $service->icon }}"></i></span>
                                        <div>
                                            <div class="cell-strong">{{ $service->name }}</div>
                                            <small class="text-muted">
                                                @if ($service->is_featured) <span class="badge badge-soft">Unggulan</span> @endif
                                                @if ($service->is_popular) <span class="badge tone-coral">Terpopuler</span> @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-strong">{{ $service->formattedPrice() }}</td>
                                <td>{{ $service->duration }}</td>
                                <td>{{ $service->bookings_count }}</td>
                                <td><span class="badge badge-{{ $service->is_active ? 'success' : 'secondary' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="text-right text-nowrap">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-light-soft btn-sm"><i class="fas fa-pen"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $service->is_active ? 'btn-outline-danger' : 'btn-outline-primary' }}">{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
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
