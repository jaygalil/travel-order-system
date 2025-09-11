<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            'view travel orders',
            'create travel orders',
            'update travel orders', 
            'delete travel orders',
            'approve travel orders',
            'reject travel orders',
            'manage workflow templates',
            'manage users',
            'view reports',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'admin' => [
                'view travel orders',
                'create travel orders', 
                'update travel orders',
                'delete travel orders',
                'approve travel orders',
                'reject travel orders',
                'manage workflow templates',
                'manage users',
                'view reports',
                'export data',
            ],
            'approver' => [
                'view travel orders',
                'approve travel orders',
                'reject travel orders',
            ],
            'employee' => [
                'view travel orders',
                'create travel orders',
                'update travel orders',
                'delete travel orders',
            ],
            'workflow_manager' => [
                'view travel orders',
                'manage workflow templates',
                'view reports',
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        // Create demo users
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP001',
                'position' => 'System Administrator',
                'department' => 'IT Department',
                'division_agency' => 'Information Technology Division',
                'phone' => '+63912345678',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'admin',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP002',
                'position' => 'Department Head',
                'department' => 'Human Resources',
                'division_agency' => 'Human Resources Division',
                'phone' => '+63923456789',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'approver',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com', 
                'password' => Hash::make('password'),
                'employee_id' => 'EMP003',
                'position' => 'Software Developer',
                'department' => 'IT Department',
                'division_agency' => 'Information Technology Division',
                'phone' => '+63934567890',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'employee',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP004', 
                'position' => 'Finance Manager',
                'department' => 'Finance',
                'division_agency' => 'Finance Division',
                'phone' => '+63945678901',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'approver',
            ],
            [
                'name' => 'Robert Garcia',
                'email' => 'robert.garcia@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP005',
                'position' => 'Division Chief', 
                'department' => 'Operations',
                'division_agency' => 'Operations Division',
                'phone' => '+63956789012',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'approver',
            ],
            [
                'name' => 'Lisa Wong',
                'email' => 'lisa.wong@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'EMP006',
                'position' => 'Project Manager',
                'department' => 'IT Department', 
                'division_agency' => 'Information Technology Division',
                'phone' => '+63967890123',
                'is_active' => true,
                'email_verified_at' => now(),
                'role' => 'employee',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            $user->assignRole($role);
        }

        $this->command->info('✅ Roles and users seeded successfully!');
        $this->command->info('📧 Admin login: admin@example.com / password');
    }
}
