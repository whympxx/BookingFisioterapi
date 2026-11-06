<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Terapi Lutut', 'slug' => 'terapi-lutut',
                'description' => 'Perawatan khusus untuk masalah pada lutut, termasuk nyeri, cedera, dan pemulihan pasca operasi.',
                'features' => ['Penanganan nyeri lutut', 'Rehabilitasi pasca operasi', 'Penguatan otot lutut', 'Konsultasi ahli'],
                'duration' => '60-90 menit', 'price' => 150000, 'image' => 'terapi_lutut.jpg',
                'icon' => 'fa-person-walking', 'is_featured' => true,
            ],
            [
                'name' => 'Rehabilitasi Pasca Cedera', 'slug' => 'rehabilitasi-pasca-cedera',
                'description' => 'Program pemulihan komprehensif untuk cedera olahraga, kecelakaan, atau kondisi medis lainnya.',
                'features' => ['Program pemulihan personal', 'Terapi manual', 'Latihan fungsional', 'Monitoring progress'],
                'duration' => '90-120 menit', 'price' => 200000, 'image' => 'rehab_cedera.jpg',
                'icon' => 'fa-heart-pulse', 'is_featured' => true, 'is_popular' => true,
            ],
            [
                'name' => 'Pijat Medis', 'slug' => 'pijat-medis',
                'description' => 'Pijat terapeutik untuk meredakan ketegangan otot, meningkatkan sirkulasi darah, dan relaksasi.',
                'features' => ['Pijat relaksasi', 'Pijat terapeutik', 'Reduksi ketegangan', 'Peningkatan fleksibilitas'],
                'duration' => '60 menit', 'price' => 120000, 'image' => 'pijat_medis.jpg',
                'icon' => 'fa-hands', 'is_featured' => true,
            ],
            [
                'name' => 'Terapi Tulang Belakang', 'slug' => 'terapi-tulang-belakang',
                'description' => 'Penanganan khusus untuk masalah tulang belakang seperti nyeri punggung, skoliosis, dan herniated disc.',
                'features' => ['Penanganan nyeri punggung', 'Terapi skoliosis', 'Koreksi postur', 'Penguatan otot inti'],
                'duration' => '60-90 menit', 'price' => 180000, 'image' => 'terapi_tulang_belakang.jpg',
                'icon' => 'fa-bone',
            ],
            [
                'name' => 'Terapi Stroke', 'slug' => 'terapi-stroke',
                'description' => 'Program rehabilitasi komprehensif untuk pasien pasca stroke dengan fokus pada pemulihan fungsi motorik.',
                'features' => ['Rehabilitasi motorik', 'Latihan keseimbangan', 'Terapi wicara', 'Aktivitas sehari-hari'],
                'duration' => '90-120 menit', 'price' => 250000, 'image' => 'terapi_stroke.jpg',
                'icon' => 'fa-brain',
            ],
            [
                'name' => 'Terapi Anak', 'slug' => 'terapi-anak',
                'description' => 'Layanan fisioterapi khusus untuk anak-anak dengan gangguan perkembangan motorik dan sensorik.',
                'features' => ['Terapi perkembangan', 'Latihan motorik', 'Sensori integrasi', 'Kerjasama orang tua'],
                'duration' => '45-60 menit', 'price' => 160000, 'image' => 'terapi_anak.jpg',
                'icon' => 'fa-child',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }

        // Contoh data fisioterapis; nama dan profil bisa diubah admin di menu Terapis.
        $therapists = [
            ['name' => 'Ftr. Andini Pratiwi', 'specialization' => 'Muskuloskeletal & Olahraga', 'experience_years' => 8,
                'bio' => 'Menangani cedera olahraga, nyeri lutut, dan rehabilitasi pasca operasi.'],
            ['name' => 'Ftr. Bima Saputra', 'specialization' => 'Neurologi (Stroke)', 'experience_years' => 10,
                'bio' => 'Fokus pada pemulihan fungsi motorik pasien pasca stroke.'],
            ['name' => 'Ftr. Citra Lestari', 'specialization' => 'Pediatri (Anak)', 'experience_years' => 6,
                'bio' => 'Terapi tumbuh kembang dan sensori integrasi untuk anak.'],
            ['name' => 'Ftr. Dimas Hidayat', 'specialization' => 'Tulang Belakang & Postur', 'experience_years' => 7,
                'bio' => 'Penanganan nyeri punggung, skoliosis, dan koreksi postur.'],
        ];

        foreach ($therapists as $therapist) {
            $model = Therapist::firstOrCreate(['name' => $therapist['name']], $therapist);

            // Akun login halaman terapis: username = nama depan, password awal terapis123.
            if (! $model->user_id) {
                $username = strtolower(explode(' ', preg_replace('/^Ftr\.\s*/', '', $model->name))[0]);
                $user = User::where('username', $username)->first();
                if (! $user) {
                    $user = new User([
                        'username' => $username,
                        'nama_lengkap' => $model->name,
                        'email' => $username.'@fisioterapiunggul.com',
                        'password' => 'terapis123',
                    ]);
                    $user->role = 'terapis';
                    $user->save();
                }
                if ($user->role === 'terapis') {
                    $model->user_id = $user->id;
                    $model->save();
                }
            }
        }

        $this->call(MedicineSeeder::class);

        // Akun admin awal. Ganti password setelah login pertama.
        if (! User::where('username', 'admin')->exists()) {
            $admin = new User([
                'username' => 'admin',
                'email' => 'admin@fisioterapiunggul.com',
                'password' => 'admin123',
            ]);
            $admin->role = 'admin';
            $admin->save();
        }
    }
}
