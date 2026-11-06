{{-- Rincian tagihan: sesi terapi + obat yang dianjurkan terapis. Parameter: $booking --}}
@php($dibayar = $booking->totalDibayar())
@php($sisa = $booking->sisaTagihan())
<div class="bill">
    <div class="bill-row">
        <span><i class="fas fa-user-nurse text-primary mr-2"></i>Sesi {{ $booking->service->name }}</span>
        <span>{{ \App\Models\Booking::rupiah($booking->biayaLayanan()) }}</span>
    </div>
    @foreach ($booking->medicines as $m)
        <div class="bill-row">
            <span><i class="fas fa-pills text-primary mr-2"></i>{{ $m->name }} <small class="text-muted">× {{ $m->pivot->jumlah }}</small></span>
            <span>{{ \App\Models\Booking::rupiah($m->pivot->jumlah * $m->pivot->harga) }}</span>
        </div>
    @endforeach
    <div class="bill-row total"><span>Total tagihan</span><span>{{ $booking->formattedTotal() }}</span></div>
    @if ($dibayar > 0)
        <div class="bill-row paid"><span>Sudah dibayar</span><span>− {{ \App\Models\Booking::rupiah(min($dibayar, $booking->totalBayar())) }}</span></div>
        <div class="bill-row due {{ $sisa ? '' : 'lunas' }}"><span>{{ $sisa ? 'Sisa tagihan' : 'Lunas' }}</span><span>{{ \App\Models\Booking::rupiah($sisa) }}</span></div>
    @endif
</div>
