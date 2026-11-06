<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MedicineController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TherapistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TherapistPanelController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', [PageController::class, 'home'])->name('home');
// File unggahan yang disimpan di database (dipakai saat deploy di Vercel).
Route::get('/berkas/{upload}/{name}', [UploadController::class, 'show'])->whereNumber('upload')->name('upload.show');
Route::get('/layanan', [PageController::class, 'layanan'])->name('layanan');
Route::get('/terapis', [PageController::class, 'therapists'])->name('therapists.index');
Route::get('/terapis/{therapist}', [PageController::class, 'therapist'])->whereNumber('therapist')->name('therapists.show');
Route::get('/obat', [PageController::class, 'medicines'])->name('medicines.index');

// Notifikasi pembayaran dari Midtrans (server-ke-server).
Route::post('/pembayaran/notifikasi', [PaymentController::class, 'notification'])
    ->middleware('throttle:60,1')->name('payment.notification');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/lupa-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'email'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/ubah-password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/ubah-password', [ProfileController::class, 'updatePassword']);

    // Halaman pasien
    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
        Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
        Route::get('/booking/slot', [BookingController::class, 'slots'])->name('booking.slots');
        Route::get('/booking/{booking}', [BookingController::class, 'show'])->whereNumber('booking')->name('booking.show');
        Route::patch('/booking/{booking}/batal', [BookingController::class, 'cancel'])->name('booking.cancel');
        Route::post('/booking/{booking}/ulasan', [BookingController::class, 'review'])->name('booking.review');
        Route::get('/booking/{booking}/bayar', [PaymentController::class, 'show'])->name('payment.show');
        Route::post('/booking/{booking}/bayar', [PaymentController::class, 'store'])->middleware('throttle:20,1')->name('payment.store');
        Route::get('/booking/{booking}/bayar/status', [PaymentController::class, 'status'])->name('payment.status');
        Route::post('/booking/{booking}/bayar/{payment}/bukti', [PaymentController::class, 'uploadProof'])->name('payment.proof');
        Route::patch('/booking/{booking}/bayar/{payment}/ganti', [PaymentController::class, 'cancel'])->name('payment.cancel');
        Route::post('/booking/{booking}/bayar/{payment}/simulasi', [PaymentController::class, 'simulate'])->name('payment.simulate');
        Route::get('/riwayat-booking', [BookingController::class, 'index'])->name('booking.index');
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profil/foto', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    });

    // Halaman khusus fisioterapis
    Route::middleware('role:terapis')->prefix('panel-terapis')->name('panel.')->group(function () {
        Route::get('/', [TherapistPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/jadwal', [TherapistPanelController::class, 'schedule'])->name('schedule');
        Route::get('/pasien/{booking}', [TherapistPanelController::class, 'show'])->name('patient');
        Route::put('/pasien/{booking}', [TherapistPanelController::class, 'update'])->name('patient.update');
    });

    // Halaman admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/booking', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('/booking/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
        Route::post('/booking/{booking}/tunai', [AdminPaymentController::class, 'cash'])->name('bookings.cash');

        Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::patch('/pembayaran/{payment}/verifikasi', [AdminPaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('/pembayaran/{payment}/tolak', [AdminPaymentController::class, 'reject'])->name('payments.reject');

        Route::get('/obat', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/obat/baru', [MedicineController::class, 'create'])->name('medicines.create');
        Route::post('/obat', [MedicineController::class, 'store'])->name('medicines.store');
        Route::get('/obat/{medicine}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
        Route::put('/obat/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');
        Route::patch('/obat/{medicine}/toggle', [MedicineController::class, 'toggle'])->name('medicines.toggle');

        Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/layanan/baru', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/layanan', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/layanan/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/layanan/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::patch('/layanan/{service}/toggle', [ServiceController::class, 'toggle'])->name('services.toggle');

        Route::get('/terapis', [TherapistController::class, 'index'])->name('therapists.index');
        Route::get('/terapis/baru', [TherapistController::class, 'create'])->name('therapists.create');
        Route::post('/terapis', [TherapistController::class, 'store'])->name('therapists.store');
        Route::get('/terapis/{therapist}/edit', [TherapistController::class, 'edit'])->name('therapists.edit');
        Route::put('/terapis/{therapist}', [TherapistController::class, 'update'])->name('therapists.update');
        Route::patch('/terapis/{therapist}/toggle', [TherapistController::class, 'toggle'])->name('therapists.toggle');

        Route::get('/admin-baru', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/admin-baru', [AdminUserController::class, 'store'])->name('users.store');
    });
});
