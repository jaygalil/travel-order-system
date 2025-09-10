<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
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
            'view-travel-orders',
            'create-travel-orders',
            'edit-travel-orders',
            'delete-travel-orders',
            'approve-travel-orders',
            'manage-users',
            'view-reports',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $employeeRole = Role::create(['name' => 'employee']);
        $approverRole = Role::create(['name' => 'approver']);
        
        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $employeeRole->givePermissionTo([
            'view-travel-orders',
            'create-travel-orders',
            'edit-travel-orders',
        ]);
        $approverRole->givePermissionTo([
            'view-travel-orders',
            'approve-travel-orders',
        ]);
        
        // Create admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@travelorder.local',
            'password' => Hash::make('password'),
            'employee_id' => 'ADM001',
            'position' => 'System Administrator',
            'division_agency' => 'DICT - ICT Division',
            'phone' => '09123456789',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');
        
        // Create sample employee
        $employee = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@travelorder.local',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP001',
            'position' => 'IT Specialist',
            'division_agency' => 'DICT - Technical Division',
            'phone' => '09987654321',
            'is_active' => true,
        ]);
        $employee->assignRole('employee');
        
        // Create approver users (matching the approval workflow)
        $approvers = [
            [
                'name' => 'Bariuan, Ronald S.',
                'email' => 'ronald.bariuan@dict.gov.ph',
                'position' => 'Provincial Officer',
                'employee_id' => 'DICT001',
            ],
            [
                'name' => 'Ammasi, Jayfer T.',
                'email' => 'jayfer.ammasi@dict.gov.ph',
                'position' => 'Human Resources',
                'employee_id' => 'DICT002',
            ],
            [
                'name' => 'Gomez, Magdalena D.',
                'email' => 'magdalena.gomez@dict.gov.ph',
                'position' => 'Recommending Approval',
                'employee_id' => 'DICT003',
            ],
            [
                'name' => 'Villafuerte, Mina Flor T.',
                'email' => 'minaflor.villafuerte@dict.gov.ph',
                'position' => 'Verification Officer',
                'employee_id' => 'DICT004',
            ],
            [
                'name' => 'Jimenez, Pinky T.',
                'email' => 'pinky.jimenez@dict.gov.ph',
                'position' => 'Final Approver',
                'employee_id' => 'DICT005',
            ],
        ];
        
        foreach ($approvers as $approverData) {
            $approver = User::create([
                'name' => $approverData['name'],
                'email' => $approverData['email'],
                'password' => Hash::make('password'),
                'employee_id' => $approverData['employee_id'],
                'position' => $approverData['position'],
                'division_agency' => 'DICT - Management',
                'phone' => '09' . rand(100000000, 999999999),
                'is_active' => true,
            ]);
            $approver->assignRole(['employee', 'approver']);
        }
        
        $this->command->info('Users and roles created successfully!');
        $this->command->info('Admin credentials: admin@travelorder.local / password');
        $this->command->info('Employee credentials: john.doe@travelorder.local / password');
    }
}
