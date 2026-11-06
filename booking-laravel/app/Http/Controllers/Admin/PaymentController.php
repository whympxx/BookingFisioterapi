<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter');

        $payments = Payment::with(['booking.service', 'booking.user'])
            ->when($filter === 'verifikasi', fn ($q) => $q->where('status', 'pending')->whereNotNull('proof'))
            ->when(in_array($filter, ['pending', 'paid', 'failed', 'expired'], true), fn ($q) => $q->where('status', $filter))
            ->when($request->query('q'), function ($q, $search) {
                $q->where(fn ($w) => $w->whereLike('order_id', "%{$search}%")
                    ->orWhereHas('booking', fn ($b) => $b->whereLike('nama', "%{$search}%")->orWhereLike('kode_booking', "%{$search}%")));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments', [
            'payments' => $payments,
            'filter' => $filter,
            'search' => $request->query('q'),
            'summary' => [
                'diterima' => Payment::where('status', 'paid')->sum('amount'),
                'bulan_ini' => Payment::where('status', 'paid')->where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
                'verifikasi' => Payment::where('status', 'pending')->whereNotNull('proof')->count(),
                'menunggu' => Payment::where('status', 'pending')->whereNull('proof')->count(),
            ],
        ]);
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $payment->markPaid($request->user());

        return back()->with('success', "Pembayaran {$payment->booking->kode_booking} diverifikasi. Booking dikonfirmasi.");
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:255']]);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $payment->admin_note = $data['admin_note'] ?? 'Bukti transfer tidak valid.';
        $payment->verified_by = $request->user()->id;
        $payment->markFailed();

        return back()->with('success', "Pembayaran {$payment->booking->kode_booking} ditolak. Pasien bisa membayar ulang.");
    }

    /** Catat pembayaran tunai di klinik. */
    public function cash(Request $request, Booking $booking): RedirectResponse
    {
        $sisa = $booking->sisaTagihan();
        if ($booking->isPaid() || $booking->status === 'Dibatalkan' || $sisa <= 0) {
            return back()->with('error', 'Booking ini sudah lunas atau dibatalkan.');
        }

        $payment = $booking->payments()->create([
            'order_id' => $booking->kode_booking.'-T'.strtoupper(Str::random(4)),
            'method' => 'tunai',
            'amount' => $sisa,
            'status' => 'pending',
        ]);
        $payment->markPaid($request->user());

        return back()->with('success', "Pembayaran tunai {$booking->kode_booking} sebesar ".Booking::rupiah($sisa).' dicatat.');
    }
}
