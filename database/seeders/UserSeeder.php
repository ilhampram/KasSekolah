<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with default accounts.
     */
    public function run(): void
    {
        // ── Admin (Bendahara Sekolah) ─────────────────────────────
        User::create([
            'name'     => 'Admin Bendahara',
            'email'    => 'admin@sekolah.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'class_id' => null,
            'nisn'     => null,
        ]);

        // ── Bendahara Kelas ───────────────────────────────────────
        User::create([
            'name'     => 'Rina Wulandari',
            'email'    => 'bendahara.xipa1@sekolah.test',
            'password' => Hash::make('password'),
            'role'     => 'bendahara_kelas',
            'class_id' => 1, // X IPA 1
            'nisn'     => null,
        ]);

        User::create([
            'name'     => 'Dani Pratama',
            'email'    => 'bendahara.xipa2@sekolah.test',
            'password' => Hash::make('password'),
            'role'     => 'bendahara_kelas',
            'class_id' => 2, // X IPA 2
            'nisn'     => null,
        ]);

        // ── Siswa ─────────────────────────────────────────────────
        $students = [
            ['name' => 'Andi Saputra',     'email' => 'andi@siswa.test',     'nisn' => '0012345001', 'class_id' => 1],
            ['name' => 'Budi Raharjo',     'email' => 'budi@siswa.test',     'nisn' => '0012345002', 'class_id' => 1],
            ['name' => 'Citra Dewi',       'email' => 'citra@siswa.test',    'nisn' => '0012345003', 'class_id' => 1],
            ['name' => 'Dina Maharani',    'email' => 'dina@siswa.test',     'nisn' => '0012345004', 'class_id' => 2],
            ['name' => 'Eka Putra',        'email' => 'eka@siswa.test',      'nisn' => '0012345005', 'class_id' => 2],
        ];

        foreach ($students as $student) {
            User::create([
                'name'     => $student['name'],
                'email'    => $student['email'],
                'password' => Hash::make('password'),
                'role'     => 'siswa',
                'class_id' => $student['class_id'],
                'nisn'     => $student['nisn'],
            ]);
        }
    }
}
