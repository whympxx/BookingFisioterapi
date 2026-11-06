<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->unsignedInteger('harga')->default(0)->after('bentuk');
            $table->string('satuan', 60)->nullable()->after('harga'); // mis. "strip 10 tablet"
        });

        // Obat yang dianjurkan terapis ikut ditagih: simpan jumlah dan harga saat dianjurkan.
        Schema::table('booking_medicine', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah')->default(1)->after('medicine_id');
            $table->unsignedInteger('harga')->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('booking_medicine', fn (Blueprint $table) => $table->dropColumn(['jumlah', 'harga']));
        Schema::table('medicines', fn (Blueprint $table) => $table->dropColumn(['harga', 'satuan']));
    }
};
