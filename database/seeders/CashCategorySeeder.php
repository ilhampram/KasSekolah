<?php

namespace Database\Seeders;

use App\Models\CashCategory;
use Illuminate\Database\Seeder;

class CashCategorySeeder extends Seeder
{
    /**
     * Seed the cash_categories table with default categories.
     */
    public function run(): void
    {
        $categories = [
            // ── Pemasukan (in) ─────────────────────────────────
            [
                'name'        => 'Iuran Bulanan',
                'type'        => 'in',
                'description' => 'Iuran kas rutin bulanan setiap siswa.',
            ],
            [
                'name'        => 'Kas Acara',
                'type'        => 'in',
                'description' => 'Iuran untuk kegiatan/acara kelas.',
            ],
            [
                'name'        => 'Sumbangan Sukarela',
                'type'        => 'in',
                'description' => 'Sumbangan sukarela dari siswa atau pihak lain.',
            ],

            // ── Pengeluaran (out) ──────────────────────────────
            [
                'name'        => 'Pembelian Alat Kelas',
                'type'        => 'out',
                'description' => 'Pengeluaran untuk pembelian perlengkapan kelas (spidol, penghapus, dll).',
            ],
            [
                'name'        => 'Biaya Kegiatan',
                'type'        => 'out',
                'description' => 'Pengeluaran untuk pelaksanaan kegiatan/acara kelas.',
            ],
            [
                'name'        => 'Biaya Kebersihan',
                'type'        => 'out',
                'description' => 'Pengeluaran untuk perlengkapan kebersihan kelas.',
            ],
        ];

        foreach ($categories as $category) {
            CashCategory::create($category);
        }
    }
}
