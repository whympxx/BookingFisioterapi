{{-- Baris ringkas obat yang dianjurkan. Parameter: $medicine, $aturan (opsional) --}}
@php($gol = $medicine->golonganInfo())
<div class="med-row">
    @if ($medicine->imageUrl())
        <img src="{{ $medicine->imageUrl() }}" alt="Kemasan {{ $medicine->name }}" class="med-thumb" loading="lazy">
    @else
        <span class="icon-chip sm"><i class="fas {{ $medicine->icon }}"></i></span>
    @endif
    <div class="flex-fill">
        <div class="cell-strong">{{ $medicine->name }} <span class="badge badge-{{ $gol['warna'] }} ml-1">{{ $gol['label'] }}</span></div>
        <div class="small text-muted">{{ $medicine->kategori }} · {{ $medicine->bentuk }}
            @if ($medicine->pivot?->harga)
                · <b class="text-dark">{{ $medicine->pivot->jumlah }} × {{ \App\Models\Booking::rupiah($medicine->pivot->harga) }}</b>{{ $medicine->satuan ? ' ('.$medicine->satuan.')' : '' }}
            @endif
        </div>
        <div class="small mt-1"><i class="fas fa-clock-rotate-left text-primary mr-1"></i><b>Aturan pakai:</b> {{ $aturan ?: $medicine->dosis }}</div>
        <div class="small text-danger mt-1"><i class="fas fa-triangle-exclamation mr-1"></i>{{ $medicine->peringatan }}</div>
    </div>
</div>
