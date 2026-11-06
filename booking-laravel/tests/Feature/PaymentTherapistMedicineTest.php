<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTherapistMedicineTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-04 07:00:00');
        config(['pembayaran.driver' => 'demo']);
    }

    private function admin(): User
    {
        return User::where('username', 'admin')->first();
    }

    private function booking(array $attrs = []): Booking
    {
        return Booking::factory()->create($attrs + ['tanggal_booking' => '2026-11-05']);
    }

    // ---------- Pembayaran ----------

    public function test_new_booking_goes_to_payment_page_with_locked_price(): void
    {
        $user = User::factory()->create();
        $service = Service::where('slug', 'terapi-lutut')->first();

        $this->actingAs($user)->post('/booking', [
            'nama' => 'Budi', 'email' => 'budi@example.com', 'no_hp' => '081234567890',
            'service_id' => $service->id, 'tanggal_booking' => '2026-11-05', 'waktu_booking' => '10:00',
        ])->assertRedirect('/booking/'.Booking::first()->id.'/bayar');

        $booking = Booking::first();
        $this->assertSame(150000, $booking->harga);
        $this->assertSame('Belum Bayar', $booking->fresh()->status_pembayaran);

        // Harga layanan naik setelah booking: tagihan tetap harga lama.
        $service->update(['price' => 175000]);
        $this->assertSame(150000, $booking->fresh()->totalBayar());

        $this->actingAs($user)->get("/booking/{$booking->id}/bayar")->assertOk()
            ->assertSee('QRIS')->assertSee('BCA Virtual Account')->assertSee('Rp 150.000');
    }

    public function test_virtual_account_payment_demo_flow(): void
    {
        $booking = $this->booking();
        $user = $booking->user;

        $this->actingAs($user)->post("/booking/{$booking->id}/bayar", ['method' => 'bca_va'])
            ->assertRedirect("/booking/{$booking->id}/bayar");

        $payment = Payment::first();
        $this->assertSame('pending', $payment->status);
        $this->assertSame($booking->totalBayar(), $payment->amount);
        $this->assertStringStartsWith('70012', $payment->va_number);

        $this->actingAs($user)->get("/booking/{$booking->id}/bayar")->assertOk()->assertSee('Nomor Virtual Account BCA');
        $this->actingAs($user)->getJson("/booking/{$booking->id}/bayar/status")->assertJson(['status' => 'pending', 'lunas' => false]);

        $this->actingAs($user)->post("/booking/{$booking->id}/bayar/{$payment->id}/simulasi")
            ->assertRedirect("/booking/{$booking->id}");

        $booking->refresh();
        $this->assertSame('Lunas', $booking->status_pembayaran);
        $this->assertSame('Dikonfirmasi', $booking->status);
        $this->assertNotNull($payment->fresh()->paid_at);

        $this->actingAs($user)->get("/booking/{$booking->id}")->assertSee('Lunas')->assertSee('BCA Virtual Account');
        // Sudah lunas: halaman bayar mengarahkan ke tiket.
        $this->actingAs($user)->get("/booking/{$booking->id}/bayar")->assertRedirect("/booking/{$booking->id}");
    }

    public function test_choosing_new_method_cancels_old_bill(): void
    {
        $booking = $this->booking();
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'qris']);
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'bni_va']);

        $this->assertSame(1, $booking->payments()->where('status', 'pending')->count());
        $this->assertSame('bni_va', $booking->payments()->where('status', 'pending')->value('method'));
    }

    public function test_payment_security(): void
    {
        $booking = $this->booking();
        $other = User::factory()->create();

        $this->actingAs($other)->get("/booking/{$booking->id}/bayar")->assertForbidden();
        $this->actingAs($other)->post("/booking/{$booking->id}/bayar", ['method' => 'qris'])->assertForbidden();
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'bitcoin'])->assertSessionHasErrors('method');

        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'qris']);
        $payment = Payment::first();
        $this->actingAs($other)->post("/booking/{$booking->id}/bayar/{$payment->id}/simulasi")->assertForbidden();

        // Tombol simulasi hilang bila memakai payment gateway sungguhan.
        config(['pembayaran.driver' => 'midtrans']);
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar/{$payment->id}/simulasi")->assertNotFound();
        $this->assertSame('Belum Bayar', $booking->fresh()->status_pembayaran);

        // Booking yang dibatalkan tidak bisa dibayar.
        $cancelled = $this->booking(['user_id' => $booking->user_id, 'status' => 'Dibatalkan', 'waktu_booking' => '11:00:00']);
        $this->actingAs($booking->user)->get("/booking/{$cancelled->id}/bayar")->assertRedirect("/booking/{$cancelled->id}");
    }

    public function test_expired_bill_is_closed(): void
    {
        $booking = $this->booking();
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'bri_va']);

        Carbon::setTestNow(now()->addHours(25));
        $this->actingAs($booking->user)->get("/booking/{$booking->id}/bayar")->assertOk()->assertSee('Pilih metode pembayaran');
        $this->assertSame('expired', Payment::first()->status);
    }

    public function test_manual_transfer_with_admin_verification(): void
    {
        $booking = $this->booking();
        $user = $booking->user;
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar", ['method' => 'transfer']);
        $payment = Payment::first();

        $this->actingAs($user)->get("/booking/{$booking->id}/bayar")->assertSee('123 456 7890');
        // Transfer manual tidak bisa disimulasikan, harus diverifikasi admin.
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar/{$payment->id}/simulasi")->assertStatus(422);

        $file = UploadedFile::fake()->create('bukti.pdf', 50, 'application/pdf');
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar/{$payment->id}/bukti", ['bukti' => $file])
            ->assertSessionHas('success');

        $payment->refresh();
        $this->assertNotNull($payment->proof);
        $this->assertSame('Menunggu Verifikasi', $booking->fresh()->status_pembayaran);
        $this->assertSame('Menunggu Verifikasi', $payment->statusLabel());

        // Pasien tidak bisa membatalkan sendiri booking yang sudah dibayar.
        $this->actingAs($user)->patch("/booking/{$booking->id}/batal")->assertSessionHas('error');
        $this->assertSame('Menunggu', $booking->fresh()->status);

        $this->actingAs($this->admin())->get('/admin/pembayaran?filter=verifikasi')->assertOk()->assertSee($booking->kode_booking);
        $this->actingAs($this->admin())->patch("/admin/pembayaran/{$payment->id}/verifikasi")->assertSessionHas('success');

        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
        $this->assertSame($this->admin()->id, $payment->fresh()->verified_by);

        File::delete(public_path('uploads/bukti/'.$payment->proof));
    }

    public function test_admin_can_reject_transfer_proof(): void
    {
        $booking = $this->booking();
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'transfer']);
        $payment = Payment::first();
        $payment->update(['proof' => 'x.jpg']);
        $booking->forceFill(['status_pembayaran' => 'Menunggu Verifikasi'])->save();

        $this->actingAs($this->admin())->patch("/admin/pembayaran/{$payment->id}/tolak", ['admin_note' => 'Nominal kurang'])
            ->assertSessionHas('success');

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('Nominal kurang', $payment->fresh()->admin_note);
        $this->assertSame('Belum Bayar', $booking->fresh()->status_pembayaran);
    }

    public function test_admin_records_cash_payment(): void
    {
        $booking = $this->booking();
        $this->actingAs($this->admin())->post("/admin/booking/{$booking->id}/tunai")->assertSessionHas('success');

        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
        $this->assertSame('tunai', $booking->payments()->value('method'));

        $this->actingAs($this->admin())->post("/admin/booking/{$booking->id}/tunai")->assertSessionHas('error');
        $this->assertSame(1, $booking->payments()->count());
    }

    public function test_midtrans_snap_and_notification(): void
    {
        config(['pembayaran.driver' => 'midtrans', 'pembayaran.midtrans.server_key' => 'SB-Mid-server-TEST']);
        Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['token' => 'snap-token-123', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/x'])]);

        $booking = $this->booking();
        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'gopay'])->assertRedirect();

        $payment = Payment::first();
        $this->assertSame('snap-token-123', $payment->snap_token);
        Http::assertSent(fn ($r) => $r['enabled_payments'] === ['gopay'] && $r['transaction_details']['gross_amount'] === $payment->amount);

        $notif = fn (string $status, ?string $amount = null) => [
            'order_id' => $payment->order_id, 'status_code' => '200', 'gross_amount' => $amount ?? $payment->amount.'.00',
            'transaction_status' => $status, 'fraud_status' => 'accept',
        ];
        $sign = fn (array $d) => $d + ['signature_key' => hash('sha512', $d['order_id'].$d['status_code'].$d['gross_amount'].'SB-Mid-server-TEST')];

        // Tanda tangan palsu ditolak.
        $this->postJson('/pembayaran/notifikasi', $notif('settlement') + ['signature_key' => 'palsu'])->assertForbidden();
        // Nominal berbeda diabaikan.
        $this->postJson('/pembayaran/notifikasi', $sign($notif('settlement', '1000.00')))->assertOk();
        $this->assertSame('pending', $payment->fresh()->status);

        $this->postJson('/pembayaran/notifikasi', $sign($notif('settlement')))->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
    }

    // ---------- Halaman terapis ----------

    public function test_therapist_logs_in_to_own_panel(): void
    {
        $this->post('/login', ['username' => 'andini', 'password' => 'terapis123'])->assertRedirect('/panel-terapis');
        $this->get('/panel-terapis')->assertOk()->assertSee('Ftr. Andini Pratiwi');
        $this->get('/admin')->assertRedirect('/panel-terapis');
        $this->get('/dashboard')->assertRedirect('/panel-terapis');
    }

    public function test_therapist_sees_only_own_patients_and_writes_notes(): void
    {
        $andini = Therapist::where('name', 'Ftr. Andini Pratiwi')->first();
        $bima = Therapist::where('name', 'Ftr. Bima Saputra')->first();
        $mine = $this->booking(['therapist_id' => $andini->id, 'tanggal_booking' => today()->toDateString(), 'waktu_booking' => '09:00:00', 'nama' => 'Pasien Andini']);
        $theirs = $this->booking(['therapist_id' => $bima->id, 'nama' => 'Pasien Bima']);

        $this->actingAs($andini->user)->get('/panel-terapis')->assertSee('Pasien Andini')->assertDontSee('Pasien Bima');
        $this->actingAs($andini->user)->get('/panel-terapis/jadwal')->assertSee('Pasien Andini')->assertDontSee('Pasien Bima');
        $this->actingAs($andini->user)->get("/panel-terapis/pasien/{$theirs->id}")->assertForbidden();
        $this->actingAs($andini->user)->put("/panel-terapis/pasien/{$theirs->id}", ['catatan_terapis' => 'x'])->assertForbidden();

        $gel = Medicine::where('slug', 'natrium-diklofenak-gel-1')->first();
        $keras = Medicine::where('golongan', 'keras')->first();

        // Obat keras tidak boleh dianjurkan fisioterapis.
        $this->actingAs($andini->user)->put("/panel-terapis/pasien/{$mine->id}", ['obat' => [$keras->id]])
            ->assertSessionHasErrors('obat.0');

        $this->actingAs($andini->user)->get("/panel-terapis/pasien/{$mine->id}")->assertOk()->assertDontSee($keras->name);
        $this->actingAs($andini->user)->put("/panel-terapis/pasien/{$mine->id}", [
            'catatan_terapis' => 'Latihan quadriceps 3x10 setiap pagi.',
            'obat' => [$gel->id],
            'aturan' => [$gel->id => 'Oles lutut kanan 3x sehari'],
            'status' => 'Selesai',
        ])->assertSessionHas('success');

        $mine->refresh();
        $this->assertSame('Selesai', $mine->status);
        $this->assertSame('Oles lutut kanan 3x sehari', $mine->medicines->first()->pivot->aturan_pakai);

        // Pasien melihat catatan & anjuran di tiket dan halaman obat.
        $this->actingAs($mine->user)->get("/booking/{$mine->id}")
            ->assertSee('Latihan quadriceps 3x10 setiap pagi.')->assertSee('Oles lutut kanan 3x sehari');
        $this->actingAs($mine->user)->get('/obat')->assertSee('Anjuran dari fisioterapis Anda');
    }

    public function test_recommended_medicines_are_added_to_patient_bill(): void
    {
        $andini = Therapist::where('name', 'Ftr. Andini Pratiwi')->first();
        $service = Service::where('slug', 'terapi-lutut')->first(); // Rp 150.000
        $booking = $this->booking(['therapist_id' => $andini->id, 'service_id' => $service->id]);
        $user = $booking->user;
        $gel = Medicine::where('slug', 'natrium-diklofenak-gel-1')->first();   // Rp 45.000
        $para = Medicine::where('slug', 'paracetamol-500-mg')->first();        // Rp 5.000

        // 1) Pasien membayar sesi terapi dulu.
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar", ['method' => 'qris']);
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar/".Payment::first()->id.'/simulasi');
        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
        $this->assertSame(150000, $booking->fresh()->totalDibayar());

        // 2) Terapis menganjurkan obat: otomatis masuk tagihan.
        $this->actingAs($andini->user)->put("/panel-terapis/pasien/{$booking->id}", [
            'obat' => [$gel->id, $para->id],
            'jumlah' => [$gel->id => 1, $para->id => 2],
            'status' => 'Selesai',
        ])->assertSessionHas('success', fn ($msg) => str_contains($msg, 'sisa Rp 55.000'));

        $booking = $booking->fresh();
        $this->assertSame(55000, $booking->biayaObat());
        $this->assertSame(205000, $booking->totalBayar());
        $this->assertSame(55000, $booking->sisaTagihan());
        $this->assertSame('Belum Bayar', $booking->status_pembayaran);
        $this->assertTrue($booking->canBePaid()); // tetap bisa dibayar walau sesi sudah selesai

        $this->actingAs($user)->get("/booking/{$booking->id}")
            ->assertSee('Rincian Tagihan')->assertSee('Rp 205.000')->assertSee('Bayar Rp 55.000');

        // 3) Pasien membayar sisa tagihan obat saja.
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar", ['method' => 'bca_va']);
        $sisa = Payment::where('status', 'pending')->first();
        $this->assertSame(55000, $sisa->amount);
        $this->actingAs($user)->post("/booking/{$booking->id}/bayar/{$sisa->id}/simulasi");

        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
        $this->assertSame(0, $booking->fresh()->sisaTagihan());

        // Harga obat dikunci: kenaikan harga setelahnya tidak mengubah tagihan.
        $gel->update(['harga' => 99000]);
        $this->assertSame(205000, $booking->fresh()->totalBayar());
    }

    public function test_open_bill_is_replaced_when_therapist_changes_medicines(): void
    {
        $andini = Therapist::where('name', 'Ftr. Andini Pratiwi')->first();
        $booking = $this->booking(['therapist_id' => $andini->id]);
        $old = $booking->totalBayar();

        $this->actingAs($booking->user)->post("/booking/{$booking->id}/bayar", ['method' => 'bri_va']);
        $this->assertSame($old, Payment::first()->amount);

        $omega = Medicine::where('slug', 'omega-3-minyak-ikan')->first();
        $this->actingAs($andini->user)->put("/panel-terapis/pasien/{$booking->id}", ['obat' => [$omega->id]]);

        // Tagihan lama (nominal lama) dibatalkan; pasien diminta memilih metode lagi dengan nominal baru.
        $this->assertSame('failed', Payment::first()->fresh()->status);
        $this->actingAs($booking->user)->get("/booking/{$booking->id}/bayar")
            ->assertSee('Pilih metode pembayaran')->assertSee(\App\Models\Booking::rupiah($old + 85000));

        // Kasir mencatat tunai sebesar total baru.
        $this->actingAs($this->admin())->post("/admin/booking/{$booking->id}/tunai");
        $this->assertSame($old + 85000, $booking->payments()->where('method', 'tunai')->value('amount'));
        $this->assertSame('Lunas', $booking->fresh()->status_pembayaran);
    }

    public function test_patient_cannot_open_therapist_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/panel-terapis')->assertRedirect('/dashboard');
    }

    public function test_admin_creates_therapist_with_login(): void
    {
        $this->actingAs($this->admin())->post('/admin/terapis', [
            'name' => 'Ftr. Eka Putri', 'specialization' => 'Geriatri', 'experience_years' => 3, 'is_active' => 1,
            'username' => 'eka', 'email' => 'eka@example.com', 'password' => 'rahasia1',
        ])->assertRedirect('/admin/terapis');

        $eka = Therapist::where('name', 'Ftr. Eka Putri')->first();
        $this->assertSame('terapis', $eka->user->role);

        // Tanpa password tidak bisa membuat akun baru.
        $this->actingAs($this->admin())->post('/admin/terapis', [
            'name' => 'Ftr. Fajar', 'specialization' => 'X', 'experience_years' => 1, 'username' => 'fajar', 'email' => 'f@example.com',
        ])->assertSessionHasErrors('password');

        // Edit tanpa mengganti password.
        $this->actingAs($this->admin())->put("/admin/terapis/{$eka->id}", [
            'name' => 'Ftr. Eka Putri', 'specialization' => 'Geriatri', 'experience_years' => 4, 'is_active' => 1,
            'username' => 'ekaputri', 'email' => 'eka@example.com', 'password' => '',
        ])->assertRedirect('/admin/terapis');

        auth()->logout();
        $this->post('/login', ['username' => 'ekaputri', 'password' => 'rahasia1'])->assertRedirect('/panel-terapis');
    }

    public function test_public_therapist_pages(): void
    {
        $andini = Therapist::where('name', 'Ftr. Andini Pratiwi')->first();
        $this->get('/terapis')->assertOk()->assertSee('Ftr. Bima Saputra');
        $this->get("/terapis/{$andini->id}")->assertOk()->assertSee('Muskuloskeletal');

        $andini->update(['is_active' => false]);
        $this->get("/terapis/{$andini->id}")->assertNotFound();

        $user = User::factory()->create();
        $this->actingAs($user)->get('/booking?terapis='.Therapist::where('name', 'Ftr. Bima Saputra')->value('id'))->assertOk();
    }

    // ---------- Obat ----------

    public function test_medicine_finder_filters_by_need_and_condition(): void
    {
        $res = $this->get('/obat?kebutuhan[]=nyeri-sendi&kondisi[]=maag')->assertOk();

        $suitable = $res->viewData('suitable')->pluck('name');
        $caution = $res->viewData('caution')->pluck('name');

        $this->assertContains('Paracetamol 500 mg', $suitable);
        $this->assertContains('Glukosamin & Kondroitin', $suitable);
        $this->assertContains('Ibuprofen 200 mg', $caution);
        $this->assertNotContains('Ibuprofen 200 mg', $suitable);
        $this->assertNotContains('Koyo Capsicum (Plester Hangat)', $suitable->merge($caution)); // bukan untuk sendi

        // Dari layanan: Terapi Stroke -> vitamin saraf
        $stroke = $this->get('/obat?layanan=terapi-stroke')->viewData('suitable')->pluck('name');
        $this->assertContains('Vitamin B1, B6, B12 (Neurotropik)', $stroke);

        $this->get('/obat')->assertSee('Obat Keras (Resep)')->assertSee('bukan pengganti resep');
    }

    public function test_medicine_photos_match_their_names(): void
    {
        $withPhoto = Medicine::whereNotNull('image')->get();
        $this->assertCount(Medicine::count(), $withPhoto); // semua obat punya foto

        foreach ($withPhoto as $m) {
            // Nama file foto = slug nama obat, jadi foto tidak tertukar.
            $this->assertSame($m->slug.'.png', $m->image, $m->name);
            $this->assertFileExists(public_path('assets/img/obat/'.$m->image));
        }

        $this->get('/obat')->assertSee('assets/img/obat/paracetamol-500-mg.png')
            ->assertSee('alt="Kemasan Paracetamol 500 mg"', false)
            ->assertSee('alt="Kemasan Meloxicam 7,5 mg"', false);

        // Obat tanpa foto tetap tampil dengan ikon.
        Medicine::where('slug', 'meloxicam-75-mg')->update(['image' => null]);
        $this->get('/obat')->assertOk()->assertDontSee('alt="Kemasan Meloxicam 7,5 mg"', false);
    }

    public function test_admin_manages_medicines(): void
    {
        $this->actingAs($this->admin())->post('/admin/obat', [
            'name' => 'Kompres Gel Dingin', 'golongan' => 'bebas', 'kategori' => 'Alat bantu', 'bentuk' => 'Gel pack',
            'harga' => 30000, 'satuan' => '1 buah',
            'kegunaan' => 'Mengurangi bengkak', 'dosis' => '15 menit', 'peringatan' => 'Lapisi kain',
            'kebutuhan' => ['cedera', 'radang'], 'icon' => 'fa-bandage', 'is_active' => 1,
        ])->assertRedirect('/admin/obat');

        $m = Medicine::where('slug', 'kompres-gel-dingin')->first();
        $this->get('/obat?kebutuhan[]=cedera')->assertSee('Kompres Gel Dingin');

        $this->actingAs($this->admin())->patch("/admin/obat/{$m->id}/toggle");
        $this->get('/obat')->assertDontSee('<h5 class="mb-1">Kompres Gel Dingin</h5>', false);

        $this->actingAs($this->admin())->post('/admin/obat', ['name' => 'X', 'golongan' => 'ilegal'])
            ->assertSessionHasErrors(['golongan', 'kebutuhan']);

        $this->actingAs(User::factory()->create())->get('/admin/obat')->assertRedirect('/dashboard');
    }

    public function test_new_pages_render_for_each_role(): void
    {
        $booking = $this->booking(['therapist_id' => Therapist::first()->id]);

        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/booking', '/admin/pembayaran', '/admin/obat', '/admin/obat/baru', '/admin/terapis', '/admin/terapis/'.Therapist::first()->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs($booking->user);
        foreach (['/dashboard', '/riwayat-booking', "/booking/{$booking->id}", '/terapis', '/obat'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs(Therapist::first()->user);
        foreach (['/panel-terapis', '/panel-terapis/jadwal', "/panel-terapis/pasien/{$booking->id}", '/obat', '/ubah-password'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
