<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Medicine;
use App\Models\Therapist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Halaman khusus fisioterapis: jadwal, data pasien, catatan terapi, dan anjuran obat. */
class TherapistPanelController extends Controller
{
    public function dashboard(Request $request): View
    {
        $therapist = $this->therapist($request);
        $active = fn ($q) => $q->whereIn('status', ['Menunggu', 'Dikonfirmasi']);

        return view('panel.dashboard', [
            'therapist' => $therapist,
            'today' => $therapist->bookings()->with(['service', 'user'])
                ->whereDate('tanggal_booking', today())->where('status', '!=', 'Dibatalkan')
                ->orderBy('waktu_booking')->get(),
            'upcoming' => $therapist->bookings()->with('service')->where($active)
                ->whereDate('tanggal_booking', '>', today())
                ->orderBy('tanggal_booking')->orderBy('waktu_booking')->take(6)->get(),
            'stats' => [
                'hari_ini' => $therapist->bookings()->whereDate('tanggal_booking', today())->where('status', '!=', 'Dibatalkan')->count(),
                'minggu_ini' => $therapist->bookings()->where('status', '!=', 'Dibatalkan')
                    ->whereBetween('tanggal_booking', [today()->startOfWeek()->toDateString(), today()->endOfWeek()->toDateString()])->count(),
                'selesai' => $therapist->bookings()->where('status', 'Selesai')->count(),
                'pasien' => $therapist->bookings()->where('status', '!=', 'Dibatalkan')->distinct()->count('user_id'),
            ],
            'rating' => $therapist->averageRating(),
            'reviews' => $therapist->bookings()->with('service')->whereNotNull('rating')->latest('rated_at')->take(4)->get(),
        ]);
    }

    public function schedule(Request $request): View
    {
        $therapist = $this->therapist($request);
        $status = $request->query('status');
        $tanggal = $request->query('tanggal');

        $bookings = $therapist->bookings()->with(['service', 'user'])
            ->when(in_array($status, Booking::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($tanggal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal), fn ($q) => $q->whereDate('tanggal_booking', $tanggal))
            ->orderByDesc('tanggal_booking')->orderBy('waktu_booking')
            ->paginate(12)->withQueryString();

        return view('panel.schedule', compact('therapist', 'bookings', 'status', 'tanggal'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $therapist = $this->therapist($request);
        abort_unless($booking->therapist_id === $therapist->id, 403);
        $booking->load(['service', 'user', 'medicines', 'latestPayment']);

        return view('panel.patient', [
            'therapist' => $therapist,
            'booking' => $booking,
            // Riwayat sesi pasien yang sama di klinik ini (dengan terapis mana pun).
            'history' => Booking::with(['service', 'therapist', 'medicines'])
                ->where('user_id', $booking->user_id)->where('id', '!=', $booking->id)
                ->where('status', '!=', 'Dibatalkan')
                ->orderByDesc('tanggal_booking')->take(5)->get(),
            'medicines' => Medicine::active()->recommendable()->orderBy('name')->get(),
            'suggested' => config('obat.kebutuhan_layanan.'.$booking->service->slug, []),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $therapist = $this->therapist($request);
        abort_unless($booking->therapist_id === $therapist->id, 403);

        if ($booking->status === 'Dibatalkan') {
            return back()->with('error', 'Booking ini sudah dibatalkan.');
        }

        $data = $request->validate([
            'catatan_terapis' => ['nullable', 'string', 'max:3000'],
            'obat' => ['nullable', 'array'],
            // Fisioterapis hanya boleh menganjurkan obat bebas / suplemen, bukan obat keras.
            'obat.*' => ['integer', Rule::exists('medicines', 'id')->where('is_active', true)->whereNot('golongan', 'keras')],
            'aturan' => ['nullable', 'array'],
            'aturan.*' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['nullable', 'array'],
            'jumlah.*' => ['nullable', 'integer', 'between:1,20'],
            'status' => ['nullable', Rule::in(['Dikonfirmasi', 'Selesai'])],
        ], [
            'obat.*.exists' => 'Obat keras hanya boleh diberikan dengan resep dokter.',
            'jumlah.*.between' => 'Jumlah obat harus 1 sampai 20.',
        ]);

        $booking->catatan_terapis = $data['catatan_terapis'] ?? null;
        if (! empty($data['status'])) {
            $booking->status = $data['status'];
        }
        $booking->save();

        // Obat yang dianjurkan otomatis masuk tagihan pasien. Harga obat yang sudah ada tetap memakai harga lama.
        $existing = $booking->medicines()->get()->keyBy('id');
        $prices = Medicine::whereIn('id', $data['obat'] ?? [])->pluck('harga', 'id');
        $sync = [];
        foreach ($data['obat'] ?? [] as $id) {
            $sync[$id] = [
                'aturan_pakai' => $data['aturan'][$id] ?? null,
                'jumlah' => (int) ($data['jumlah'][$id] ?? 1) ?: 1,
                'harga' => $existing->has($id) ? (int) $existing[$id]->pivot->harga : (int) ($prices[$id] ?? 0),
            ];
        }
        $wasPaid = $booking->isPaid();
        $booking->medicines()->sync($sync);
        $booking->syncBilling();

        $message = 'Catatan terapi & anjuran untuk '.$booking->nama.' disimpan. Total tagihan: '.$booking->formattedTotal().'.';
        if ($wasPaid && ! $booking->isPaid()) {
            $message .= ' Pasien perlu membayar sisa '.Booking::rupiah($booking->sisaTagihan()).' untuk obat.';
        } elseif ($booking->totalDibayar() > $booking->totalBayar()) {
            $message .= ' Pasien sudah membayar lebih '.Booking::rupiah($booking->totalDibayar() - $booking->totalBayar()).'; hubungi admin untuk pengembalian.';
        }

        return back()->with('success', $message);
    }

    private function therapist(Request $request): Therapist
    {
        $therapist = $request->user()->therapist;
        abort_unless($therapist, 403, 'Akun ini belum dihubungkan ke data fisioterapis. Hubungi admin.');

        return $therapist;
    }
}
