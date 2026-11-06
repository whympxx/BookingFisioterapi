@extends('layouts.app')

@section('title', 'Dasbor Admin')

@section('content')
@include('partials.page-hero', [
    'eyebrow' => now()->translatedFormat('l, d F Y'),
    'eyebrowIcon' => 'fa-calendar-day',
    'title' => 'Dasbor Admin',
    'subtitle' => 'Ringkasan booking, pendapatan, dan jadwal klinik hari ini.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            @foreach ([
                ['Total booking', $total, 'fa-calendar-check', '', route('admin.bookings.index')],
                ['Menunggu konfirmasi', $counts['Menunggu'] ?? 0, 'fa-hourglass-half', 'tone-warning', route('admin.bookings.index', ['status' => 'Menunggu'])],
                ['Pasien terdaftar', $pasien, 'fa-users', 'tone-info', null],
                ['Pendapatan (selesai)', null, 'fa-wallet', 'tone-success', null],
            ] as $i => [$label, $value, $icon, $tone, $url])
                <div class="col-sm-6 col-lg-3 mb-4" data-reveal style="--d: {{ $i * 0.08 }}s">
                    <a @if ($url) href="{{ $url }}" @endif class="stat-tile text-reset" data-tilt="8">
                        <span class="icon-chip {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                        <div>
                            @if ($value === null)
                                <div class="val" style="font-size:1.3rem">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                            @else
                                <div class="val" data-count="{{ $value }}">0</div>
                            @endif
                            <div class="lbl">{{ $label }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4" data-reveal="left">
                <div class="card-soft h-100">
                    <div class="card-head">
                        <h5><i class="fas fa-chart-line text-primary mr-2"></i>Booking 14 hari terakhir</h5>
                        <span class="badge badge-soft">Rating rata-rata: {{ $rating > 0 ? number_format($rating, 1).' ★' : '—' }}</span>
                    </div>
                    <div style="height:280px"><canvas id="trendChart" aria-label="Grafik jumlah booking 14 hari terakhir" role="img"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4" data-reveal="right">
                <div class="card-soft h-100">
                    <div class="card-head"><h5><i class="fas fa-chart-pie text-primary mr-2"></i>Per layanan</h5></div>
                    <div style="height:280px"><canvas id="serviceChart" aria-label="Grafik booking per layanan" role="img"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4" data-reveal="left">
                <div class="card-soft h-100">
                    <div class="card-head">
                        <h5><i class="fas fa-calendar-day text-primary mr-2"></i>Jadwal hari ini</h5>
                        <a href="{{ route('admin.bookings.index', ['tanggal' => today()->format('Y-m-d')]) }}" class="btn btn-light-soft btn-sm">Detail</a>
                    </div>
                    @forelse ($today as $b)
                        <div class="appt">
                            <div class="appt-date"><div class="d" style="font-size:1.05rem">{{ $b->jam() }}</div><div class="m">WIB</div></div>
                            <div class="appt-info">
                                <h6>{{ $b->nama }}</h6>
                                <small>{{ $b->service->name }} · {{ $b->therapist?->name ?? 'Belum ada terapis' }}</small>
                            </div>
                            <span class="badge badge-{{ $b->badgeClass() }}">{{ $b->status }}</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0"><i class="fas fa-mug-hot mr-1"></i>Tidak ada jadwal hari ini.</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6 mb-4" data-reveal="right">
                <div class="card-soft h-100">
                    <div class="card-head">
                        <h5><i class="fas fa-hourglass-half text-primary mr-2"></i>Perlu konfirmasi</h5>
                        <a href="{{ route('admin.bookings.index', ['status' => 'Menunggu']) }}" class="btn btn-light-soft btn-sm">Semua</a>
                    </div>
                    @forelse ($pending as $b)
                        <div class="appt">
                            <div class="appt-date">
                                <div class="d">{{ $b->tanggal_booking->format('d') }}</div>
                                <div class="m">{{ $b->tanggal_booking->translatedFormat('M') }}</div>
                            </div>
                            <div class="appt-info">
                                <h6>{{ $b->nama }}</h6>
                                <small>{{ $b->service->name }} · {{ $b->jam() }} WIB</small>
                            </div>
                            <form method="POST" action="{{ route('admin.bookings.update', $b) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="Dikonfirmasi">
                                <input type="hidden" name="therapist_id" value="{{ $b->therapist_id }}">
                                <button type="submit" class="btn btn-primary btn-sm" title="Konfirmasi"><i class="fas fa-check"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0"><i class="fas fa-circle-check mr-1"></i>Semua booking sudah diproses.</p>
                    @endforelse
                </div>
            </div>
        </div>

        @if ($latestReviews->isNotEmpty())
            <div class="card-soft mb-4" data-reveal>
                <div class="card-head"><h5><i class="fas fa-star text-primary mr-2"></i>Ulasan terbaru</h5></div>
                <div class="row">
                    @foreach ($latestReviews as $r)
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="stars small">@for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $r->rating ? 'fas' : 'far' }} fa-star"></i>@endfor</div>
                            <div class="font-weight-bold text-dark">{{ $r->nama }}</div>
                            <small class="text-muted d-block mb-1">{{ $r->service->name }}</small>
                            <div class="small">{{ \Illuminate\Support\Str::limit($r->ulasan ?: 'Tanpa komentar.', 90) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
    (function () {
        if (!window.Chart) return;
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = '#64748b';

        const trend = @json($trend);
        const ctx = document.getElementById('trendChart').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, 'rgba(13,148,136,.35)');
        grad.addColorStop(1, 'rgba(13,148,136,0)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trend.map(t => t.label),
                datasets: [{
                    label: 'Booking', data: trend.map(t => t.total), fill: true, backgroundColor: grad,
                    borderColor: '#0d9488', borderWidth: 3, tension: .4, pointRadius: 4, pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d9488', pointBorderWidth: 2,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } },
                },
            },
        });

        const svc = @json($perService->map(fn ($s) => ['name' => $s->name, 'total' => $s->bookings_count]));
        new Chart(document.getElementById('serviceChart'), {
            type: 'doughnut',
            data: {
                labels: svc.map(s => s.name),
                datasets: [{
                    data: svc.map(s => s.total),
                    backgroundColor: ['#0d9488', '#0ea5e9', '#f59e0b', '#8b5cf6', '#f43f5e', '#22c55e', '#64748b'],
                    borderWidth: 0, hoverOffset: 10,
                }],
            },
            options: {
                maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } } },
            },
        });
    })();
</script>
@endpush
