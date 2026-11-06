<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-04 07:00:00'); // Rabu
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'service_id' => Service::first()->id,
            'tanggal_booking' => '2026-11-05',
            'waktu_booking' => '10:00',
        ], $override);
    }

    private function admin(): User
    {
        return User::where('username', 'admin')->first();
    }

    public function test_home_page_is_public_and_shows_services_and_therapists(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Terapi Stroke')
            ->assertSee('Ftr. Andini Pratiwi')
            ->assertSee('data-spine3d', false);
        $this->get('/layanan')->assertOk()->assertSee('Rp 250.000');
    }

    public function test_slots_endpoint_lists_hourly_slots(): void
    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->getJson('/booking/slot?tanggal=2026-11-05')->assertOk();

        $times = collect($res->json('slots'))->pluck('time');
        $this->assertSame('08:00', $times->first());
        $this->assertSame('19:00', $times->last()); // tutup 20:00, slot terakhir 19:00

        // Minggu: 09:00 - 15:00
        $sunday = collect($this->actingAs($user)->getJson('/booking/slot?tanggal=2026-11-08')->json('slots'))->pluck('time');
        $this->assertSame(['09:00', '10:00', '11:00', '12:00', '13:00', '14:00'], $sunday->all());
    }

    public function test_full_slot_is_rejected(): void
    {
        Booking::factory()->count(3)->create(['tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00:00']);

        $user = User::factory()->create();
        $slot = collect($this->actingAs($user)->getJson('/booking/slot?tanggal=2026-11-05')->json('slots'))->firstWhere('time', '10:00');
        $this->assertFalse($slot['available']);

        $this->actingAs($user)->post('/booking', $this->payload())->assertSessionHasErrors('waktu_booking');
    }

    public function test_cancelled_bookings_free_up_the_slot(): void
    {
        Booking::factory()->count(3)->create(['tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00:00', 'status' => 'Dibatalkan']);

        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->payload())->assertSessionHasNoErrors();
    }

    public function test_busy_therapist_is_rejected_but_other_therapist_ok(): void
    {
        [$t1, $t2] = Therapist::take(2)->get();
        Booking::factory()->create(['tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00:00', 'therapist_id' => $t1->id]);

        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->payload(['therapist_id' => $t1->id]))->assertSessionHasErrors('waktu_booking');
        $this->actingAs($user)->post('/booking', $this->payload(['therapist_id' => $t2->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'therapist_id' => $t2->id]);
    }

    public function test_off_the_hour_time_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->payload(['waktu_booking' => '10:30']))->assertSessionHasErrors('waktu_booking');
    }

    public function test_booking_gets_code_and_ticket_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->payload());
        $booking = Booking::first();

        $this->assertMatchesRegularExpression('/^FIS-\d{6}-[A-Z0-9]{4}$/', $booking->kode_booking);
        $this->actingAs($user)->get("/booking/{$booking->id}")->assertOk()->assertSee($booking->kode_booking);

        $other = User::factory()->create();
        $this->actingAs($other)->get("/booking/{$booking->id}")->assertForbidden();
    }

    public function test_phone_saved_to_profile_after_first_booking(): void
    {
        $user = User::factory()->create(['no_hp' => null]);
        $this->actingAs($user)->post('/booking', $this->payload());
        $this->assertSame('081234567890', $user->fresh()->no_hp);
    }

    public function test_review_only_for_finished_booking_once(): void
    {
        $user = User::factory()->create();
        $waiting = Booking::factory()->for($user)->create();
        $done = Booking::factory()->for($user)->create(['status' => 'Selesai']);

        $this->actingAs($user)->post("/booking/{$waiting->id}/ulasan", ['rating' => 5])->assertSessionHas('error');

        $this->actingAs($user)->post("/booking/{$done->id}/ulasan", ['rating' => 6])->assertSessionHasErrors('rating');
        $this->actingAs($user)->post("/booking/{$done->id}/ulasan", ['rating' => 5, 'ulasan' => 'Lutut saya sudah tidak nyeri lagi'])
            ->assertSessionHas('success');
        $this->assertSame(5, $done->fresh()->rating);

        $this->actingAs($user)->post("/booking/{$done->id}/ulasan", ['rating' => 1])->assertSessionHas('error');
        $this->assertSame(5, $done->fresh()->rating);

        $this->get('/')->assertSee('Lutut saya sudah tidak nyeri lagi');
    }

    public function test_profile_update(): void
    {
        $taken = User::factory()->create(['email' => 'dipakai@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profil', ['email' => 'dipakai@example.com'])->assertSessionHasErrors('email');
        $this->actingAs($user)->put('/profil', ['email' => $user->email, 'no_hp' => '123'])->assertSessionHasErrors('no_hp');
        $this->actingAs($user)->put('/profil', ['nama_lengkap' => 'Budi Santoso', 'email' => 'baru@example.com', 'no_hp' => '081298765432'])
            ->assertSessionHas('success');

        $this->assertSame('Budi Santoso', $user->fresh()->nama_lengkap);
        $this->actingAs($user)->get('/booking')->assertSee('value="Budi Santoso"', false)->assertSee('081298765432');
    }

    public function test_admin_can_manage_services(): void
    {
        $service = Service::where('slug', 'pijat-medis')->first();

        $this->actingAs($this->admin())->put("/admin/layanan/{$service->id}", [
            'name' => 'Pijat Medis', 'description' => 'Baru', 'features' => "Satu\nDua\n\n",
            'duration' => '60 menit', 'price' => 135000, 'icon' => 'fa-hands', 'is_active' => 1,
        ])->assertRedirect('/admin/layanan');

        $service->refresh();
        $this->assertSame(135000, $service->price);
        $this->assertSame(['Satu', 'Dua'], $service->features);

        $this->get('/layanan')->assertSee('Rp 135.000');

        $this->actingAs($this->admin())->patch("/admin/layanan/{$service->id}/toggle");
        $this->assertFalse($service->fresh()->is_active);
        $this->get('/layanan')->assertDontSee('<h3>Pijat Medis</h3>', false);

        // Layanan nonaktif tidak bisa dibooking
        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->payload(['service_id' => $service->id]))->assertSessionHasErrors('service_id');

        $this->actingAs($this->admin())->post('/admin/layanan', [
            'name' => 'Terapi Bahu', 'description' => 'Nyeri bahu', 'features' => 'Frozen shoulder',
            'duration' => '60 menit', 'price' => 170000, 'icon' => 'fa-dumbbell', 'is_active' => 1,
        ])->assertRedirect('/admin/layanan');
        $this->assertDatabaseHas('services', ['slug' => 'terapi-bahu', 'price' => 170000]);
    }

    public function test_admin_can_manage_therapists_and_assign(): void
    {
        $this->actingAs($this->admin())->post('/admin/terapis', [
            'name' => 'Ftr. Eka Putri', 'specialization' => 'Geriatri', 'experience_years' => 4, 'is_active' => 1,
        ])->assertRedirect('/admin/terapis');
        $eka = Therapist::where('name', 'Ftr. Eka Putri')->first();
        $this->assertTrue($eka->is_active);
        $this->assertSame('EP', $eka->initials());

        $a = Booking::factory()->create(['tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00:00', 'therapist_id' => $eka->id]);
        $b = Booking::factory()->create(['tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00:00']);

        $this->actingAs($this->admin())->patch("/admin/booking/{$b->id}", ['status' => 'Dikonfirmasi', 'therapist_id' => $eka->id])
            ->assertSessionHas('error');
        $this->assertNull($b->fresh()->therapist_id);

        $other = Therapist::where('id', '!=', $eka->id)->first();
        $this->actingAs($this->admin())->patch("/admin/booking/{$b->id}", ['status' => 'Dikonfirmasi', 'therapist_id' => $other->id])
            ->assertSessionHas('success');
        $this->assertSame($other->id, $b->fresh()->therapist_id);
    }

    public function test_admin_dashboard_shows_revenue(): void
    {
        $service = Service::where('slug', 'terapi-stroke')->first();
        Booking::factory()->count(2)->create(['service_id' => $service->id, 'status' => 'Selesai']);

        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Rp 500.000');
    }

    public function test_patient_cannot_access_admin_management(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/layanan')->assertRedirect('/dashboard');
        $this->actingAs($user)->post('/admin/terapis', ['name' => 'x'])->assertRedirect('/dashboard');
        $this->assertDatabaseMissing('therapists', ['name' => 'x']);
    }
}
