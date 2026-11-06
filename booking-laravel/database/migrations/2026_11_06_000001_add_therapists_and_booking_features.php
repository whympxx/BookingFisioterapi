<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('specialization', 100);
            $table->unsignedTinyInteger('experience_years')->default(1);
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap', 100)->nullable()->after('username');
            $table->string('no_hp', 20)->nullable()->after('email');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_popular');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('kode_booking', 20)->nullable()->unique()->after('id');
            $table->foreignId('therapist_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable()->after('status');
            $table->text('ulasan')->nullable()->after('rating');
            $table->timestamp('rated_at')->nullable()->after('ulasan');
        });

        // Beri kode untuk booking yang sudah ada.
        DB::table('bookings')->whereNull('kode_booking')->orderBy('id')->each(function ($b) {
            DB::table('bookings')->where('id', $b->id)->update([
                'kode_booking' => 'FIS-'.date('ymd', strtotime($b->created_at ?? 'now')).'-'.strtoupper(Str::random(4)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('therapist_id');
            $table->dropUnique(['kode_booking']);
            $table->dropColumn(['kode_booking', 'rating', 'ulasan', 'rated_at']);
        });
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('is_active'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['nama_lengkap', 'no_hp']));
        Schema::dropIfExists('therapists');
    }
};
