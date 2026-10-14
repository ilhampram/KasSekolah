<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Seed academic_years table.
     */
    public function run(): void
    {
        $years = [
            ['year' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false],
            ['year' => '2025/2026', 'semester' => 'Genap',  'is_active' => false],
            ['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true],
            ['year' => '2026/2027', 'semester' => 'Genap',  'is_active' => false],
        ];

        foreach ($years as $item) {
            AcademicYear::create($item);
        }
    }
}
