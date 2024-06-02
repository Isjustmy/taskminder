<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentIdentifier;
use App\Models\TeacherIdentifier;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'phone_number' => '+6289537657645',
            'student_class_id' => null,
            'guru_mata_pelajaran' => null,
        ]);

        // Assign permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        $permissions = Permission::all();
        $adminRole->syncPermissions($permissions);

        // Assign role with permissions to admin user
        $adminUser->assignRole($adminRole);

        // Buat seeder user lainnya (HANYA UNTUK DEMONSTRASI!)
        $users = [
            [
                'name' => 'Altari Altar',
                'email' => 'altarialtar@gmail.com',
                'password' => bcrypt('altari123'),
                'phone_number' => '+6289537657646',
                'student_class_id' => null,
                'guru_mata_pelajaran' => 'RPL - Produktif',
                'role' => 'guru',
                'identifier' => '22345678911'
            ],
            [
                'nomor_absen' => 12,
                'name' => 'Udin Mauludin',
                'email' => 'udinmauludin@gmail.com',
                'password' => bcrypt('udinmauludin123'),
                'phone_number' => '+6289537657647',
                'student_class_id' => 13,
                'guru_mata_pelajaran' => null,
                'role' => 'siswa',
                'identifier' => '12345678911'
            ],
            [
                'nomor_absen' => 13,
                'name' => 'Rian Rain',
                'email' => 'riansudrajat@gmail.com',
                'password' => bcrypt('rianrain123'),
                'phone_number' => '+6289537657648',
                'student_class_id' => 13,
                'guru_mata_pelajaran' => null,
                'role' => 'pengurus_kelas',
                'identifier' => '12345678912'
            ]
        ];

        foreach ($users as $userData) {
            // Create user
            $user = User::create([
                'nomor_absen' => in_array($userData['role'], ['siswa', 'pengurus_kelas']) ? $userData['nomor_absen'] : null,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'phone_number' => $userData['phone_number'],
                'student_class_id' => in_array($userData['role'], ['siswa', 'pengurus_kelas']) ? $userData['student_class_id'] : null,
                'guru_mata_pelajaran' => $userData['role'] === 'guru' ? $userData['guru_mata_pelajaran'] : null
            ]);

            // Find role
            $role = Role::where('name', $userData['role'])->first();

            // Assign role to user
            $user->assignRole($role);

            // Jika peran pengguna adalah 'pengurus_kelas', tambahkan juga peran 'siswa'
            if ($userData['role'] === 'pengurus_kelas') {
                $studentRole = Role::where('name', 'siswa')->first();
                $user->assignRole($studentRole);
            }

            // Assign identifier
            if ($userData['role'] === 'siswa' || $userData['role'] === 'pengurus_kelas') {
                StudentIdentifier::updateOrCreate([
                    'nisn' => $userData['identifier'],
                    'student_id' => $user->id,
                ]);
            } elseif ($userData['role'] === 'guru') {
                TeacherIdentifier::updateOrCreate([
                    'nip' => $userData['identifier'],
                    'teacher_id' => $user->id,
                ]);
            }
        }
    }
}
