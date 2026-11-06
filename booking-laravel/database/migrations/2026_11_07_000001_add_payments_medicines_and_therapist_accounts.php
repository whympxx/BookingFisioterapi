<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Role baru "terapis" untuk akun fisioterapis.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('user')->change();
        });

        Schema::table('therapists', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Harga dikunci saat booking dibuat, supaya perubahan harga layanan tidak mengubah tagihan lama.
            $table->unsignedInteger('harga')->nullable()->after('waktu_booking');
            $table->string('status_pembayaran', 30)->default('Belum Bayar')->after('status');
            $table->text('catatan_terapis')->nullable()->after('catatan');
        });

        DB::table('bookings')->orderBy('id')->each(function ($b) {
            DB::table('bookings')->where('id', $b->id)->update([
                'harga' => DB::table('services')->where('id', $b->service_id)->value('price'),
            ]);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('order_id', 40)->unique();
            $table->string('method', 30);
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('pending'); // pending, paid, failed, expired
            $table->string('va_number', 40)->nullable();
            $table->text('qr_string')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('proof')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('golongan', 20);
            $table->string('kategori', 80);
            $table->string('bentuk', 60);
            $table->text('kegunaan');
            $table->text('dosis');
            $table->text('peringatan');
            $table->json('kebutuhan');
            $table->json('hindari_jika')->nullable();
            $table->string('icon', 50)->default('fa-pills');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('booking_medicine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('aturan_pakai', 255)->nullable();
            $table->timestamps();
            $table->unique(['booking_id', 'medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_medicine');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('payments');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['harga', 'status_pembayaran', 'catatan_terapis']));
        Schema::table('therapists', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        DB::table('users')->where('role', 'terapis')->update(['role' => 'user']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->change();
        });
    }
};
