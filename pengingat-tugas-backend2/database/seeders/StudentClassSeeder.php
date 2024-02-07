<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentClass;

class StudentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // kelas 10
        StudentClass::create([
            'class' => '10 RPL 1'
        ]);
        StudentClass::create([
            'class' => '10 RPL 2'
        ]);
        StudentClass::create([
            'class' => '10 RPL 3'
        ]);
        StudentClass::create([
            'class' => '10 ANIMASI 1'
        ]);
        StudentClass::create([
            'class' => '10 ANIMASI 2'
        ]);
        StudentClass::create([
            'class' => '10 BCF 1'
        ]);
        StudentClass::create([
            'class' => '10 BCF 2'
        ]);
        StudentClass::create([
            'class' => '10 TO 1'
        ]);
        StudentClass::create([
            'class' => '10 TO 2'
        ]);
        StudentClass::create([
            'class' => '10 TPFL'
        ]);

        // kelas 11
        StudentClass::create([
            'class' => '11 RPL 1'
        ]);
        StudentClass::create([
            'class' => '11 RPL 2'
        ]);
        StudentClass::create([
            'class' => '11 RPL 3'
        ]);
        StudentClass::create([
            'class' => '11 ANIMASI 1'
        ]);
        StudentClass::create([
            'class' => '11 ANIMASI 2'
        ]);
        StudentClass::create([
            'class' => '11 BCF 1'
        ]);
        StudentClass::create([
            'class' => '11 BCF 2'
        ]);
        StudentClass::create([
            'class' => '11 TO 1'
        ]);
        StudentClass::create([
            'class' => '11 TO 2'
        ]);
        StudentClass::create([
            'class' => '11 TPFL'
        ]);

        // kelas 12
        StudentClass::create([
            'class' => '12 RPL 1'
        ]);
        StudentClass::create([
            'class' => '12 RPL 2'
        ]);
        StudentClass::create([
            'class' => '12 RPL 3'
        ]);
        StudentClass::create([
            'class' => '12 ANIMASI 1'
        ]);
        StudentClass::create([
            'class' => '12 ANIMASI 2'
        ]);
        StudentClass::create([
            'class' => '12 BCF 1'
        ]);
        StudentClass::create([
            'class' => '12 BCF 2'
        ]);
        StudentClass::create([
            'class' => '12 TO 1'
        ]);
        StudentClass::create([
            'class' => '12 TO 2'
        ]);
        StudentClass::create([
            'class' => '12 TPFL'
        ]);
    }
}
