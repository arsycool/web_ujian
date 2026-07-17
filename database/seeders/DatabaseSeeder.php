<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tidak ada data wajib di-seed. Soal ujian disimpan statis di App\Data\QuestionBank.
        // Akun pengguna dibuat otomatis saat login pertama kali (lihat AuthController).
    }
}
