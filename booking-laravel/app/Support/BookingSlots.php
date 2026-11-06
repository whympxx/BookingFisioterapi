<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\Carbon;

/**
 * Menghitung slot jam booking yang masih tersedia untuk satu tanggal.
 */
class BookingSlots
{
    /**
     * @return array<int, array{time: string, remaining: int, available: bool, reason: ?string}>
     */
    public static function forDate(string $date, ?int $therapistId = null): array
    {
        $day = Carbon::parse($date)->startOfDay();
        [$open, $close] = config('klinik.jam_operasional')[$day->dayOfWeek];
        $capacity = (int) config('klinik.kapasitas_per_slot');
        $step = (int) config('klinik.durasi_slot_menit');

        $active = Booking::whereDate('tanggal_booking', $day->toDateString())
            ->where('status', '!=', 'Dibatalkan')
            ->get(['waktu_booking', 'therapist_id']);

        $slots = [];
        $time = $day->copy()->setTimeFromTimeString($open);
        $end = $day->copy()->setTimeFromTimeString($close)->subMinutes($step);

        while ($time <= $end) {
            $label = $time->format('H:i');
            $inSlot = $active->filter(fn ($b) => substr((string) $b->waktu_booking, 0, 5) === $label);
            $remaining = max(0, $capacity - $inSlot->count());

            $reason = null;
            if ($time->isPast()) {
                $reason = 'Sudah lewat';
            } elseif ($remaining === 0) {
                $reason = 'Penuh';
            } elseif ($therapistId && $inSlot->contains('therapist_id', $therapistId)) {
                $reason = 'Terapis sibuk';
            }

            $slots[] = [
                'time' => $label,
                'remaining' => $remaining,
                'available' => $reason === null,
                'reason' => $reason,
            ];
            $time->addMinutes($step);
        }

        return $slots;
    }

    public static function isAvailable(string $date, string $time, ?int $therapistId = null): ?string
    {
        foreach (self::forDate($date, $therapistId) as $slot) {
            if ($slot['time'] === $time) {
                return $slot['available'] ? null : $slot['reason'];
            }
        }

        return 'Di luar jam operasional';
    }
}
