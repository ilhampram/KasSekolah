<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Seed the classes table.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $classes = [
            ['class_name' => 'X IPA 1',  'academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Budi Santoso, S.Pd'],
            ['class_name' => 'X IPA 2',  'academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Siti Aminah, S.Pd'],
            ['class_name' => 'X IPS 1',  'academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Ahmad Fauzi, M.Pd'],
            ['class_name' => 'XI IPA 1', 'academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Dewi Lestari, S.Pd'],
            ['class_name' => 'XI IPS 1', 'academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Rudi Hartono, S.Pd'],
            ['class_name' => 'XII IPA 1','academic_year_id' => $activeYear?->id, 'academic_year' => $activeYear?->year ?? '2026/2027', 'homeroom_teacher' => 'Sri Wahyuni, M.Pd'],
        ];

        foreach ($classes as $class) {
            SchoolClass::create($class);
        }
    }
}
