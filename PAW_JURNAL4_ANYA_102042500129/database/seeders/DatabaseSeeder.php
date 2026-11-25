<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\MataKuliah;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Dosen Pengampu Default
        $dosen = User::updateOrCreate(
            ['email' => 'dosen@celoe.test'],
            [
                'name' => 'Dr. Ir. Budi Santoso, M.T.',
                'password' => Hash::make('dosen123'),
                'role' => 'dosen',
                'nomor_induk' => '198501012015041001',
            ]
        );

        // 2. Akun Mahasiswa Default
        $mahasiswa1 = User::updateOrCreate(
            ['email' => 'mahasiswa@celoe.test'],
            [
                'name' => 'Ahmad Pratama',
                'password' => Hash::make('mahasiswa123'),
                'role' => 'mahasiswa',
                'nomor_induk' => '1301210001',
            ]
        );

        // 3. Mata Kuliah Dasar (Modul 3)
        $course1 = MataKuliah::updateOrCreate(
            ['kode_mk' => 'IF301'],
            [
                'nama_mk' => 'Pengembangan Aplikasi Website',
                'deskripsi' => 'Mata kuliah yang membahas arsitektur aplikasi web modern menggunakan framework Laravel, MVC, Eloquent ORM, dan keamanan web.',
                'sks' => 4,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen->id,
            ]
        );

        // 4. Daftarkan Mahasiswa ke Mata Kuliah Pertama (status approved)
        $course1->students()->syncWithoutDetaching([
            $mahasiswa1->id => ['status' => 'approved'],
        ]);

        // 5. Tambahkan Materi Contoh
        Material::updateOrCreate(
            ['course_id' => $course1->id, 'minggu_ke' => 1, 'urutan' => 1],
            [
                'judul' => 'Pengenalan Arsitektur MVC & Routing di Laravel 12',
                'konten' => 'Mempelajari alur HTTP Request, Route Middleware, Controller, dan pembuatan View menggunakan Blade Template.',
            ]
        );

         
// 6. Akun Mahasiswa Tambahan
$mahasiswa2 = User::updateOrCreate(
    ['email' => 'mahasiswa2@celoe.test'],
    [
        'name' => 'Siti Nurhaliza',
        'password' => Hash::make('mahasiswa123'),
        'role' => 'mahasiswa',
        'nomor_induk' => '1301210002',
    ]
);

// 7. Mata Kuliah Tambahan
$course2 = MataKuliah::updateOrCreate(
    ['kode_mk' => 'IF302'],
    [
        'nama_mk' => 'Pemrograman Web Lanjut',
        'deskripsi' => 'Mempelajari pengembangan aplikasi web menggunakan teknologi modern.',
        'sks' => 3,
        'semester' => 'Genap 2026/2027',
        'dosen_id' => $dosen->id,
    ]
);

$course3 = MataKuliah::updateOrCreate(
    ['kode_mk' => 'IF303'],
    [
        'nama_mk' => 'Basis Data Lanjut',
        'deskripsi' => 'Mempelajari pengelolaan dan pengembangan basis data.',
        'sks' => 3,
        'semester' => 'Ganjil 2025/2026',
        'dosen_id' => $dosen->id,
    ]
);

// 8. Daftarkan Mahasiswa ke Mata Kuliah
$course2->students()->syncWithoutDetaching([
    $mahasiswa2->id => ['status' => 'approved'],
]);

$course3->students()->syncWithoutDetaching([
    $mahasiswa1->id => ['status' => 'approved'],
]);

// 9. Tambahkan Dua Tugas
$tugas1 = Assignment::updateOrCreate(
    [
        'course_id' => $course1->id,
        'judul' => 'Tugas Praktikum Laravel',
    ],
    [
        'deskripsi' => 'Buat aplikasi CRUD sederhana menggunakan Laravel.',
        'deadline' => now()->addDays(7),
    ]
);

$tugas2 = Assignment::updateOrCreate(
    [
        'course_id' => $course2->id,
        'judul' => 'Tugas Pemrograman Web',
    ],
    [
        'deskripsi' => 'Buat halaman web interaktif menggunakan framework Laravel.',
        'deadline' => now()->addDays(10),
    ]
);

// 10. Tambahkan Contoh Pengumpulan Tugas
Submission::updateOrCreate(
    [
        'assignment_id' => $tugas1->id,
        'user_id' => $mahasiswa1->id,
    ],
    [
        'jawaban' => 'https://github.com/mahasiswa/tugas-laravel',
        'submitted_at' => now()->subDays(2),
    ]
);

Submission::updateOrCreate(
    [
        'assignment_id' => $tugas2->id,
        'user_id' => $mahasiswa2->id,
    ],
    [
        'jawaban' => 'https://github.com/mahasiswa2/tugas-web',
        'submitted_at' => now()->subDays(1),
    ]
);

        }
}
