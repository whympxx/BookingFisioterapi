@extends('layouts.app')

@php($editing = $medicine->exists)
@php($needs = old('kebutuhan', $medicine->kebutuhan ?? []))
@php($avoid = old('hindari_jika', $medicine->hindari_jika ?? []))

@section('title', $editing ? 'Edit Obat' : 'Tambah Obat')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.medicines.index').'">Obat</a> / '.($editing ? 'Edit' : 'Baru'),
    'title' => $editing ? 'Edit '.$medicine->name : 'Tambah Obat / Suplemen',
])

<div class="page-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card-soft pad-lg" data-reveal>
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <form method="POST" action="{{ $editing ? route('admin.medicines.update', $medicine) : route('admin.medicines.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if ($editing) @method('PUT') @endif
                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label for="name">Nama obat & kekuatan</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $medicine->name) }}" class="form-control" required maxlength="120" placeholder="mis. Paracetamol 500 mg">
                            </div>
                            <div class="form-group col-md-5">
                                <label for="golongan">Golongan</label>
                                <select id="golongan" name="golongan" class="custom-select" required>
                                    @foreach (config('obat.golongan') as $k => $g)
                                        <option value="{{ $k }}" @selected(old('golongan', $medicine->golongan) === $k)>{{ $g['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="kategori">Kategori</label>
                                <input type="text" id="kategori" name="kategori" value="{{ old('kategori', $medicine->kategori) }}" class="form-control" required maxlength="80" placeholder="mis. Pereda nyeri">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="bentuk">Bentuk</label>
                                <input type="text" id="bentuk" name="bentuk" value="{{ old('bentuk', $medicine->bentuk) }}" class="form-control" required maxlength="60" placeholder="Tablet / gel / kapsul">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="icon">Ikon</label>
                                <select id="icon" name="icon" class="custom-select" required>
                                    @foreach (config('obat.icons') as $k => $l)
                                        <option value="{{ $k }}" @selected(old('icon', $medicine->icon) === $k)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="harga">Harga (Rp)</label>
                                <input type="number" id="harga" name="harga" value="{{ old('harga', $medicine->harga) }}" class="form-control" required min="0" step="500" placeholder="5000">
                                <small class="text-muted">Otomatis ditagihkan ke pasien saat dianjurkan terapis.</small>
                            </div>
                            <div class="form-group col-md-7">
                                <label for="satuan">Per satuan</label>
                                <input type="text" id="satuan" name="satuan" value="{{ old('satuan', $medicine->satuan) }}" class="form-control" required maxlength="60" placeholder="mis. strip 10 tablet / tube 20 g">
                            </div>
                            <div class="form-group col-12">
                                <label for="image">Foto kemasan <small class="text-muted font-weight-normal">(opsional, JPG/PNG/WEBP maks 2MB; tanpa foto akan memakai ikon)</small></label>
                                <div class="d-flex align-items-center">
                                    <img id="imagePreview" src="{{ $medicine->imageUrl() }}" alt="" class="med-thumb lg mr-3 {{ $medicine->imageUrl() ? '' : 'd-none' }}">
                                    <input type="file" id="image" name="image" class="form-control-file" accept="image/jpeg,image/png,image/webp" data-preview-target="imagePreview">
                                </div>
                            </div>
                            <div class="form-group col-12">
                                <label for="kegunaan">Kegunaan</label>
                                <textarea id="kegunaan" name="kegunaan" class="form-control" rows="2" required maxlength="1000">{{ old('kegunaan', $medicine->kegunaan) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="dosis">Dosis umum</label>
                                <textarea id="dosis" name="dosis" class="form-control" rows="3" required maxlength="1000">{{ old('dosis', $medicine->dosis) }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="peringatan">Peringatan</label>
                                <textarea id="peringatan" name="peringatan" class="form-control" rows="3" required maxlength="1000">{{ old('peringatan', $medicine->peringatan) }}</textarea>
                            </div>
                        </div>

                        <label class="d-block">Cocok untuk keluhan</label>
                        <div class="chip-grid mb-4">
                            @foreach (config('obat.kebutuhan') as $k => $n)
                                <label class="chip-check">
                                    <input type="checkbox" name="kebutuhan[]" value="{{ $k }}" @checked(in_array($k, $needs, true))>
                                    <span><i class="fas {{ $n['icon'] }}"></i>{{ $n['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <label class="d-block">Hindari / konsultasi dulu jika pasien</label>
                        <div class="chip-grid mb-4">
                            @foreach (config('obat.kondisi') as $k => $l)
                                <label class="chip-check warn">
                                    <input type="checkbox" name="hindari_jika[]" value="{{ $k }}" @checked(in_array($k, $avoid, true))>
                                    <span><i class="fas fa-heart-circle-exclamation"></i>{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="custom-control custom-switch mb-4">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $medicine->is_active))>
                            <label class="custom-control-label font-weight-normal" for="is_active">Tampilkan di halaman Obat</label>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.medicines.index') }}" class="btn btn-light-soft">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk mr-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
