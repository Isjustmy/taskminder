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
        // Task Permissions
        // 1. View Task Details
        Permission::firstOrCreate(['name' => 'tasks.view', 'guard_name' => 'api']);

        // 2. Create Task
        Permission::firstOrCreate(['name' => 'tasks.create', 'guard_name' => 'api']);

        // 3. Edit Task
        Permission::firstOrCreate(['name' => 'tasks.edit', 'guard_name' => 'api']);

        // 4. Submit Task
        Permission::firstOrCreate(['name' => 'tasks.submit', 'guard_name' => 'api']);

        // 5. Delete Task
        Permission::firstOrCreate(['name' => 'tasks.delete', 'guard_name' => 'api']);

        // Notification Permissions
        // 6. View Notifications
        Permission::firstOrCreate(['name' => 'view_notifications', 'guard_name' => 'api']);

        // Task Grading Permissions
        // 7. Give Score Task
        Permission::firstOrCreate(['name' => 'grade_task', 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'view_grades_summary', 'guard_name' => 'api']);

        // Calendar Permissions
        // 11. Personal Task Calendar
        Permission::firstOrCreate(['name' => 'personal_task_calendar', 'guard_name' => 'api']);

        // User Account Permissions
        // 13. View User Account
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'api']);

        // 14. Create User Account
        Permission::firstOrCreate(['name' => 'users.store', 'guard_name' => 'api']);

        // 15. Edit User Account
        Permission::firstOrCreate(['name' => 'users.edit', 'guard_name' => 'api']);

        // 16. Delete User Account
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
