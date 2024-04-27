<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    // Default permissions for different roles.
    protected $permissionTeacher = [
        'tasks.view',
        'tasks.create',
        'tasks.edit',
        'tasks.delete',
        'grade_task',
        'view_grades_summary',
        'users.view',
        'view_notifications',
    ];

    protected $permissionStudent = [
        'users.view',
        'users.store',
        'users.edit',
        'users.delete',
        'tasks.view',
        'personal_task_calendar',
        'view_notifications',
        'tasks.submit'
    ];

    protected $permissionClassRepresentative = [
        'tasks.view',
        'tasks.create',
        'tasks.edit',
        'tasks.delete',
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Permission::firstOrCreate(['name' => 'tasks.view', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'tasks.create', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'tasks.edit', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'tasks.submit', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'tasks.delete', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'view_notifications', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'grade_task', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'view_grades_summary', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'personal_task_calendar', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'users.store', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'users.edit', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'users.delete', 'guard_name' => 'api']);


        // Assign permissions to roles
        $roles = Role::all();

        foreach ($roles as $role) {
            // Check the role
            if ($role->name === 'guru') {
                $role->syncPermissions($this->permissionTeacher);
            } elseif ($role->name === 'siswa') {
                $role->syncPermissions($this->permissionStudent);
            } elseif ($role->name === 'pengurus_kelas') {
                $role->syncPermissions($this->permissionClassRepresentative);
            }
        }
    }
}
