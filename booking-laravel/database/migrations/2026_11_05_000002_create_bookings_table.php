<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // Booking terhubung ke akun pemesan, bukan dicocokkan lewat email.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('nama', 100);
            $table->string('email', 100);
            $table->string('no_hp', 20);
            $table->date('tanggal_booking');
            $table->time('waktu_booking');
            $table->text('catatan')->nullable();
            $table->enum('status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])->default('Menunggu');
            $table->timestamps();

            $table->index(['tanggal_booking', 'waktu_booking']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
