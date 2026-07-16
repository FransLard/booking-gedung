<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\Booking;
use App\Models\Gedung;
use App\Models\GedungImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'User Biasa',
            'email'    => 'user@example.com',
            'password' => bcrypt('user123'),
            'role'     => 'user',
        ]);

        $extraUsers = [
            ['name' => 'Budi Santoso',   'email' => 'budi@example.com'],
            ['name' => 'Siti Rahayu',    'email' => 'siti@example.com'],
            ['name' => 'Ahmad Hidayat',  'email' => 'ahmad@example.com'],
            ['name' => 'Dewi Lestari',   'email' => 'dewi@example.com'],
            ['name' => 'Rudi Hermawan',  'email' => 'rudi@example.com'],
            ['name' => 'Ani Wijaya',     'email' => 'ani@example.com'],
            ['name' => 'Deni Kurniawan', 'email' => 'deni@example.com'],
            ['name' => 'Fitri Handayani','email' => 'fitri@example.com'],
        ];

        foreach ($extraUsers as $u) {
            User::create([
                'name'     => $u['name'],
                'email'    => $u['email'],
                'password' => bcrypt('password'),
                'role'     => 'user',
            ]);
        }

        Gedung::insert([
            [
                'nama'       => 'Gedung Serbaguna A',
                'alamat'     => 'Jl. Merdeka No. 1',
                'deskripsi'  => 'Gedung luas dengan kapasitas 500 orang, cocok untuk seminar dan konser. Dilengkapi AC, sound system, dan panggung permanen.',
                'kapasitas'  => 500,
                'harga_sewa' => 5000000,
                'gambar'     => 'https://picsum.photos/seed/gedung-a-main/800/600',
            ],
            [
                'nama'       => 'Gedung Pertemuan B',
                'alamat'     => 'Jl. Sudirman No. 45',
                'deskripsi'  => 'Ruang meeting premium untuk 50 orang dengan fasilitas lengkap. Proyektor HD, video conference, dan coffee corner.',
                'kapasitas'  => 50,
                'harga_sewa' => 1500000,
                'gambar'     => 'https://picsum.photos/seed/gedung-b-main/800/600',
            ],
            [
                'nama'       => 'Aula Serbaguna C',
                'alamat'     => 'Jl. Diponegoro No. 22',
                'deskripsi'  => 'Aula untuk resepsi pernikahan dan acara besar hingga 1000 orang. Dilengkapi lighting, panggung, dan sound system profesional.',
                'kapasitas'  => 1000,
                'harga_sewa' => 10000000,
                'gambar'     => 'https://picsum.photos/seed/gedung-c-main/800/600',
            ],
            [
                'nama'       => 'Ruang Rapat D',
                'alamat'     => 'Jl. Gatot Subroto No. 10',
                'deskripsi'  => 'Ruang rapat modern untuk 20 orang dengan proyektor dan sound system. Cocok untuk rapat direksi dan presentasi klien.',
                'kapasitas'  => 20,
                'harga_sewa' => 800000,
                'gambar'     => 'https://picsum.photos/seed/gedung-d-main/800/600',
            ],
        ]);

        $gedungImages = [];
        $imgIdx = 1;
        $gedungIds = range(1, 4);
        $captions = ['Tampak depan', 'Ruang utama', 'Area panggung', 'Lobby', 'Dekorasi interior', 'Area parkir', 'Fasilitas lengkap', 'Sudut ruangan', 'Tampak samping', 'Area belakang'];

        foreach ($gedungIds as $gid) {
            $count = rand(3, 5);
            for ($i = 1; $i <= $count; $i++) {
                $gedungImages[] = [
                    'gedung_id'  => $gid,
                    'gambar'     => "https://picsum.photos/seed/gedung-{$gid}-{$i}/800/600",
                    'keterangan' => $captions[array_rand($captions)],
                    'urutan'     => $i,
                ];
                $imgIdx++;
            }
        }

        GedungImage::insert($gedungImages);

        AddOn::insert([
            ['nama' => 'Sound System',   'deskripsi' => 'Speaker dan mixer profesional',           'harga' => 500000],
            ['nama' => 'Proyektor',      'deskripsi' => 'Proyektor HD dengan layar 120 inci',      'harga' => 300000],
            ['nama' => 'Catering',       'deskripsi' => 'Paket snack dan minuman untuk 50 orang',  'harga' => 250000],
            ['nama' => 'Dekorasi',       'deskripsi' => 'Dekorasi panggung dan ruangan elegam',    'harga' => 750000],
            ['nama' => 'Fotografer',     'deskripsi' => 'Dokumentasi foto profesional',             'harga' => 600000],
            ['nama' => 'Live Music',     'deskripsi' => 'Band akustik 3 orang untuk acara',        'harga' => 1500000],
            ['nama' => 'LED Wall',       'deskripsi' => 'Layar LED besar 4K untuk presentasi',     'harga' => 2000000],
            ['nama' => 'Meeting Kit',    'deskripsi' => 'ATK, flipchart, dan alat tulis lengkap',  'harga' => 150000],
        ]);

        $statusOptions = ['confirmed', 'confirmed', 'confirmed', 'pending', 'cancelled'];
        $paymentOptions = ['paid_lunas', 'paid_lunas', 'paid_dp', 'unpaid', 'unpaid'];
        $userIds = range(2, 9);
        $gedungIds = range(1, 4);
        $jamList = [
            ['08:00', '17:00'],
            ['09:00', '12:00'],
            ['13:00', '17:00'],
            ['08:00', '12:00'],
            ['14:00', '21:00'],
            ['10:00', '16:00'],
            ['07:00', '22:00'],
            ['09:00', '14:00'],
            ['15:00', '20:00'],
            ['11:00', '15:00'],
        ];

        $bookings = [];
        $bookingCounter = 1;

        for ($m = -5; $m <= 2; $m++) {
            $monthStart = now()->addMonths($m)->startOfMonth();
            $monthEnd = now()->addMonths($m)->endOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            $bookingCount = $m < 0 ? rand(3, 6) : rand(1, 3);

            for ($b = 0; $b < $bookingCount; $b++) {
                $day = rand(1, $daysInMonth);
                $date = $monthStart->copy()->addDays($day - 1);

                $userId = $userIds[array_rand($userIds)];
                $gedungId = $gedungIds[array_rand($gedungIds)];
                $jamIdx = array_rand($jamList);
                $jamMulai = $jamList[$jamIdx][0];
                $jamSelesai = $jamList[$jamIdx][1];

                if ($m < 0) {
                    $status = $statusOptions[array_rand($statusOptions)];
                    $payment = in_array($status, ['cancelled']) ? 'unpaid' : $paymentOptions[array_rand($paymentOptions)];
                } elseif ($m == 0) {
                    $status = rand(0, 2) ? 'confirmed' : 'pending';
                    $payment = $status === 'confirmed' ? $paymentOptions[array_rand($paymentOptions)] : 'unpaid';
                } else {
                    $status = 'pending';
                    $payment = 'unpaid';
                }

                $gedung = Gedung::find($gedungId);
                $hargaGedung = $gedung->harga_sewa;

                $addonPrice = [0, 300000, 500000, 750000];
                if (rand(0, 1)) {
                    $hargaGedung += $addonPrice[array_rand($addonPrice)];
                }

                $createdAt = $date->copy()->subDays(rand(1, 14));
                if ($createdAt > now()) {
                    $createdAt = now()->subDays(rand(1, 5));
                }

                $bookings[] = [
                    'booking_code'   => sprintf('BK-2025-%04d', $bookingCounter),
                    'user_id'        => $userId,
                    'gedung_id'      => $gedungId,
                    'tanggal'        => $date->format('Y-m-d'),
                    'jam_mulai'      => $jamMulai,
                    'jam_selesai'    => $jamSelesai,
                    'total_harga'    => $hargaGedung,
                    'payment_type'   => $payment === 'paid_lunas' ? 'lunas' : ($payment === 'paid_dp' ? 'dp' : 'dp'),
                    'payment_status' => $payment,
                    'status'         => $status,
                    'created_at'     => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at'     => $createdAt->format('Y-m-d H:i:s'),
                ];

                $bookingCounter++;
            }
        }

        foreach ($bookings as $data) {
            Booking::create($data);
        }

        $userBiasaBookings = [
            [
                'booking_code'   => 'BKG-SEED-0001',
                'user_id'        => 2,
                'gedung_id'      => 1,
                'tanggal'        => now()->addDays(3)->format('Y-m-d'),
                'jam_mulai'      => '09:00',
                'jam_selesai'    => '17:00',
                'total_harga'    => 5000000,
                'payment_type'   => 'dp',
                'payment_status' => 'paid_dp',
                'status'         => 'confirmed',
                'created_at'     => now()->subDays(2)->format('Y-m-d H:i:s'),
                'updated_at'     => now()->subDays(2)->format('Y-m-d H:i:s'),
            ],
            [
                'booking_code'   => 'BKG-SEED-0002',
                'user_id'        => 2,
                'gedung_id'      => 2,
                'tanggal'        => now()->addDays(10)->format('Y-m-d'),
                'jam_mulai'      => '13:00',
                'jam_selesai'    => '17:00',
                'total_harga'    => 1500000,
                'payment_type'   => 'lunas',
                'payment_status' => 'paid_lunas',
                'status'         => 'confirmed',
                'created_at'     => now()->subDays(7)->format('Y-m-d H:i:s'),
                'updated_at'     => now()->subDays(7)->format('Y-m-d H:i:s'),
            ],
            [
                'booking_code'   => 'BKG-SEED-0003',
                'user_id'        => 2,
                'gedung_id'      => 3,
                'tanggal'        => now()->subDays(5)->format('Y-m-d'),
                'jam_mulai'      => '08:00',
                'jam_selesai'    => '12:00',
                'total_harga'    => 10000000,
                'payment_type'   => 'lunas',
                'payment_status' => 'paid_lunas',
                'status'         => 'confirmed',
                'created_at'     => now()->subDays(20)->format('Y-m-d H:i:s'),
                'updated_at'     => now()->subDays(20)->format('Y-m-d H:i:s'),
            ],
        ];

        foreach ($userBiasaBookings as $data) {
            Booking::create($data);
        }
    }
}
