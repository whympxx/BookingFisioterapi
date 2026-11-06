<?php

// Data pendukung halaman Obat & Anjuran.
// Informasi obat bersifat edukasi umum, bukan pengganti resep atau saran dokter/apoteker.
return [
    'golongan' => [
        'bebas' => ['label' => 'Obat Bebas', 'warna' => 'success', 'info' => 'Bisa dibeli tanpa resep.'],
        'bebas_terbatas' => ['label' => 'Obat Bebas Terbatas', 'warna' => 'info', 'info' => 'Tanpa resep, perhatikan peringatan di kemasan.'],
        'suplemen' => ['label' => 'Suplemen / Vitamin', 'warna' => 'soft', 'info' => 'Pendukung, bukan pengganti terapi.'],
        'keras' => ['label' => 'Obat Keras (Resep)', 'warna' => 'danger', 'info' => 'Hanya dengan resep dokter.'],
    ],

    // Keluhan / kebutuhan pasien fisioterapi.
    'kebutuhan' => [
        'nyeri-sendi' => ['label' => 'Nyeri sendi & lutut', 'icon' => 'fa-person-walking'],
        'nyeri-otot' => ['label' => 'Nyeri & kaku otot', 'icon' => 'fa-hand-dots'],
        'punggung' => ['label' => 'Nyeri punggung & leher', 'icon' => 'fa-bone'],
        'cedera' => ['label' => 'Cedera olahraga / terkilir', 'icon' => 'fa-person-running'],
        'radang' => ['label' => 'Bengkak & radang', 'icon' => 'fa-fire'],
        'saraf' => ['label' => 'Kesemutan & kebas (saraf)', 'icon' => 'fa-bolt'],
        'tulang' => ['label' => 'Kesehatan tulang', 'icon' => 'fa-shield-heart'],
        'stroke' => ['label' => 'Pemulihan pasca stroke', 'icon' => 'fa-brain'],
    ],

    // Kondisi yang membuat obat tertentu perlu dihindari / dikonsultasikan dulu.
    'kondisi' => [
        'hamil' => 'Hamil atau menyusui',
        'maag' => 'Sakit maag / tukak lambung',
        'ginjal' => 'Gangguan ginjal / batu ginjal',
        'hati' => 'Gangguan hati / rutin minum alkohol',
        'jantung' => 'Penyakit jantung / hipertensi',
        'pengencer-darah' => 'Minum obat pengencer darah',
        'asma' => 'Asma',
        'alergi-kerang' => 'Alergi kerang / seafood',
        'anak' => 'Untuk anak di bawah 12 tahun',
    ],

    // Kebutuhan yang umum untuk tiap layanan (dipakai di tiket booking).
    'kebutuhan_layanan' => [
        'terapi-lutut' => ['nyeri-sendi', 'radang'],
        'rehabilitasi-pasca-cedera' => ['cedera', 'radang', 'tulang'],
        'pijat-medis' => ['nyeri-otot'],
        'terapi-tulang-belakang' => ['punggung', 'saraf'],
        'terapi-stroke' => ['stroke', 'saraf'],
        'terapi-anak' => [],
    ],

    'icons' => [
        'fa-tablets' => 'Tablet', 'fa-capsules' => 'Kapsul', 'fa-pills' => 'Pil',
        'fa-pump-medical' => 'Gel / krim', 'fa-bandage' => 'Plester / koyo',
        'fa-prescription-bottle-medical' => 'Botol obat', 'fa-jar' => 'Balsam',
    ],
];
