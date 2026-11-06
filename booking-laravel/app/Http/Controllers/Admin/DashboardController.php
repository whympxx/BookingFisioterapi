<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = Booking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $revenue = Booking::where('bookings.status', 'Selesai')
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->sum('services.price');

        // Grafik 14 hari terakhir (berdasarkan tanggal booking dibuat).
        $from = today()->subDays(13);
        $perDay = Booking::where('created_at', '>=', $from)->get(['created_at'])
            ->groupBy(fn ($b) => $b->created_at->format('Y-m-d'))
            ->map->count();
        $trend = collect(range(0, 13))->map(function ($i) use ($from, $perDay) {
            $day = $from->copy()->addDays($i);

            return ['label' => $day->translatedFormat('d M'), 'total' => $perDay[$day->format('Y-m-d')] ?? 0];
        });

        $perService = Service::withCount(['bookings' => fn ($q) => $q->where('status', '!=', 'Dibatalkan')])
            ->orderByDesc('bookings_count')->get(['id', 'name']);

        return view('admin.dashboard', [
            'counts' => $counts,
            'total' => $counts->sum(),
            'revenue' => $revenue,
            'pasien' => User::where('role', 'user')->count(),
            'rating' => round((float) Booking::whereNotNull('rating')->avg('rating'), 1),
            'trend' => $trend,
            'perService' => $perService,
            'today' => Booking::with(['service', 'therapist'])
                ->whereDate('tanggal_booking', today())
                ->where('status', '!=', 'Dibatalkan')
                ->orderBy('waktu_booking')->get(),
            'pending' => Booking::with('service')->where('status', 'Menunggu')
                ->orderBy('tanggal_booking')->orderBy('waktu_booking')->take(6)->get(),
            'therapists' => Therapist::active()->orderBy('name')->get(),
            'latestReviews' => Booking::with('service')->whereNotNull('rating')->latest('rated_at')->take(4)->get(),
        ]);
    }
}
