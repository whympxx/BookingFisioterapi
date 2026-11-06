<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Support\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    public function show(Request $request, Booking $booking): View|RedirectResponse
    {
        $this->authorizeOwner($request, $booking);
        $booking->load(['service', 'therapist', 'medicines']);
        // Tagihan lama dengan nominal berbeda (misalnya terapis baru menambah obat) dibatalkan otomatis.
        $booking->syncBilling();

        if ($booking->isPaid()) {
            return redirect()->route('booking.show', $booking)->with('success', 'Booking ini sudah lunas.');
        }
        if (! $booking->canBePaid()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Booking ini tidak bisa dibayar.');
        }

        $payment = $this->openPayment($booking);
        if ($payment) {
            $payment = PaymentGateway::refresh($payment);
            if ($payment->status === 'paid') {
                return redirect()->route('booking.show', $booking)->with('success', 'Pembayaran berhasil! Booking Anda sudah dikonfirmasi.');
            }
            if (! $payment->isOpen()) {
                $payment = null;
            }
        }

        return view('payment.show', [
            'booking' => $booking,
            'payment' => $payment,
            'methods' => collect(config('pembayaran.metode'))->groupBy('grup', true),
            'isDemo' => PaymentGateway::isDemo(),
            'history' => $booking->payments()->latest()->get(),
        ]);
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeOwner($request, $booking);
        if (! $booking->canBePaid()) {
            return redirect()->route('booking.show', $booking)->with('error', 'Booking ini tidak bisa dibayar.');
        }

        $data = $request->validate(
            ['method' => ['required', Rule::in(array_keys(config('pembayaran.metode')))]],
            ['method.required' => 'Pilih metode pembayaran.']
        );

        try {
            PaymentGateway::create($booking, $data['method']);
        } catch (RuntimeException $e) {
            report($e);

            return back()->with('error', 'Pembayaran belum bisa dibuat. Coba lagi beberapa saat atau pilih metode lain.');
        }

        return redirect()->route('payment.show', $booking);
    }

    public function status(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeOwner($request, $booking);
        $payment = $booking->latestPayment;
        if ($payment) {
            $payment = PaymentGateway::refresh($payment);
        }

        return response()->json([
            'status' => $payment?->status,
            'lunas' => $booking->fresh()->isPaid(),
            'redirect' => route('booking.show', $booking),
        ]);
    }

    public function uploadProof(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $booking, $payment);
        abort_unless($payment->method === 'transfer' && $payment->isOpen(), 422);

        $request->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:3072'],
        ], ['bukti.required' => 'Pilih file bukti transfer.']);

        $name = 'bukti_'.$booking->id.'_'.time().'_'.Str::random(5).'.'.$request->file('bukti')->extension();
        $request->file('bukti')->move(public_path('uploads/bukti'), $name);

        $payment->update(['proof' => $name]);
        $booking->forceFill(['status_pembayaran' => 'Menunggu Verifikasi'])->save();

        return redirect()->route('booking.show', $booking)
            ->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi maksimal 1×24 jam.');
    }

    public function cancel(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $booking, $payment);
        if ($payment->isOpen() && ! $payment->proof) {
            $payment->markFailed();
        }

        return redirect()->route('payment.show', $booking)->with('success', 'Silakan pilih metode pembayaran lain.');
    }

    /** Hanya untuk mode demo: menyimulasikan pasien sudah membayar. */
    public function simulate(Request $request, Booking $booking, Payment $payment): RedirectResponse
    {
        abort_unless(PaymentGateway::isDemo(), 404);
        $this->authorizePayment($request, $booking, $payment);
        abort_unless($payment->isOpen() && $payment->method !== 'transfer', 422);

        $payment->markPaid();

        return redirect()->route('booking.show', $booking)
            ->with('success', 'Pembayaran berhasil! Booking Anda otomatis dikonfirmasi.');
    }

    /** Notifikasi server-ke-server dari Midtrans. */
    public function notification(Request $request): JsonResponse
    {
        if (! PaymentGateway::handleNotification($request->all())) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        return response()->json(['message' => 'ok']);
    }

    private function openPayment(Booking $booking): ?Payment
    {
        $payment = $booking->payments()->where('status', 'pending')->latest()->first();
        if ($payment && ! $payment->isOpen()) {
            $payment->markFailed('expired');

            return null;
        }

        return $payment;
    }

    private function authorizeOwner(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
    }

    private function authorizePayment(Request $request, Booking $booking, Payment $payment): void
    {
        $this->authorizeOwner($request, $booking);
        abort_unless($payment->booking_id === $booking->id, 404);
    }
}
