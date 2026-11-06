<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Therapist;
use App\Support\BookingSlots;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        return view('booking.create', [
            'services' => Service::active()->orderBy('id')->get(),
            'therapists' => Therapist::active()->orderBy('name')->get(),
            'selectedService' => $request->query('layanan'),
            'selectedTherapist' => $request->query('terapis'),
            'user' => $request->user(),
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'therapist_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'tanggal' => $data['tanggal'],
            'slots' => BookingSlots::forDate($data['tanggal'], $data['therapist_id'] ?? null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'therapist_id' => ['nullable', Rule::exists('therapists', 'id')->where('is_active', true)],
            'tanggal_booking' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'waktu_booking' => ['required', 'date_format:H:i', $this->slotRule($request)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan format 08xxxxxxxxxx.',
            'tanggal_booking.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'service_id.required' => 'Silakan pilih layanan.',
            'waktu_booking.required' => 'Silakan pilih jam booking.',
        ]);

        $data['waktu_booking'] .= ':00';

        $duplicate = $request->user()->bookings()
            ->whereDate('tanggal_booking', $data['tanggal_booking'])
            ->where('waktu_booking', $data['waktu_booking'])
            ->where('status', '!=', 'Dibatalkan')
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'waktu_booking' => 'Anda sudah punya booking di tanggal dan jam yang sama.',
            ]);
        }

        // Simpan nomor HP ke profil agar terisi otomatis di booking berikutnya.
        if (! $request->user()->no_hp) {
            $request->user()->update(['no_hp' => $data['no_hp']]);
        }

        $booking = $request->user()->bookings()->create($data);

        return redirect()->route('payment.show', $booking)
            ->with('success', 'Booking berhasil dibuat! Selesaikan pembayaran online, atau bayar nanti di klinik.');
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $bookings = $request->user()->bookings()
            ->with(['service', 'therapist', 'medicines'])
            ->when(in_array($status, Booking::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('booking.index', compact('bookings', 'status'));
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        $booking->load(['service', 'therapist', 'medicines', 'latestPayment']);

        // Anjuran umum sesuai layanan, dipakai bila terapis belum memberi anjuran khusus.
        $needs = config('obat.kebutuhan_layanan.'.$booking->service->slug, []);

        return view('booking.show', compact('booking', 'needs'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        if (! $booking->canBeCancelled()) {
            return back()->with('error', $booking->status_pembayaran !== 'Belum Bayar'
                ? 'Booking yang sudah dibayar tidak bisa dibatalkan sendiri. Hubungi klinik untuk pembatalan & pengembalian dana.'
                : 'Booking yang sudah dikonfirmasi/selesai tidak dapat dibatalkan.');
        }

        $booking->update(['status' => 'Dibatalkan']);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }

    public function review(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        if (! $booking->canBeRated()) {
            return back()->with('error', 'Ulasan hanya bisa diberikan sekali untuk booking yang sudah selesai.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'ulasan' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->forceFill($data + ['rated_at' => now()])->save();

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    private function slotRule(Request $request): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($request) {
            $date = $request->input('tanggal_booking');
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! preg_match('/^\d{2}:\d{2}$/', (string) $value)) {
                return;
            }

            $therapist = $request->filled('therapist_id') ? (int) $request->input('therapist_id') : null;
            $problem = BookingSlots::isAvailable($date, $value, $therapist);

            if ($problem) {
                [$open, $close] = config('klinik.jam_operasional')[\Carbon\Carbon::parse($date)->dayOfWeek];
                $fail(match ($problem) {
                    'Sudah lewat' => 'Jam booking sudah lewat. Pilih jam yang akan datang.',
                    'Penuh' => 'Slot jam tersebut sudah penuh. Pilih jam lain.',
                    'Terapis sibuk' => 'Fisioterapis yang dipilih sudah punya jadwal di jam tersebut.',
                    default => "Pilih jam sesuai slot yang tersedia ($open - $close).",
                });
            }
        };
    }
}
