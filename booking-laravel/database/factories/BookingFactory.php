<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_id' => fn () => Service::inRandomOrder()->value('id'),
            'nama' => fake()->name(),
            'email' => fake()->safeEmail(),
            'no_hp' => '08'.fake()->numerify('##########'),
            'tanggal_booking' => now()->addDays(3)->format('Y-m-d'),
            'waktu_booking' => '10:00:00',
            'catatan' => null,
            'status' => 'Menunggu',
        ];
    }
}
