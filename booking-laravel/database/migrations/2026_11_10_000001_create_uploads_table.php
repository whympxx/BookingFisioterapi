<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // File unggahan yang disimpan di database (UPLOAD_DRIVER=database), dipakai saat deploy di Vercel.
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('folder', 30);
            $table->string('name');
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->longText('data'); // isi file dalam base64
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
