<?php

// Pembayaran online.
// - driver "demo": tanpa payment gateway. Nomor VA dan QR dibuat sendiri, dan ada tombol
//   "simulasikan pembayaran" untuk mencoba alur. Jangan dipakai saat website sudah online.
// - driver "midtrans": pembayaran sungguhan lewat Midtrans Snap. Isi MIDTRANS_SERVER_KEY dan
//   MIDTRANS_CLIENT_KEY di .env, lalu daftarkan URL notifikasi /pembayaran/notifikasi
//   di dashboard Midtrans.
return [
    'driver' => env('PAYMENT_DRIVER', 'demo'),

    // Batas waktu bayar sejak metode dipilih.
    'batas_jam' => 24,

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'production' => (bool) env('MIDTRANS_PRODUCTION', false),
    ],

    // Rekening klinik untuk transfer manual (bukti transfer diverifikasi admin).
    'rekening' => [
        ['bank' => 'BCA', 'nomor' => '123 456 7890', 'atas_nama' => 'Klinik Fisioterapi Unggul Sehat'],
        ['bank' => 'Mandiri', 'nomor' => '137 00 1234567 8', 'atas_nama' => 'Klinik Fisioterapi Unggul Sehat'],
    ],

    // `midtrans` = kode enabled_payments di Midtrans Snap.
    'metode' => [
        'qris' => ['label' => 'QRIS', 'grup' => 'QRIS & E-Wallet', 'icon' => 'fa-qrcode', 'tipe' => 'qr', 'midtrans' => 'other_qris',
            'info' => 'Bisa dibayar dengan GoPay, OVO, DANA, ShopeePay, LinkAja, atau m-banking.'],
        'gopay' => ['label' => 'GoPay', 'grup' => 'QRIS & E-Wallet', 'icon' => 'fa-wallet', 'tipe' => 'qr', 'midtrans' => 'gopay',
            'info' => 'Pindai QR dengan aplikasi Gojek / GoPay.'],
        'shopeepay' => ['label' => 'ShopeePay', 'grup' => 'QRIS & E-Wallet', 'icon' => 'fa-bag-shopping', 'tipe' => 'qr', 'midtrans' => 'shopeepay',
            'info' => 'Pindai QR dengan aplikasi Shopee.'],
        'bca_va' => ['label' => 'BCA Virtual Account', 'grup' => 'Virtual Account', 'icon' => 'fa-building-columns', 'tipe' => 'va', 'midtrans' => 'bca_va',
            'bank' => 'BCA', 'prefix' => '70012'],
        'bni_va' => ['label' => 'BNI Virtual Account', 'grup' => 'Virtual Account', 'icon' => 'fa-building-columns', 'tipe' => 'va', 'midtrans' => 'bni_va',
            'bank' => 'BNI', 'prefix' => '8810'],
        'bri_va' => ['label' => 'BRI Virtual Account', 'grup' => 'Virtual Account', 'icon' => 'fa-building-columns', 'tipe' => 'va', 'midtrans' => 'bri_va',
            'bank' => 'BRI', 'prefix' => '26215'],
        'mandiri_va' => ['label' => 'Mandiri Bill Payment', 'grup' => 'Virtual Account', 'icon' => 'fa-building-columns', 'tipe' => 'va', 'midtrans' => 'echannel',
            'bank' => 'Mandiri', 'prefix' => '89608'],
        'transfer' => ['label' => 'Transfer Bank Manual', 'grup' => 'Transfer Manual', 'icon' => 'fa-money-bill-transfer', 'tipe' => 'manual', 'midtrans' => null,
            'info' => 'Transfer ke rekening klinik lalu unggah bukti. Diverifikasi admin maks. 1×24 jam.'],
    ],
];
