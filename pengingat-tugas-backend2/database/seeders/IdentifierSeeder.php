<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentIdentifier;
use App\Models\TeacherIdentifier;

class IdentifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data NISN untuk siswa
        $studentIdentifiers = [
            ['nisn' => '1234567891'],
            ['nisn' => '1234567892'],
            // Tambahkan data NISN siswa lainnya di sini
        ];

        // Masukkan data NISN siswa ke dalam tabel student_identifiers
        foreach ($studentIdentifiers as $identifier) {
            StudentIdentifier::create($identifier);
        }

        // Data NIS untuk guru
        $teacherIdentifiers = [
            ['nis' => '1234567893'],
            ['nis' => '1234567894'],
            // Tambahkan data NIS guru lainnya di sini
        ];

        // Masukkan data NIS guru ke dalam tabel teacher_identifiers
        foreach ($teacherIdentifiers as $identifier) {
            TeacherIdentifier::create($identifier);
        }
    }
}
