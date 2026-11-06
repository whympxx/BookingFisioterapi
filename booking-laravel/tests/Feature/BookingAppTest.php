<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BookingAppTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Rabu pagi, supaya jam operasional bisa diprediksi.
        Carbon::setTestNow('2026-11-04 07:00:00');
    }

    private function bookingData(array $override = []): array
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'service_id' => Service::first()->id,
            'tanggal_booking' => '2026-11-05', // Kamis
            'waktu_booking' => '10:00',
            'catatan' => 'Nyeri lutut kanan',
        ], $override);
    }

    public function test_user_can_register_and_login(): void
    {
        $this->post('/register', [
            'username' => 'budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia1',
            'password_confirmation' => 'rahasia1',
        ])->assertRedirect('/login');

        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia1'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('Halo, budi');
    }

    public function test_register_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/register', [
            'username' => 'budi2',
            'email' => 'budi@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_admin_login_goes_to_admin_panel(): void
    {
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
            ->assertRedirect('/admin');
    }

    public function test_sql_injection_login_fails(): void
    {
        $this->post('/login', ['username' => "admin' OR '1'='1", 'password' => 'x'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_guest_cannot_open_admin_panel_or_update_status(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $booking = Booking::factory()->create();
        $this->patch("/admin/booking/{$booking->id}", ['status' => 'Selesai'])->assertRedirect('/login');
        $this->assertSame('Menunggu', $booking->fresh()->status);
    }

    public function test_regular_user_cannot_open_admin_pages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertRedirect('/dashboard');
        $this->actingAs($user)->get('/admin/admin-baru')->assertRedirect('/dashboard');
    }

    public function test_public_admin_registration_is_gone(): void
    {
        $this->get('/register_admin')->assertNotFound();
    }

    public function test_user_can_book_and_see_it_in_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/booking', $this->bookingData())
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'status' => 'Menunggu']);
        $this->actingAs($user)->get('/riwayat-booking')->assertSee('Terapi Lutut')->assertSee('Menunggu');
    }

    public function test_history_shows_booking_even_with_different_email(): void
    {
        $user = User::factory()->create(['email' => 'akun@example.com']);
        $this->actingAs($user)->post('/booking', $this->bookingData(['email' => 'lain@example.com']));

        $this->actingAs($user)->get('/riwayat-booking')->assertSee('Budi Santoso');
    }

    public function test_history_only_shows_own_bookings(): void
    {
        $other = Booking::factory()->create(['nama' => 'Pasien Lain']);
        $user = User::factory()->create(['email' => $other->email]);

        $this->actingAs($user)->get('/riwayat-booking')->assertDontSee('Pasien Lain');
    }

    public function test_booking_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/booking', $this->bookingData(['no_hp' => '12345']))
            ->assertSessionHasErrors('no_hp');
        $this->actingAs($user)->post('/booking', $this->bookingData(['tanggal_booking' => '2026-11-01']))
            ->assertSessionHasErrors('tanggal_booking');
        $this->actingAs($user)->post('/booking', $this->bookingData(['waktu_booking' => '22:00']))
            ->assertSessionHasErrors('waktu_booking');
        // Minggu tutup jam 15:00
        $this->actingAs($user)->post('/booking', $this->bookingData(['tanggal_booking' => '2026-11-08', 'waktu_booking' => '16:00']))
            ->assertSessionHasErrors('waktu_booking');
        $this->actingAs($user)->post('/booking', $this->bookingData(['service_id' => 999]))
            ->assertSessionHasErrors('service_id');
        // Hari ini jam yang sudah lewat
        Carbon::setTestNow('2026-11-04 12:00:00');
        $this->actingAs($user)->post('/booking', $this->bookingData(['tanggal_booking' => '2026-11-04', 'waktu_booking' => '09:00']))
            ->assertSessionHasErrors('waktu_booking');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_duplicate_booking_same_slot_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/booking', $this->bookingData());
        $this->actingAs($user)->post('/booking', $this->bookingData())->assertSessionHasErrors('waktu_booking');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_user_can_cancel_only_own_waiting_booking(): void
    {
        $user = User::factory()->create();
        $mine = Booking::factory()->for($user)->create();
        $confirmed = Booking::factory()->for($user)->create(['status' => 'Dikonfirmasi']);
        $other = Booking::factory()->create();

        $this->actingAs($user)->patch("/booking/{$mine->id}/batal")->assertSessionHas('success');
        $this->assertSame('Dibatalkan', $mine->fresh()->status);

        $this->actingAs($user)->patch("/booking/{$confirmed->id}/batal")->assertSessionHas('error');
        $this->assertSame('Dikonfirmasi', $confirmed->fresh()->status);

        $this->actingAs($user)->patch("/booking/{$other->id}/batal")->assertForbidden();
    }

    public function test_admin_can_update_status_with_valid_value_only(): void
    {
        $admin = User::where('username', 'admin')->first();
        $booking = Booking::factory()->create();

        $this->actingAs($admin)->patch("/admin/booking/{$booking->id}", ['status' => 'Dikonfirmasi'])
            ->assertSessionHas('success');
        $this->assertSame('Dikonfirmasi', $booking->fresh()->status);

        $this->actingAs($admin)->patch("/admin/booking/{$booking->id}", ['status' => "Selesai'; DROP TABLE bookings;--"])
            ->assertSessionHasErrors('status');
        $this->assertSame('Dikonfirmasi', $booking->fresh()->status);
    }

    public function test_admin_panel_lists_and_filters_bookings(): void
    {
        $admin = User::where('username', 'admin')->first();
        Booking::factory()->create(['nama' => 'Andi Menunggu']);
        Booking::factory()->create(['nama' => 'Citra Selesai', 'status' => 'Selesai']);

        $this->actingAs($admin)->get('/admin/booking')->assertSee('Andi Menunggu')->assertSee('Citra Selesai');
        $this->actingAs($admin)->get('/admin/booking?status=Selesai')->assertDontSee('Andi Menunggu')->assertSee('Citra Selesai');
        $this->actingAs($admin)->get('/admin/booking?q=Andi')->assertSee('Andi Menunggu')->assertDontSee('Citra Selesai');
    }

    public function test_admin_can_create_another_admin(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)->post('/admin/admin-baru', [
            'username' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => 'rahasia1',
            'password_confirmation' => 'rahasia1',
        ])->assertSessionHas('success');

        $this->assertSame('admin', User::where('username', 'admin2')->first()->role);
    }

    public function test_register_cannot_set_admin_role(): void
    {
        $this->post('/register', [
            'username' => 'nakal',
            'email' => 'nakal@example.com',
            'password' => 'rahasia1',
            'password_confirmation' => 'rahasia1',
            'role' => 'admin',
        ]);

        $this->assertSame('user', User::where('username', 'nakal')->first()->fresh()->role);
    }

    public function test_change_password(): void
    {
        $user = User::factory()->create(); // password: "password"

        $this->actingAs($user)->put('/ubah-password', [
            'password_lama' => 'salah',
            'password' => 'baru123',
            'password_confirmation' => 'baru123',
        ])->assertSessionHasErrors('password_lama');

        $this->actingAs($user)->put('/ubah-password', [
            'password_lama' => 'password',
            'password' => 'baru123',
            'password_confirmation' => 'baru123',
        ])->assertSessionHas('success');

        $this->assertTrue(password_verify('baru123', $user->fresh()->password));
    }

    public function test_forgot_password_does_not_reveal_or_change_password(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);
        $oldHash = $user->password;

        $this->post('/lupa-password', ['email' => 'budi@example.com'])->assertSessionHas('success');
        $this->post('/lupa-password', ['email' => 'tidakada@example.com'])->assertSessionHas('success');

        $this->assertSame($oldHash, $user->fresh()->password);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'budi@example.com']);
    }

    public function test_profile_photo_upload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profil/foto', [
            'profile_pic' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('profile_pic');

        // Pakai salinan JPEG asli (UploadedFile::fake()->image butuh ekstensi GD).
        $tmp = tempnam(sys_get_temp_dir(), 'img');
        copy(public_path('assets/img/ikon_fisioterapi.jpg'), $tmp);

        $this->actingAs($user)->post('/profil/foto', [
            'profile_pic' => new UploadedFile($tmp, 'foto.jpg', 'image/jpeg', null, true),
        ])->assertSessionHas('success');

        $name = $user->fresh()->profile_pic;
        $this->assertNotNull($name);
        $this->assertFileExists(public_path('uploads/'.$name));
        @unlink(public_path('uploads/'.$name));
    }

    public function test_all_user_pages_render(): void
    {
        $user = User::factory()->create();
        foreach (['/', '/dashboard', '/layanan', '/booking', '/riwayat-booking', '/ubah-password', '/profil'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }

        $this->actingAs($user)->get('/layanan')
            ->assertSee('Terapi Stroke')->assertSee('Rp 250.000')->assertSee('Riwayat');

        $admin = User::where('username', 'admin')->first();
        foreach (['/admin', '/admin/booking', '/admin/layanan', '/admin/layanan/baru', '/admin/terapis', '/admin/terapis/baru', '/admin/admin-baru', '/ubah-password'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_logout_requires_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/logout')->assertStatus(405);
        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
