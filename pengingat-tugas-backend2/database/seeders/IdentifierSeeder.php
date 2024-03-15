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
            ['nisn' => '1234567893'],
            ['nisn' => '1234567894'],
            ['nisn' => '1234567895'],
            ['nisn' => '1234567896'],
            ['nisn' => '1234567897'],
            ['nisn' => '1234567898'],
            ['nisn' => '1234567899'],
            ['nisn' => '12345678910'],
            // Tambahkan data NISN siswa lainnya di sini
        ];

        // Masukkan data NISN siswa ke dalam tabel student_identifiers
        foreach ($studentIdentifiers as $identifier) {
            StudentIdentifier::create($identifier);
        }

        // Data nip untuk guru
        $teacherIdentifiers = [
            ['nip' => '2234567891'],
            ['nip' => '2234567892'],
            ['nip' => '2234567893'],
            ['nip' => '2234567894'],
            ['nip' => '2234567895'],
            ['nip' => '2234567896'],
            ['nip' => '2234567897'],
            ['nip' => '2234567898'],
            ['nip' => '2234567899'],
            ['nip' => '22345678910'],
            // Tambahkan data nip guru lainnya di sini
        ];

        // Masukkan data nip guru ke dalam tabel teacher_identifiers
        foreach ($teacherIdentifiers as $identifier) {
            TeacherIdentifier::create($identifier);
        }
    }
}
