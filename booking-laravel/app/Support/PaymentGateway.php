<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Membuat tagihan dan membaca status pembayaran.
 * Driver "demo" membuat nomor VA / QR sendiri; driver "midtrans" memakai Midtrans Snap.
 */
class PaymentGateway
{
    public static function driver(): string
    {
        return config('pembayaran.driver') === 'midtrans' ? 'midtrans' : 'demo';
    }

    public static function isDemo(): bool
    {
        return self::driver() === 'demo';
    }

    public static function create(Booking $booking, string $method): Payment
    {
        $info = config('pembayaran.metode.'.$method);
        if (! $info) {
            throw new RuntimeException('Metode pembayaran tidak dikenal.');
        }

        // Tagihan lama yang belum dibayar dibatalkan, supaya hanya ada satu tagihan aktif.
        $booking->payments()->where('status', 'pending')->update(['status' => 'failed']);

        $booking->load(['medicines', 'service']);
        $amount = $booking->sisaTagihan();
        if ($amount <= 0) {
            throw new RuntimeException('Tidak ada tagihan yang perlu dibayar.');
        }

        $payment = new Payment([
            'method' => $method,
            'amount' => $amount,
            'status' => 'pending',
            'expires_at' => now()->addHours(config('pembayaran.batas_jam', 24)),
        ]);
        $payment->booking()->associate($booking);
        $payment->order_id = $booking->kode_booking.'-'.strtoupper(Str::random(5));

        if ($info['tipe'] === 'manual') {
            $payment->save();

            return $payment;
        }

        if (self::driver() === 'midtrans') {
            self::createMidtrans($payment, $booking, $info);
        } elseif ($info['tipe'] === 'va') {
            $payment->va_number = $info['prefix'].str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
        } else {
            $payment->qr_string = 'DEMO|'.$payment->order_id.'|'.$payment->amount;
        }

        $payment->save();

        return $payment;
    }

    private static function createMidtrans(Payment $payment, Booking $booking, array $info): void
    {
        $response = Http::withBasicAuth(config('pembayaran.midtrans.server_key'), '')
            ->acceptJson()
            ->timeout(20)
            ->post(self::snapUrl(), [
                'transaction_details' => ['order_id' => $payment->order_id, 'gross_amount' => $payment->amount],
                'item_details' => self::itemDetails($booking, $payment->amount),
                'customer_details' => [
                    'first_name' => $booking->nama,
                    'email' => $booking->email,
                    'phone' => $booking->no_hp,
                ],
                'enabled_payments' => [$info['midtrans']],
                'expiry' => ['unit' => 'hours', 'duration' => (int) config('pembayaran.batas_jam', 24)],
                'callbacks' => ['finish' => route('payment.show', $booking)],
            ]);

        if (! $response->successful() || ! $response->json('token')) {
            throw new RuntimeException('Gagal menghubungi Midtrans: '.($response->json('error_messages.0') ?? $response->status()));
        }

        $payment->snap_token = $response->json('token');
        $payment->payload = ['redirect_url' => $response->json('redirect_url')];
    }

    /**
     * Rincian tagihan untuk Midtrans (jumlahnya wajib sama dengan nominal bayar).
     * Bila sebagian sudah dibayar, dikirim satu baris "sisa tagihan".
     */
    public static function itemDetails(Booking $booking, int $amount): array
    {
        if ($amount !== $booking->totalBayar()) {
            return [['id' => 'SISA-'.$booking->id, 'price' => $amount, 'quantity' => 1, 'name' => 'Sisa tagihan '.$booking->kode_booking]];
        }

        $items = [[
            'id' => 'SVC-'.$booking->service_id,
            'price' => $booking->biayaLayanan(),
            'quantity' => 1,
            'name' => Str::limit($booking->service->name, 45, ''),
        ]];
        foreach ($booking->medicines as $m) {
            if ((int) $m->pivot->harga <= 0) {
                continue;
            }
            $items[] = [
                'id' => 'OBT-'.$m->id,
                'price' => (int) $m->pivot->harga,
                'quantity' => (int) $m->pivot->jumlah,
                'name' => Str::limit($m->name, 45, ''),
            ];
        }

        return $items;
    }

    /** Tanya status terbaru ke Midtrans (berguna di localhost, saat notifikasi tidak bisa masuk). */
    public static function refresh(Payment $payment): Payment
    {
        if (self::driver() !== 'midtrans' || $payment->status !== 'pending' || ! $payment->snap_token) {
            return $payment;
        }

        $base = config('pembayaran.midtrans.production') ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
        $response = Http::withBasicAuth(config('pembayaran.midtrans.server_key'), '')
            ->acceptJson()->timeout(15)
            ->get($base.'/v2/'.urlencode($payment->order_id).'/status');

        if ($response->successful() && $response->json('transaction_status')) {
            self::apply($payment, $response->json());
        }

        return $payment->fresh();
    }

    /** Proses notifikasi dari Midtrans. Mengembalikan false bila tanda tangan tidak valid. */
    public static function handleNotification(array $data): bool
    {
        $serverKey = (string) config('pembayaran.midtrans.server_key');
        $expected = hash('sha512', ($data['order_id'] ?? '').($data['status_code'] ?? '').($data['gross_amount'] ?? '').$serverKey);

        if ($serverKey === '' || ! hash_equals($expected, (string) ($data['signature_key'] ?? ''))) {
            return false;
        }

        $payment = Payment::where('order_id', $data['order_id'])->first();
        if ($payment) {
            self::apply($payment, $data);
        }

        return true;
    }

    private static function apply(Payment $payment, array $data): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        // Nominal harus sama dengan tagihan.
        if (isset($data['gross_amount']) && (int) round((float) $data['gross_amount']) !== $payment->amount) {
            return;
        }

        $status = $data['transaction_status'] ?? '';
        $fraud = $data['fraud_status'] ?? 'accept';

        $payment->payload = array_merge($payment->payload ?? [], ['last_notification' => $data]);
        $payment->va_number = $data['va_numbers'][0]['va_number'] ?? $data['bill_key'] ?? $payment->va_number;
        $payment->save();

        if (in_array($status, ['settlement', 'capture'], true) && $fraud === 'accept') {
            $payment->markPaid();
        } elseif ($status === 'expire') {
            $payment->markFailed('expired');
        } elseif (in_array($status, ['cancel', 'deny', 'failure'], true)) {
            $payment->markFailed();
        }
    }

    public static function snapUrl(): string
    {
        return config('pembayaran.midtrans.production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public static function snapJs(): string
    {
        return config('pembayaran.midtrans.production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
