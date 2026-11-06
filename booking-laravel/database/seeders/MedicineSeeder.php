<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Daftar obat & suplemen yang umum dianjurkan untuk keluhan pasien fisioterapi.
 * Dosis yang ditulis adalah dosis umum dewasa sesuai label; ikuti anjuran dokter/apoteker.
 */
class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Paracetamol 500 mg', 'golongan' => 'bebas', 'kategori' => 'Pereda nyeri', 'bentuk' => 'Tablet', 'icon' => 'fa-tablets',
                'kegunaan' => 'Meredakan nyeri ringan–sedang (otot, sendi, punggung) dan demam. Aman untuk lambung sehingga cocok sebagai pilihan pertama.',
                'dosis' => 'Dewasa: 1 tablet, 3–4 kali sehari bila nyeri. Jarak minimal 4–6 jam. Maksimal 8 tablet (4 gram) per hari.',
                'peringatan' => 'Jangan melebihi dosis maksimal dan jangan digabung dengan obat lain yang juga mengandung paracetamol. Hati-hati pada penyakit hati dan peminum alkohol.',
                'kebutuhan' => ['nyeri-sendi', 'nyeri-otot', 'punggung', 'cedera'],
                'hindari_jika' => ['hati'],
            ],
            [
                'name' => 'Ibuprofen 200 mg', 'golongan' => 'bebas_terbatas', 'kategori' => 'Anti-inflamasi (NSAID)', 'bentuk' => 'Tablet', 'icon' => 'fa-tablets',
                'kegunaan' => 'Meredakan nyeri disertai bengkak/radang, misalnya terkilir, nyeri otot setelah olahraga, atau nyeri sendi.',
                'dosis' => 'Dewasa: 1–2 tablet, 3 kali sehari sesudah makan. Maksimal 6 tablet (1.200 mg) per hari tanpa resep. Gunakan paling lama 3–5 hari.',
                'peringatan' => 'Minum sesudah makan. Dapat memicu nyeri/perdarahan lambung. Hindari bila ada maag, gangguan ginjal, penyakit jantung, asma yang kambuh karena obat nyeri, hamil, atau sedang minum pengencer darah.',
                'kebutuhan' => ['nyeri-otot', 'nyeri-sendi', 'cedera', 'radang', 'punggung'],
                'hindari_jika' => ['hamil', 'maag', 'ginjal', 'jantung', 'pengencer-darah', 'asma'],
            ],
            [
                'name' => 'Natrium Diklofenak Gel 1%', 'golongan' => 'bebas_terbatas', 'kategori' => 'Anti-inflamasi oles', 'bentuk' => 'Gel', 'icon' => 'fa-pump-medical',
                'kegunaan' => 'Gel pereda nyeri dan radang yang dioles langsung di area nyeri: lutut, punggung, bahu, atau bekas terkilir. Efek ke lambung jauh lebih kecil dibanding tablet.',
                'dosis' => 'Oleskan tipis 3–4 kali sehari di area nyeri, pijat perlahan. Cuci tangan sesudahnya. Gunakan paling lama 2 minggu tanpa saran dokter.',
                'peringatan' => 'Jangan dioles pada luka terbuka, mata, atau kulit iritasi. Jangan ditutup perban rapat atau dikombinasi kompres panas. Hindari pada kehamilan trimester akhir dan pada asma yang sensitif obat nyeri.',
                'kebutuhan' => ['nyeri-sendi', 'nyeri-otot', 'cedera', 'radang', 'punggung'],
                'hindari_jika' => ['hamil', 'asma'],
            ],
            [
                'name' => 'Krim Otot Metil Salisilat & Mentol', 'golongan' => 'bebas', 'kategori' => 'Pereda nyeri oles', 'bentuk' => 'Krim / balsam', 'icon' => 'fa-jar',
                'kegunaan' => 'Memberi sensasi hangat-dingin yang meredakan pegal, kaku, dan nyeri otot ringan, misalnya leher kaku atau pegal punggung.',
                'dosis' => 'Oleskan secukupnya 3–4 kali sehari di area pegal.',
                'peringatan' => 'Hanya untuk kulit utuh. Jangan dipakai bersama bantal/kompres panas. Tidak untuk anak kecil. Konsultasikan dulu bila minum obat pengencer darah (warfarin).',
                'kebutuhan' => ['nyeri-otot', 'punggung'],
                'hindari_jika' => ['pengencer-darah', 'anak'],
            ],
            [
                'name' => 'Koyo Capsicum (Plester Hangat)', 'golongan' => 'bebas', 'kategori' => 'Plester pereda nyeri', 'bentuk' => 'Plester', 'icon' => 'fa-bandage',
                'kegunaan' => 'Plester hangat untuk pegal dan kaku otot punggung, pinggang, dan bahu. Praktis dipakai di sela aktivitas.',
                'dosis' => 'Tempel 1 lembar di area pegal, maksimal 8 jam lalu lepas. Beri jeda sebelum menempel lagi di tempat yang sama.',
                'peringatan' => 'Jangan ditempel pada kulit luka, iritasi, atau dekat mata. Lepas bila kulit terasa sangat panas atau melepuh. Tidak untuk anak-anak.',
                'kebutuhan' => ['nyeri-otot', 'punggung'],
                'hindari_jika' => ['anak'],
            ],
            [
                'name' => 'Glukosamin & Kondroitin', 'golongan' => 'suplemen', 'kategori' => 'Suplemen sendi', 'bentuk' => 'Tablet / kapsul', 'icon' => 'fa-capsules',
                'kegunaan' => 'Suplemen pendukung untuk pengapuran sendi (osteoartritis) ringan–sedang, terutama lutut. Hasilnya bertahap, umumnya baru terasa setelah 4–8 minggu.',
                'dosis' => 'Glukosamin 1.500 mg per hari (sekaligus atau dibagi), sesuai label kemasan. Evaluasi setelah 3 bulan.',
                'peringatan' => 'Sebagian produk dibuat dari cangkang kerang. Konsultasikan bila minum pengencer darah (warfarin), memiliki diabetes, atau sedang hamil/menyusui.',
                'kebutuhan' => ['nyeri-sendi'],
                'hindari_jika' => ['alergi-kerang', 'pengencer-darah', 'hamil'],
            ],
            [
                'name' => 'Kalsium + Vitamin D3', 'golongan' => 'suplemen', 'kategori' => 'Suplemen tulang', 'bentuk' => 'Tablet', 'icon' => 'fa-tablets',
                'kegunaan' => 'Mendukung kepadatan tulang dan pemulihan pasca patah tulang, serta mencegah pengeroposan tulang pada lansia.',
                'dosis' => 'Umumnya 1 tablet (500–600 mg kalsium + 400–800 IU vitamin D3) 1–2 kali sehari sesudah makan, sesuai label.',
                'peringatan' => 'Minum terpisah 2 jam dari antibiotik atau suplemen zat besi. Konsultasikan bila punya riwayat batu ginjal atau gangguan ginjal.',
                'kebutuhan' => ['tulang', 'cedera'],
                'hindari_jika' => ['ginjal'],
            ],
            [
                'name' => 'Vitamin B1, B6, B12 (Neurotropik)', 'golongan' => 'suplemen', 'kategori' => 'Vitamin saraf', 'bentuk' => 'Tablet', 'icon' => 'fa-pills',
                'kegunaan' => 'Mendukung kesehatan saraf tepi; sering dianjurkan untuk keluhan kesemutan, kebas, atau saraf terjepit, serta pada masa pemulihan stroke.',
                'dosis' => '1 tablet sehari sesudah makan, sesuai label kemasan.',
                'peringatan' => 'Jangan melebihi dosis anjuran dalam jangka panjang: vitamin B6 dosis tinggi justru dapat menyebabkan kesemutan. Bila kesemutan makin berat atau disertai lemah anggota gerak, segera periksa ke dokter.',
                'kebutuhan' => ['saraf', 'stroke'],
                'hindari_jika' => [],
            ],
            [
                'name' => 'Omega-3 (Minyak Ikan)', 'golongan' => 'suplemen', 'kategori' => 'Suplemen umum', 'bentuk' => 'Kapsul lunak', 'icon' => 'fa-capsules',
                'kegunaan' => 'Mendukung kesehatan jantung dan pembuluh darah, serta dapat membantu mengurangi kaku sendi akibat peradangan.',
                'dosis' => '1–2 kapsul sehari sesudah makan, sesuai label.',
                'peringatan' => 'Konsultasikan bila minum obat pengencer darah atau akan menjalani operasi. Hentikan bila ada alergi ikan.',
                'kebutuhan' => ['stroke', 'nyeri-sendi'],
                'hindari_jika' => ['pengencer-darah'],
            ],
            [
                'name' => 'Eperisone HCl 50 mg', 'golongan' => 'keras', 'kategori' => 'Relaksan otot', 'bentuk' => 'Tablet', 'icon' => 'fa-prescription-bottle-medical',
                'kegunaan' => 'Mengurangi kejang dan kaku otot, misalnya pada nyeri punggung bawah akut atau kekakuan otot (spastisitas) pasca stroke.',
                'dosis' => 'Hanya sesuai resep dokter. Dosis umum dewasa: 1 tablet 3 kali sehari sesudah makan.',
                'peringatan' => 'Wajib resep dokter. Dapat menyebabkan kantuk dan lemas, jadi jangan mengemudi sesudah minum. Tidak dianjurkan saat hamil/menyusui atau pada gangguan hati.',
                'kebutuhan' => ['nyeri-otot', 'punggung', 'stroke'],
                'hindari_jika' => ['hamil', 'hati'],
            ],
            [
                'name' => 'Meloxicam 7,5 mg', 'golongan' => 'keras', 'kategori' => 'Anti-inflamasi (NSAID)', 'bentuk' => 'Tablet', 'icon' => 'fa-prescription-bottle-medical',
                'kegunaan' => 'Mengurangi nyeri dan radang jangka menengah pada pengapuran sendi atau radang sendi.',
                'dosis' => 'Hanya sesuai resep dokter. Umumnya 1 tablet sekali sehari sesudah makan.',
                'peringatan' => 'Wajib resep dokter. Risiko perdarahan lambung, gangguan ginjal, dan efek ke jantung, terutama pada lansia dan pemakaian lama.',
                'kebutuhan' => ['nyeri-sendi', 'radang'],
                'hindari_jika' => ['hamil', 'maag', 'ginjal', 'jantung', 'pengencer-darah', 'asma'],
            ],
            [
                'name' => 'Natrium Diklofenak 50 mg', 'golongan' => 'keras', 'kategori' => 'Anti-inflamasi (NSAID)', 'bentuk' => 'Tablet', 'icon' => 'fa-prescription-bottle-medical',
                'kegunaan' => 'Pereda nyeri dan radang yang lebih kuat untuk cedera akut, nyeri sendi, atau nyeri pasca tindakan.',
                'dosis' => 'Hanya sesuai resep dokter. Umumnya 1 tablet 2–3 kali sehari sesudah makan, dalam jangka pendek.',
                'peringatan' => 'Wajib resep dokter. Risiko gangguan lambung, ginjal, dan jantung. Jangan digabung dengan obat anti-inflamasi lain (misalnya ibuprofen).',
                'kebutuhan' => ['nyeri-sendi', 'radang', 'cedera'],
                'hindari_jika' => ['hamil', 'maag', 'ginjal', 'jantung', 'pengencer-darah', 'asma'],
            ],
        ];

        // Perkiraan harga eceran awal; ubah sesuai harga klinik di menu admin Obat.
        $prices = [
            'paracetamol-500-mg' => [5000, 'strip 10 tablet'],
            'ibuprofen-200-mg' => [8000, 'strip 10 tablet'],
            'natrium-diklofenak-gel-1' => [45000, 'tube 20 g'],
            'krim-otot-metil-salisilat-mentol' => [25000, 'tube 30 g'],
            'koyo-capsicum-plester-hangat' => [15000, 'kemasan 5 lembar'],
            'glukosamin-kondroitin' => [120000, 'botol 30 kapsul'],
            'kalsium-vitamin-d3' => [65000, 'botol 30 tablet'],
            'vitamin-b1-b6-b12-neurotropik' => [25000, 'strip 10 tablet'],
            'omega-3-minyak-ikan' => [85000, 'botol 30 kapsul'],
            'eperisone-hcl-50-mg' => [35000, 'strip 10 tablet'],
            'meloxicam-75-mg' => [20000, 'strip 10 tablet'],
            'natrium-diklofenak-50-mg' => [12000, 'strip 10 tablet'],
        ];

        foreach ($items as $item) {
            $slug = Str::slug($item['name']);
            $medicine = Medicine::updateOrCreate(['slug' => $slug], $item + ['is_active' => true]);

            // Harga hanya diisi bila belum ada, supaya harga yang diubah admin tidak tertimpa.
            if (! $medicine->harga && isset($prices[$slug])) {
                $medicine->update(['harga' => $prices[$slug][0], 'satuan' => $prices[$slug][1]]);
            }

            // Foto bawaan bernama sesuai slug di public/assets/img/obat. Foto unggahan admin tidak ditimpa.
            if (! $medicine->image && is_file(public_path("assets/img/obat/{$slug}.png"))) {
                $medicine->update(['image' => "{$slug}.png"]);
            }
        }
    }
}
