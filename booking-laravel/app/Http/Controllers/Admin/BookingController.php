<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Therapist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));
        $tanggal = $request->query('tanggal');

        $bookings = Booking::with(['service', 'user', 'therapist', 'medicines'])
            ->when(in_array($status, Booking::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($tanggal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal), fn ($q) => $q->whereDate('tanggal_booking', $tanggal))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q) => $q->whereLike('nama', "%$search%")
                    ->orWhereLike('email', "%$search%")
                    ->orWhereLike('no_hp', "%$search%")
                    ->orWhereLike('kode_booking', "%$search%"));
            })
            ->orderByDesc('tanggal_booking')
            ->orderByDesc('waktu_booking')
            ->paginate(15)
            ->withQueryString();

        $counts = Booking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $therapists = Therapist::orderBy('name')->get();

        return view('admin.bookings', compact('bookings', 'counts', 'status', 'search', 'tanggal', 'therapists'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'therapist_id' => ['nullable', 'exists:therapists,id'],
        ]);

        // Satu terapis tidak boleh menangani dua pasien di slot yang sama.
        if (! empty($data['therapist_id']) && (int) $data['therapist_id'] !== (int) $booking->therapist_id) {
            $bentrok = Booking::where('id', '!=', $booking->id)
                ->where('therapist_id', $data['therapist_id'])
                ->whereDate('tanggal_booking', $booking->tanggal_booking)
                ->where('waktu_booking', $booking->waktu_booking)
                ->where('status', '!=', 'Dibatalkan')
                ->exists();

            if ($bentrok) {
                return back()->with('error', 'Fisioterapis tersebut sudah punya jadwal di jam yang sama.');
            }
        }

        $booking->update([
            'status' => $data['status'],
            'therapist_id' => $data['therapist_id'] ?? $booking->therapist_id,
        ]);

        return back()->with('success', "Booking {$booking->kode_booking} diperbarui: {$data['status']}.");
    }
}
