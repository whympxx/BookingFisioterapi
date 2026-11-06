<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Medicine;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'services' => Service::active()->orderBy('id')->get(),
            'therapists' => Therapist::active()->orderBy('id')->take(4)->get(),
            'reviews' => Booking::with(['service', 'user'])
                ->whereNotNull('rating')
                ->where('rating', '>=', 4)
                ->whereNotNull('ulasan')
                ->latest('rated_at')
                ->take(6)
                ->get(),
            'numbers' => [
                'pasien' => User::where('role', 'user')->count(),
                'sesi' => Booking::where('status', 'Selesai')->count(),
                'terapis' => Therapist::active()->count(),
                'rating' => round((float) Booking::whereNotNull('rating')->avg('rating'), 1),
            ],
        ]);
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $stats = $user->bookings()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $upcoming = $user->bookings()
            ->with(['service', 'therapist'])
            ->whereIn('status', ['Menunggu', 'Dikonfirmasi'])
            ->whereDate('tanggal_booking', '>=', today())
            ->orderBy('tanggal_booking')
            ->orderBy('waktu_booking')
            ->take(3)
            ->get();

        $toReview = $user->bookings()->with('service')
            ->where('status', 'Selesai')->whereNull('rating')->latest()->first();

        return view('dashboard', [
            'services' => Service::active()->where('is_featured', true)->orderBy('id')->get(),
            'stats' => $stats,
            'upcoming' => $upcoming,
            'toReview' => $toReview,
        ]);
    }

    public function layanan(): View
    {
        return view('layanan', ['services' => Service::active()->orderBy('id')->get()]);
    }

    public function therapists(): View
    {
        return view('therapists.index', [
            'therapists' => Therapist::active()
                ->withCount(['bookings as sesi_count' => fn ($q) => $q->where('status', 'Selesai')])
                ->withAvg('bookings as rating_avg', 'rating')
                ->orderBy('name')->get(),
        ]);
    }

    public function therapist(Therapist $therapist): View
    {
        abort_unless($therapist->is_active, 404);

        return view('therapists.show', [
            'therapist' => $therapist,
            'rating' => $therapist->averageRating(),
            'sesi' => $therapist->bookings()->where('status', 'Selesai')->count(),
            'reviews' => $therapist->bookings()->with(['service', 'user'])
                ->whereNotNull('rating')->latest('rated_at')->take(6)->get(),
            'ratingCount' => $therapist->bookings()->whereNotNull('rating')->count(),
        ]);
    }

    public function medicines(Request $request): View
    {
        $allNeeds = config('obat.kebutuhan');
        $allConditions = config('obat.kondisi');

        $needs = array_values(array_intersect((array) $request->query('kebutuhan', []), array_keys($allNeeds)));
        $layanan = $request->query('layanan');
        if ($layanan && ! $needs) {
            $needs = config('obat.kebutuhan_layanan.'.$layanan, []);
        }
        $conditions = array_values(array_intersect((array) $request->query('kondisi', []), array_keys($allConditions)));
        $search = trim((string) $request->query('q'));

        $medicines = Medicine::active()->orderByRaw("case when golongan = 'keras' then 1 else 0 end")->orderBy('name')->get()
            ->filter(fn ($m) => ! $needs || array_intersect($needs, $m->kebutuhan ?? []))
            ->filter(fn ($m) => $search === '' || str_contains(mb_strtolower($m->name.' '.$m->kategori.' '.$m->kegunaan), mb_strtolower($search)));

        [$caution, $suitable] = $medicines->partition(fn ($m) => (bool) $m->conflictsWith($conditions));

        // Anjuran dari fisioterapis untuk pasien yang sedang login.
        $user = $request->user();
        $fromTherapist = $user && $user->role === 'user'
            ? Booking::with(['medicines', 'therapist', 'service'])->where('user_id', $user->id)
                ->whereHas('medicines')->latest('tanggal_booking')->take(3)->get()
            : collect();

        return view('medicines.index', [
            'suitable' => $suitable->values(),
            'caution' => $caution->values(),
            'needs' => $needs,
            'conditions' => $conditions,
            'search' => $search,
            'allNeeds' => $allNeeds,
            'allConditions' => $allConditions,
            'filtered' => (bool) ($needs || $conditions || $search !== ''),
            'fromTherapist' => $fromTherapist,
        ]);
    }
}
