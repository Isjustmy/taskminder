<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);
        Role::create([
            'name' => 'siswa',
            'guard_name' => 'api'
        ]);
        Role::create([
            'name' => 'guru',
            'guard_name' => 'api'
        ]);
        Role::create([
            'name' => 'pengurus_kelas',
            'guard_name' => 'api'
        ]);

    }
}
