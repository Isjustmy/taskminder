<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create data user.
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345'),
            'phone_number' => '088888888888',
            'student_class_id' => null
        ]);


        // assign permission to role.
        $role = Role::whereName('admin')->first();
        $permissions = Permission::all();

        $role->syncPermissions($permissions);


        // assign role with permission to user.
        $user = User::find(1);
        $user->assignRole($role->name);
    }
}
