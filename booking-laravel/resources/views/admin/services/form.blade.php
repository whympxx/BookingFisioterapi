@extends('layouts.app')

@php($editing = $service->exists)

@section('title', $editing ? 'Edit Layanan' : 'Tambah Layanan')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.services.index').'">Layanan</a> / '.($editing ? 'Edit' : 'Baru'),
    'title' => $editing ? 'Edit '.$service->name : 'Tambah Layanan',
])

<div class="page-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card-soft pad-lg" data-reveal>
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form method="POST" action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if ($editing) @method('PUT') @endif
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="name">Nama layanan</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $service->name) }}" class="form-control" required maxlength="100">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="icon">Ikon</label>
                                <select id="icon" name="icon" class="custom-select" required>
                                    @foreach ($icons as $value => $label)
                                        <option value="{{ $value }}" @selected(old('icon', $service->icon) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="price">Harga per sesi (Rp)</label>
                                <input type="number" id="price" name="price" value="{{ old('price', $service->price) }}" class="form-control" required min="0" step="1000">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="duration">Durasi</label>
                                <input type="text" id="duration" name="duration" value="{{ old('duration', $service->duration) }}" class="form-control" required placeholder="contoh: 60-90 menit">
                            </div>
                            <div class="form-group col-12">
                                <label for="description">Deskripsi singkat</label>
                                <textarea id="description" name="description" class="form-control" rows="2" required maxlength="500">{{ old('description', $service->description) }}</textarea>
                            </div>
                            <div class="form-group col-md-7">
                                <label for="features">Poin layanan <small class="text-muted font-weight-normal">(satu per baris)</small></label>
                                <textarea id="features" name="features" class="form-control" rows="5" required>{{ old('features', implode("\n", $service->features ?? [])) }}</textarea>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="image">Gambar <small class="text-muted font-weight-normal">(opsional, maks 2MB)</small></label>
                                @if ($editing)
                                    <img src="{{ $service->imageUrl() }}" alt="" class="d-block mb-2" style="width:100%;height:120px;object-fit:cover;border-radius:14px">
                                @endif
                                <input type="file" id="image" name="image" class="form-control-file" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap mb-3">
                            @foreach (['is_active' => 'Aktif (tampil di website)', 'is_featured' => 'Tampilkan di dashboard pasien', 'is_popular' => 'Label "Terpopuler"'] as $field => $label)
                                <div class="custom-control custom-switch mr-4 mb-2">
                                    <input type="checkbox" class="custom-control-input" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field, $service->$field))>
                                    <label class="custom-control-label font-weight-normal" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.services.index') }}" class="btn btn-light-soft">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk mr-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
