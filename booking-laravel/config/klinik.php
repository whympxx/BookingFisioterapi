<?php

// Info klinik dipakai di footer, chatbot, dan validasi jam booking,
// supaya tidak ada lagi data yang berbeda-beda antar halaman.
return [
    'nama' => 'Fisioterapi Unggul Sehat',
    'telepon' => '+62 812-3456-7890',
    'telepon_link' => '+6281234567890',
    'email' => 'info@fisioterapiunggul.com',
    'alamat' => 'Jl. Kesehatan No. 123, Jakarta',
    'biaya_home_visit' => 50000,
    'jarak_home_visit_km' => 15,

    // Booking dibagi per slot 1 jam. Satu slot bisa diisi beberapa pasien,
    // tapi satu fisioterapis hanya menangani satu pasien per slot.
    'kapasitas_per_slot' => 3,
    'durasi_slot_menit' => 60,

    // Jam operasional per hari (0 = Minggu ... 6 = Sabtu, sesuai Carbon::dayOfWeek).
    'jam_operasional' => [
        0 => ['09:00', '15:00'],
        1 => ['08:00', '20:00'],
        2 => ['08:00', '20:00'],
        3 => ['08:00', '20:00'],
        4 => ['08:00', '20:00'],
        5 => ['08:00', '20:00'],
        6 => ['08:00', '17:00'],
    ],

    'jam_operasional_label' => [
        'Senin - Jumat' => '08:00 - 20:00',
        'Sabtu' => '08:00 - 17:00',
        'Minggu' => '09:00 - 15:00',
    ],
];
