{{-- Kartu obat di katalog. Parameter: $medicine, $conflicts (array kondisi yang bertabrakan) --}}
@php($gol = $medicine->golonganInfo())
@php($conflicts = $conflicts ?? [])
<article class="card-soft med-card h-100 {{ $conflicts ? 'is-caution' : '' }} {{ $medicine->needsPrescription() ? 'is-rx' : '' }}" data-tilt="4">
    @if ($medicine->imageUrl())
        <div class="med-photo">
            <img src="{{ $medicine->imageUrl() }}" alt="Kemasan {{ $medicine->name }}" loading="lazy">
            @if ($medicine->needsPrescription())<span class="med-rx"><i class="fas fa-prescription"></i> Resep</span>@endif
        </div>
    @endif
    <div class="d-flex align-items-start mb-3">
        @unless ($medicine->imageUrl())
            <span class="med-icon mr-3"><i class="fas {{ $medicine->icon }}"></i></span>
        @endunless
        <div class="min-w-0">
            <h5 class="mb-1">{{ $medicine->name }}</h5>
            <div class="small text-muted">{{ $medicine->kategori }} · {{ $medicine->bentuk }}</div>
        </div>
    </div>
    <div class="mb-2">
        <span class="badge badge-{{ $gol['warna'] }}" title="{{ $gol['info'] }}">
            @if ($medicine->needsPrescription())<i class="fas fa-prescription mr-1"></i>@endif{{ $gol['label'] }}
        </span>
    </div>
    @if ($medicine->harga)
        <div class="med-price mb-2">{{ \App\Models\Booking::rupiah($medicine->harga) }} <small>/ {{ $medicine->satuan }}</small></div>
    @endif
    <p class="small mb-3">{{ $medicine->kegunaan }}</p>
    <div class="med-tags mb-3">
        @foreach ($medicine->kebutuhanLabels() as $label)
            <span>{{ $label }}</span>
        @endforeach
    </div>

    @if ($conflicts)
        <div class="med-alert mb-3">
            <i class="fas fa-triangle-exclamation mr-1"></i>
            Konsultasikan dulu, karena Anda memilih: {{ implode(', ', array_map(fn ($k) => strtolower(config('obat.kondisi.'.$k)), $conflicts)) }}.
        </div>
    @endif

    <div class="mt-auto">
        <a class="med-toggle" data-toggle="collapse" href="#med-{{ $medicine->id }}" role="button" aria-expanded="false" aria-controls="med-{{ $medicine->id }}">
            Dosis & peringatan <i class="fas fa-chevron-down ml-1"></i>
        </a>
        <div class="collapse" id="med-{{ $medicine->id }}">
            <div class="med-detail">
                <div class="k"><i class="fas fa-clock-rotate-left mr-1"></i>Dosis umum</div>
                <p>{{ $medicine->dosis }}</p>
                <div class="k text-danger"><i class="fas fa-triangle-exclamation mr-1"></i>Peringatan</div>
                <p class="mb-0">{{ $medicine->peringatan }}</p>
            </div>
        </div>
    </div>
</article>
